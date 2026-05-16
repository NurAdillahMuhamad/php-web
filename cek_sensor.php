<?php
// ================================================================
// cek_sensor.php — Data terbaru untuk dashboard
// ================================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ── KONEKSI DATABASE ─────────────────────────────────────────────
$konek = mysqli_connect("sql111.infinityfree.com", "if0_41924899", "mikroalga123", "if0_41924899_mikroalga");

if (!$konek) {
    echo json_encode([
        'error' => 'Koneksi database gagal'
    ]);
    exit;
}

// ── DATA SENSOR TERBARU ──────────────────────────────────────────
$q_sensor = mysqli_query(
    $konek,
    "SELECT * FROM mikroalga_sensor ORDER BY id DESC LIMIT 1"
);

$d = mysqli_fetch_assoc($q_sensor);

// ── DATA WARNA TERBARU ───────────────────────────────────────────
$q_warna = mysqli_query(
    $konek,
    "SELECT warna, status_warna, persentase_warna
     FROM mikroalga_sensor
     WHERE warna IS NOT NULL
       AND warna != ''
       AND warna != 'tidak terdeteksi'
     ORDER BY id DESC
     LIMIT 1"
);

$w = mysqli_fetch_assoc($q_warna);

// ── HITUNG MENIT SEJAK UPDATE TERAKHIR ──────────────────────────
$menit_lalu = null;

if ($d && isset($d['waktu'])) {
    $menit_lalu = round(
        (time() - strtotime($d['waktu'])) / 60
    );
}

// ── STATUS pH ────────────────────────────────────────────────────
$ph = (float)($d['ph'] ?? 0);

$ph_status = 'normal';

if ($ph < 8.5) {
    $ph_status = 'rendah';
}
elseif ($ph > 9.0) {
    $ph_status = 'tinggi';
}

// ── STATUS LUX ───────────────────────────────────────────────────
$lux = (float)($d['lux'] ?? 0);

$lux_status = 'normal';

if ($lux <= 0) {
    $lux_status = 'error';
}
elseif ($lux < 1000) {
    $lux_status = 'redup';
}
elseif ($lux > 10000) {
    $lux_status = 'terlalu terang';
}

// ── STATUS POMPA DARI RELAY ESP32 ───────────────────────────────
$pompa_basa = (
    isset($d['relay_basa']) &&
    $d['relay_basa']
) ? 'DOSING' : 'IDLE';

$pompa_normal = (
    isset($d['relay_netral']) &&
    $d['relay_netral']
) ? 'DOSING' : 'IDLE';

// ── STATUS UV ────────────────────────────────────────────────────
$uv = (
    isset($d['relay_uv']) &&
    $d['relay_uv']
) ? 'ON' : 'OFF';

// ── RESPONSE JSON ────────────────────────────────────────────────
$response = [

    // SENSOR
    "pH"         => round($ph, 2),
    "cahaya"     => round($lux),
    "waktu"      => $d['waktu'] ?? null,
    "menit_lalu" => $menit_lalu,

    // STATUS SENSOR
    "pH_status"  => $ph_status,
    "lux_status" => $lux_status,

    // WARNA AIR
    "warna"            => $w['warna'] ?? 'tidak terdeteksi',
    "status_warna"     => $w['status_warna'] ?? '-',
    "persentase_warna" => $w['persentase_warna'] ?? 0,

    // STATUS RELAY
    "pompa_basa"   => $pompa_basa,
    "pompa_normal" => $pompa_normal,
    "uv"           => $uv
];

// ── OUTPUT JSON ──────────────────────────────────────────────────
echo json_encode($response);

// ── TUTUP KONEKSI ────────────────────────────────────────────────
mysqli_close($konek);
?>