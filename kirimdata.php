<?php
// ================================================================
// kirimdata.php — Penerima data dari ESP32 (REVISED)
// Perubahan:
// 1. Koneksi DB pakai environment variable Railway
// 2. Tambah field pompa_nutrisi, vol_normal, vol_nutrisi
// 3. Hapus persentase_warna
// 4. Fix nama kolom di INSERT & UPDATE status_pompa
// ================================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ── KONEKSI DATABASE (Railway env variable) ──────────────────────
$db_host = getenv('MYSQLHOST')     ?: 'localhost';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';
$db_port = (int)(getenv('MYSQLPORT') ?: 3306);

$konek = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$konek) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi DB gagal: ' . mysqli_connect_error()]);
    exit;
}

// ── BACA PARAMETER (GET atau POST JSON atau POST form) ───────────
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw   = file_get_contents('php://input');
    $json  = json_decode($raw, true);
    $input = $json ?: $_POST;
} else {
    $input = $_GET;
}

// ── HELPER: ambil nilai dengan fallback berbagai nama field ───────
function ambil($input, ...$keys) {
    foreach ($keys as $k) {
        if (isset($input[$k]) && $input[$k] !== '') return $input[$k];
        $kl = strtolower($k);
        if (isset($input[$kl]) && $input[$kl] !== '') return $input[$kl];
    }
    return null;
}

// ── BACA SEMUA FIELD DARI ESP32 ───────────────────────────────────
$pH           = ambil($input, 'pH', 'ph', 'PH');
$cahaya       = ambil($input, 'cahaya', 'lux', 'Lux', 'LUX');
$uv           = strtoupper(ambil($input, 'uv', 'UV') ?? 'OFF');
$pompa_basa   = strtoupper(ambil($input, 'pompa_basa',   'PompaBasa')   ?? 'IDLE');
$pompa_normal = strtoupper(ambil($input, 'pompa_normal', 'PompaNormal') ?? 'IDLE');
$pompa_nutrisi= strtoupper(ambil($input, 'pompa_nutrisi', 'PompaNutrisi') ?? 'OFF');
$vol_basa     = (float)(ambil($input, 'vol_basa',   'volBasa')   ?? 0);
$vol_normal   = (float)(ambil($input, 'vol_normal', 'volNormal') ?? 0);
$vol_nutrisi  = (float)(ambil($input, 'vol_nutrisi','volNutrisi') ?? 0);

// ── VALIDASI MINIMAL ─────────────────────────────────────────────
if ($pH === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'msg' => 'Parameter pH wajib ada']);
    exit;
}

// ── SANITASI ─────────────────────────────────────────────────────
$pH      = (float)$pH;
$cahaya  = ($cahaya === null || strtoupper((string)$cahaya) === 'ERROR') ? 0 : (int)$cahaya;

$uv            = mysqli_real_escape_string($konek, $uv);
$pompa_basa    = mysqli_real_escape_string($konek, $pompa_basa);
$pompa_normal  = mysqli_real_escape_string($konek, $pompa_normal);
$pompa_nutrisi = mysqli_real_escape_string($konek, $pompa_nutrisi);

// ── INSERT KE mikroalga_sensor ───────────────────────────────────
// Warna, status_warna, fase_sebelumnya akan diisi oleh Railway worker (UPDATE)
$sql_sensor = "INSERT INTO mikroalga_sensor
    (pH, cahaya, uv, pompa_basa, pompa_normal, pompa_nutrisi,
     vol_basa, vol_normal, vol_nutrisi, waktu)
    VALUES
    ('$pH', '$cahaya', '$uv', '$pompa_basa', '$pompa_normal', '$pompa_nutrisi',
     '$vol_basa', '$vol_normal', '$vol_nutrisi', NOW())";

$r1 = mysqli_query($konek, $sql_sensor);

// Fallback minimal kalau ada kolom yang belum exist
if (!$r1) {
    $sql_fallback = "INSERT INTO mikroalga_sensor (pH, cahaya, uv, pompa_basa, pompa_normal, waktu)
        VALUES ('$pH', '$cahaya', '$uv', '$pompa_basa', '$pompa_normal', NOW())";
    $r1 = mysqli_query($konek, $sql_fallback);
}

// ── UPDATE status_pompa ──────────────────────────────────────────
$status_gabungan = ($pompa_basa === 'DOSING' || $pompa_normal === 'DOSING') ? 'ON' : 'OFF';

$sql_pompa = "UPDATE status_pompa
    SET
        status         = '$status_gabungan',
        uv             = '$uv',
        pompa_basa     = '$pompa_basa',
        pompa_normal   = '$pompa_normal',
        pompa_nutrisi  = '$pompa_nutrisi'
    WHERE id = 1";

$r2 = mysqli_query($konek, $sql_pompa);

if (!$r2) {
    mysqli_query($konek, "UPDATE status_pompa SET status='$status_gabungan' WHERE id=1");
}

// ── RESPONSE KE ESP32 ────────────────────────────────────────────
if ($r1) {
    echo json_encode([
        'status'  => 'ok',
        'ts'      => date('Y-m-d H:i:s'),
        'pH'      => $pH,
        'cahaya'  => $cahaya,
        'uv'      => $uv,
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => mysqli_error($konek)]);
}

mysqli_close($konek);
?>
