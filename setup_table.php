<?php
$konek = mysqli_connect(getenv('MYSQLHOST'), getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'), getenv('MYSQLDATABASE'), getenv('MYSQLPORT'));
if (!$konek) die('Koneksi gagal');

$sql = "CREATE TABLE IF NOT EXISTS foto_metadata (
    id INT PRIMARY KEY AUTO_INCREMENT,
    file_id VARCHAR(255) UNIQUE NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    tanggal DATE NOT NULL,
    jam TIME NOT NULL,
    fase VARCHAR(100),
    status_warna VARCHAR(100),
    skor FLOAT DEFAULT 0.0,
    waktu_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    kolam_id INT DEFAULT 1,
    KEY idx_tanggal_kolam (tanggal, kolam_id),
    KEY idx_fase (fase),
    KEY idx_waktu (waktu_upload)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($konek, $sql)) echo "✅ Tabel berhasil dibuat";
else echo "❌ Error: " . mysqli_error($konek);
mysqli_close($konek);
?>
