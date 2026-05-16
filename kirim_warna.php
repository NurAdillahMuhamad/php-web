<?php
$konek = mysqli_connect("localhost", "root", "", "mikroalga");

$warna        = isset($_GET['warna'])        ? $_GET['warna']        : 'tidak terdeteksi';
$status_warna = isset($_GET['status_warna']) ? $_GET['status_warna'] : '-';
$persentase   = isset($_GET['persentase'])   ? $_GET['persentase']   : 0;

// Langsung update id terakhir
$id_query = mysqli_query($konek, "SELECT MAX(id) as max_id FROM mikroalga_sensor");
$id_row   = mysqli_fetch_assoc($id_query);
$max_id   = $id_row['max_id'];

if ($max_id) {
    $sql = "UPDATE mikroalga_sensor 
            SET warna='$warna', status_warna='$status_warna', persentase_warna='$persentase' 
            WHERE id='$max_id'";
    $result = mysqli_query($konek, $sql);

    if ($result) {
        echo "OK - id $max_id - warna: $warna";
    } else {
        echo "GAGAL: " . mysqli_error($konek);
    }
} else {
    echo "TIDAK ADA DATA";
}
?>