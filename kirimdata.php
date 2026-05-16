<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

// ── Baca parameter (GET atau POST) ───────────────────────────────
$input = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $json = json_decode($raw, true);
    $input = $json ?: $_POST;
} else {
    $input = $_GET;
}

function ambil($input, ...$keys) {
    foreach ($keys as $k) {
        if (isset($input[$k]) && $input[$k] !== '') return $input[$k];
        $kl = strtolower($k);
        if (isset($input[$kl]) && $input[$kl] !== '') return $input[$kl];
    }
    return null;
}

$pH           = ambil($input, 'pH', 'ph', 'PH');
$cahaya       = ambil($input, 'cahaya', 'lux', 'Lux', 'LUX');
$uv           = strtoupper(ambil($input, 'uv', 'UV') ?? 'OFF');
$pompa_basa   = strtoupper(ambil($input, 'pompa_basa', 'PompaBasa') ?? 'IDLE');
$pompa_normal = strtoupper(ambil($input, 'pompa_normal', 'PompaNormal') ?? 'IDLE');
$warna        = ambil($input, 'warna', 'Warna') ?? 'tidak terdeteksi';
$status_warna = ambil($input, 'status_warna', 'statusWarna') ?? '-';
$persentase   = (float)(ambil($input, 'persentase', 'persentase_warna') ?? 0);
$vol_basa     = (float)(ambil($input, 'vol_basa', 'volBasa') ?? 0);

if ($pH === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'msg' => 'Parameter pH wajib ada']);
    exit;
}

$pH     = (float)$pH;
$cahaya = ($cahaya === null || strtoupper((string)$cahaya) === 'ERROR') ? 0 : (int)$cahaya;

try {
    // Insert sensor
    $stmt = $pdo->prepare("INSERT INTO mikroalga_sensor
        (pH, cahaya, warna, status_warna, persentase_warna, uv, pompa_basa, pompa_normal, vol_basa, waktu)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$pH, $cahaya, $warna, $status_warna, $persentase, $uv, $pompa_basa, $pompa_normal, $vol_basa]);

    // Update status pompa
    $status_gabungan = ($pompa_basa === 'DOSING' || $pompa_normal === 'DOSING') ? 'ON' : 'OFF';
    $stmt2 = $pdo->prepare("UPDATE status_pompa SET status=?, uv=?, pompa_basa=?, pompa_normal=? WHERE id=1");
    $stmt2->execute([$status_gabungan, $uv, $pompa_basa, $pompa_normal]);

    echo json_encode(['status' => 'ok', 'ts' => date('Y-m-d H:i:s'), 'pH' => $pH, 'cahaya' => $cahaya]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}
?>
