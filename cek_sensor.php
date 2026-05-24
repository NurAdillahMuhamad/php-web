<?php
// ================================================================
// cek_sensor.php — Data terbaru untuk dashboard (REVISED)
// Perubahan:
// 1. Koneksi DB pakai environment variable Railway
// 2. Fix nama kolom: relay_basa/netral/uv → pompa_basa/normal/uv
// 3. Fix threshold pH: 9.0 → 10.5
// 4. Tambah pompa_nutrisi ke response
// 5. Hapus persentase_warna
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
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}

// ── DATA SENSOR TERBARU ──────────────────────────────────────────
$q_sensor = mysqli_query($konek,
    "SELECT * FROM mikroalga_sensor ORDER BY id DESC LIMIT 1"
);
$d = mysqli_fetch_assoc($q_sensor);

// ── DATA WARNA TERBARU (pisah query untuk robustness) ────────────
$q_warna = mysqli_query($konek,
    "SELECT warna, status_warna
     FROM mikroalga_sensor
     WHERE warna IS NOT NULL
       AND warna != ''
       AND warna != 'tidak terdeteksi'
     ORDER BY id DESC LIMIT 1"
);
$w = mysqli_fetch_assoc($q_warna);

// ── DATA POMPA NUTRISI TERAKHIR AKTIF ────────────────────────────
$q_nutrisi = mysqli_query($konek,
    "SELECT waktu FROM mikroalga_sensor
     WHERE pompa_nutrisi = 'ON'
     ORDER BY id DESC LIMIT 1"
);
$nutrisi_row       = mysqli_fetch_assoc($q_nutrisi);
$nutrisi_terakhir  = $nutrisi_row['waktu'] ?? null;

// ── HITUNG MENIT SEJAK UPDATE TERAKHIR ──────────────────────────
$menit_lalu = null;
if ($d && isset($d['waktu'])) {
    $menit_lalu = round((time() - strtotime($d['waktu'])) / 60);
}

// ── STATUS pH (FIX: threshold 10.5, bukan 9.0) ──────────────────
$ph        = (float)($d['pH'] ?? $d['ph'] ?? 0);
$ph_status = 'normal';
if ($ph < 8.5) {
    $ph_status = 'rendah';
} elseif ($ph > 10.5) {   // FIX: sebelumnya 9.0
    $ph_status = 'tinggi';
}

// ── STATUS LUX ───────────────────────────────────────────────────
$lux        = (float)($d['cahaya'] ?? 0);
$lux_status = 'normal';
if ($lux <= 0) {
    $lux_status = 'error';
} elseif ($lux < 1000) {
    $lux_status = 'redup';
} elseif ($lux > 10000) {
    $lux_status = 'terlalu terang';
}

// ── STATUS POMPA (FIX: pakai nama kolom yang benar di DB) ────────
// Sebelumnya: $d['relay_basa'], $d['relay_netral'], $d['relay_uv'] → tidak ada
// Sekarang:   $d['pompa_basa'], $d['pompa_normal'], $d['uv'] → benar
$pompa_basa    = $d['pompa_basa']    ?? 'IDLE';
$pompa_normal  = $d['pompa_normal']  ?? 'IDLE';
$pompa_nutrisi = $d['pompa_nutrisi'] ?? 'OFF';
$uv            = $d['uv']            ?? 'OFF';

// ── RESPONSE JSON ────────────────────────────────────────────────
$response = [
    // SENSOR
    'pH'            => round($ph, 2),
    'cahaya'        => round($lux),
    'waktu'         => $d['waktu']     ?? null,
    'menit_lalu'    => $menit_lalu,

    // STATUS SENSOR
    'pH_status'     => $ph_status,
    'lux_status'    => $lux_status,

    // WARNA AIR
    'warna'         => $w['warna']        ?? 'tidak terdeteksi',
    'status_warna'  => $w['status_warna'] ?? '-',

    // STATUS AKTUATOR (FIX: nama kolom benar)
    'pompa_basa'    => $pompa_basa,
    'pompa_normal'  => $pompa_normal,
    'pompa_nutrisi' => $pompa_nutrisi,
    'uv'            => $uv,

    // VOLUME (untuk info tambahan)
    'vol_basa'      => (float)($d['vol_basa']    ?? 0),
    'vol_normal'    => (float)($d['vol_normal']   ?? 0),
    'vol_nutrisi'   => (float)($d['vol_nutrisi']  ?? 0),

    // NUTRISI TERAKHIR
    'nutrisi_terakhir' => $nutrisi_terakhir,
];

echo json_encode($response);
mysqli_close($konek);
?>
