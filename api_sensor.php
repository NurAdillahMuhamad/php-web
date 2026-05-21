<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/db.php';

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    echo json_encode(['error' => 'Format tanggal tidak valid']);
    exit;
}

try {
    // Konversi waktu ke WIB (UTC+7) sebelum filter tanggal
    $stmt = $pdo->prepare("
        SELECT id, pH, cahaya, warna, status_warna, persentase_warna,
               CONVERT_TZ(waktu, '+00:00', '+07:00') as waktu
        FROM mikroalga_sensor
        WHERE DATE(CONVERT_TZ(waktu, '+00:00', '+07:00')) = ?
        ORDER BY id ASC
    ");
    $stmt->execute([$tanggal]);
    $data = $stmt->fetchAll();

    if (empty($data)) {
        echo json_encode(['error' => 'Tidak ada data', 'data' => []]);
    } else {
        echo json_encode(['data' => $data]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
