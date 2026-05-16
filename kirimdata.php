<?php
// ================================================================
// kirimdata.php — Penerima data dari ESP32
// Mendukung GET (query string) dan POST (JSON / form-data)
// URL contoh GET: http://192.168.x.x/mikroalga/kirimdata.php?pH=7.27&cahaya=1500&uv=ON&pompa_basa=DOSING&pompa_normal=IDLE
// ================================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$konek = mysqli_connect("sql111.infinityfree.com", "if0_41924899", "mikroalga123", "if0_41924899_mikroalga");
if (!$konek) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi DB gagal']);
    exit;
}

// ── 1. Baca parameter (GET atau POST JSON atau POST form) ────────
$input = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    $input = $json ?: $_POST;
} else {
    $input = $_GET;
}

// ── 2. Normalkan nama field dari ESP32 ───────────────────────────
// ESP32 serial: pH=7.27 | Lux=1500 | UV=ON | PompaBasa=DOSING | PompaNormal=IDLE
// Terima semua kemungkinan penulisan huruf besar/kecil

function ambil($input, ...$keys) {
    foreach ($keys as $k) {
        if (isset($input[$k]) && $input[$k] !== '') return $input[$k];
        // coba lowercase
        $kl = strtolower($k);
        if (isset($input[$kl]) && $input[$kl] !== '') return $input[$kl];
    }
    return null;
}

$pH          = ambil($input, 'pH', 'ph', 'PH');
$cahaya      = ambil($input, 'cahaya', 'lux', 'Lux', 'LUX', 'cahaya');
$uv          = strtoupper(ambil($input, 'uv', 'UV') ?? 'OFF');
$pompa_basa  = strtoupper(ambil($input, 'pompa_basa', 'PompaBasa', 'pompabasa') ?? 'IDLE');
$pompa_normal= strtoupper(ambil($input, 'pompa_normal', 'PompaNormal', 'pompanormal') ?? 'IDLE');
$relay_basa   = ($input['relay_basa']   ?? false) ? 'DOSING' : $pompa_basa;
$relay_netral = ($input['relay_netral'] ?? false) ? 'DOSING' : $pompa_normal;
$warna       = ambil($input, 'warna', 'Warna') ?? 'tidak terdeteksi';
$status_warna= ambil($input, 'status_warna', 'statusWarna') ?? '-';
$persentase  = ambil($input, 'persentase', 'persentase_warna') ?? 0;
$vol_basa    = (float)(ambil($input, 'vol_basa', 'volBasa') ?? 0);

// ── 3. Validasi minimal ─────────────────────────────────────────
if ($pH === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'msg' => 'Parameter pH wajib ada']);
    exit;
}

// Sanitasi angka
$pH      = (float)$pH;
// Jika cahaya = "ERROR" (sensor gagal baca), simpan 0
$cahaya  = ($cahaya === null || strtoupper($cahaya) === 'ERROR') ? 0 : (int)$cahaya;
$persentase = (float)$persentase;

// ── 4. Escape string ────────────────────────────────────────────
$uv           = mysqli_real_escape_string($konek, $uv);
$pompa_basa   = mysqli_real_escape_string($konek, $pompa_basa);
$pompa_normal = mysqli_real_escape_string($konek, $pompa_normal);
$warna        = mysqli_real_escape_string($konek, $warna);
$status_warna = mysqli_real_escape_string($konek, $status_warna);

// ── 5. Simpan ke tabel mikroalga_sensor ─────────────────────────
// Cek apakah kolom uv, pompa_basa, pompa_normal sudah ada
// Kalau belum ada, script tetap jalan (INSERT hanya kolom yang tersedia)
$sql_sensor = "INSERT INTO mikroalga_sensor 
                (pH, cahaya, warna, status_warna, persentase_warna, uv, pompa_basa, pompa_normal, vol_basa, waktu)
               VALUES 
                ('$pH', '$cahaya', '$warna', '$status_warna', '$persentase', '$uv', '$pompa_basa', '$pompa_normal', '$vol_basa', NOW())";

$r1 = mysqli_query($konek, $sql_sensor);

// Fallback: kalau kolom baru belum ada, coba INSERT minimal
if (!$r1) {
    $sql_sensor_minimal = "INSERT INTO mikroalga_sensor (pH, cahaya, waktu) VALUES ('$pH', '$cahaya', NOW())";
    $r1 = mysqli_query($konek, $sql_sensor_minimal);
}

// ── 6. Update tabel status_pompa ────────────────────────────────
// Tentukan status gabungan: jika salah satu DOSING → ON
$status_gabungan = ($pompa_basa === 'DOSING' || $pompa_normal === 'DOSING') ? 'ON' : 'OFF';

$sql_pompa = "UPDATE status_pompa 
              SET status='$status_gabungan', uv='$uv', pompa_basa='$relay_basa', pompa_normal='$relay_netral' 
              WHERE id=1";
$r2 = mysqli_query($konek, $sql_pompa);

// Fallback minimal kalau kolom baru belum ada
if (!$r2) {
    mysqli_query($konek, "UPDATE status_pompa SET status='$status_gabungan' WHERE id=1");
}

// ── 7. Response ke ESP32 ────────────────────────────────────────
if ($r1) {
    echo json_encode([
        'status' => 'ok',
        'ts'     => date('Y-m-d H:i:s'),
        'pH'     => $pH,
        'cahaya' => $cahaya,
        'uv'     => $uv,
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => mysqli_error($konek)]);
}

mysqli_close($konek);
?>