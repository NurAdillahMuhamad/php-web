<?php
header('Content-Type: application/json');

$konek = mysqli_connect("sql111.infinityfree.com", "if0_41924899", "mikroalga123", "if0_41924899_mikroalga");

if (!$konek) {
    echo json_encode(['error' => 'Koneksi gagal']);
    exit;
}

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    echo json_encode(['error' => 'Format tanggal tidak valid']);
    exit;
}

$sql = "SELECT 
            id,
            pH,
            cahaya,
            warna,
            status_warna,
            persentase_warna,
            waktu
        FROM mikroalga_sensor
        WHERE DATE(waktu) = '$tanggal'
        ORDER BY id ASC";

$result = mysqli_query($konek, $sql);

if (!$result) {
    echo json_encode(['error' => 'Query gagal: ' . mysqli_error($konek)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

if (empty($data)) {
    echo json_encode(['error' => 'Tidak ada data', 'data' => []]);
} else {
    echo json_encode(['data' => $data]);
}

mysqli_close($konek);
?>