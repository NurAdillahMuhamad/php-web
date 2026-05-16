<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

try {
    $d = $pdo->query("SELECT * FROM mikroalga_sensor ORDER BY id DESC LIMIT 1")->fetch();
    $w = $pdo->query("SELECT warna, status_warna, persentase_warna FROM mikroalga_sensor
                      WHERE warna IS NOT NULL AND warna != '' AND warna != 'tidak terdeteksi'
                      ORDER BY id DESC LIMIT 1")->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$menit_lalu = null;
if ($d && isset($d['waktu'])) {
    $menit_lalu = round((time() - strtotime($d['waktu'])) / 60);
}

$ph = (float)($d['pH'] ?? $d['ph'] ?? 0);
$ph_status = 'normal';
if ($ph < 8.5)     $ph_status = 'rendah';
elseif ($ph > 9.0) $ph_status = 'tinggi';

$lux = (float)($d['cahaya'] ?? 0);
$lux_status = 'normal';
if ($lux <= 0)        $lux_status = 'error';
elseif ($lux < 1000)  $lux_status = 'redup';
elseif ($lux > 10000) $lux_status = 'terlalu terang';

echo json_encode([
    "pH"               => round($ph, 2),
    "cahaya"           => round($lux),
    "waktu"            => $d['waktu']            ?? null,
    "menit_lalu"       => $menit_lalu,
    "pH_status"        => $ph_status,
    "lux_status"       => $lux_status,
    "warna"            => $w['warna']            ?? 'tidak terdeteksi',
    "status_warna"     => $w['status_warna']     ?? '-',
    "persentase_warna" => $w['persentase_warna'] ?? 0,
    "pompa_basa"       => $d['pompa_basa']       ?? 'IDLE',
    "pompa_normal"     => $d['pompa_normal']     ?? 'IDLE',
    "uv"               => $d['uv']               ?? 'OFF',
]);
?>
