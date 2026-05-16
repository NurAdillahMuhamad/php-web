<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/db.php';

$warna        = isset($_GET['warna'])        ? mysqli_real_escape_string($konek, $_GET['warna'])        : 'tidak terdeteksi';
$status_warna = isset($_GET['status_warna']) ? mysqli_real_escape_string($konek, $_GET['status_warna']) : '-';
$persentase   = isset($_GET['persentase'])   ? (float)$_GET['persentase']                               : 0;

// Update baris terakhir di mikroalga_sensor
$id_query = mysqli_query($konek, "SELECT MAX(id) as max_id FROM mikroalga_sensor");
$id_row   = mysqli_fetch_assoc($id_query);
$max_id   = $id_row['max_id'];

if ($max_id) {
    $sql = "UPDATE mikroalga_sensor
            SET warna='$warna', status_warna='$status_warna', persentase_warna='$persentase'
            WHERE id='$max_id'";
    $result = mysqli_query($konek, $sql);

    if ($result) {
        echo json_encode(['status' => 'ok', 'id' => $max_id, 'warna' => $warna]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'msg' => mysqli_error($konek)]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Tidak ada data sensor']);
}

mysqli_close($konek);
?>
