<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/db.php';

// =============================================
//  AMBIL DATA SENSOR DARI DATABASE
// =============================================
try {
    $d = $pdo->query("SELECT * FROM mikroalga_sensor ORDER BY id DESC LIMIT 1")->fetch();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// =============================================
//  AMBIL DATA WARNA DARI RAILWAY
// =============================================
$warna_railway   = null;
$railway_url     = "https://worker-production-c170.up.railway.app/hasil_warna";

$ctx = stream_context_create([
    "http" => [
        "timeout" => 5,  // maksimal 5 detik, agar tidak hang
        "method"  => "GET",
    ]
]);

$raw = @file_get_contents($railway_url, false, $ctx);
if ($raw !== false) {
    $warna_railway = json_decode($raw, true);
}

// =============================================
//  HITUNG MENIT LALU (sensor)
// =============================================
$menit_lalu = null;
if ($d && isset($d['waktu'])) {
    $menit_lalu = round((time() - strtotime($d['waktu'])) / 60);
}

// =============================================
//  pH STATUS
// =============================================
$ph = (float)($d['pH'] ?? $d['ph'] ?? 0);
$ph_status = 'normal';
if ($ph < 8.5)     $ph_status = 'rendah';
elseif ($ph > 9.0) $ph_status = 'tinggi';

// =============================================
//  CAHAYA STATUS
// =============================================
$lux = (float)($d['cahaya'] ?? 0);
$lux_status = 'normal';
if ($lux <= 0)        $lux_status = 'error';
elseif ($lux < 1000)  $lux_status = 'redup';
elseif ($lux > 10000) $lux_status = 'terlalu terang';

// =============================================
//  WARNA — Railway prioritas, fallback ke DB
// =============================================
if ($warna_railway && isset($warna_railway['warna']) && $warna_railway['warna'] !== 'tidak terdeteksi') {
    $warna        = $warna_railway['warna'];
    $status_warna = $warna_railway['status_warna'] ?? '-';
    $skor_warna   = $warna_railway['skor']         ?? 0;
    $warna_menit  = $warna_railway['menit_lalu']   ?? null;
    $warna_source = 'railway';
} else {
    // Fallback ke database kalau Railway tidak tersedia / tidak terdeteksi
    try {
        $w = $pdo->query("SELECT warna, status_warna, persentase_warna FROM mikroalga_sensor
                          WHERE warna IS NOT NULL AND warna != '' AND warna != 'tidak terdeteksi'
                          ORDER BY id DESC LIMIT 1")->fetch();
    } catch (PDOException $e) {
        $w = null;
    }
    $warna        = $w['warna']            ?? 'tidak terdeteksi';
    $status_warna = $w['status_warna']     ?? '-';
    $skor_warna   = $w['persentase_warna'] ?? 0;
    $warna_menit  = null;
    $warna_source = 'database';
}

// =============================================
//  OUTPUT JSON
// =============================================
echo json_encode([
    "pH"               => round($ph, 2),
    "cahaya"           => round($lux),
    "waktu"            => $d['waktu']        ?? null,
    "menit_lalu"       => $menit_lalu,
    "pH_status"        => $ph_status,
    "lux_status"       => $lux_status,
    "warna"            => $warna,
    "status_warna"     => $status_warna,
    "skor_warna"       => $skor_warna,
    "warna_menit_lalu" => $warna_menit,
    "warna_source"     => $warna_source,    // 'railway' atau 'database'
    "pompa_basa"       => $d['pompa_basa']  ?? 'IDLE',
    "pompa_normal"     => $d['pompa_normal'] ?? 'IDLE',
    "uv"               => $d['uv']          ?? 'OFF',
]);
?>
