<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

$warna        = $_GET['warna']        ?? 'tidak terdeteksi';
$status_warna = $_GET['status_warna'] ?? '-';
$persentase   = (float)($_GET['persentase'] ?? 0);

try {
    $max = $pdo->query("SELECT MAX(id) as max_id FROM mikroalga_sensor")->fetch();
    $max_id = $max['max_id'];

    if ($max_id) {
        $stmt = $pdo->prepare("UPDATE mikroalga_sensor SET warna=?, status_warna=?, persentase_warna=? WHERE id=?");
        $stmt->execute([$warna, $status_warna, $persentase, $max_id]);
        echo json_encode(['status' => 'ok', 'id' => $max_id, 'warna' => $warna]);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Tidak ada data sensor']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
}
?>
