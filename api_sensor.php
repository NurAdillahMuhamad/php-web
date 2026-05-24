<?php
// ================================================================
// api_sensor.php — Data historis untuk grafik (REVISED)
// Perubahan:
// 1. Koneksi DB pakai environment variable Railway
// 2. Hapus persentase_warna dari query & response
// ================================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ── KONEKSI DATABASE (Railway env variable) ──────────────────────
$db_host = getenv('MYSQLHOST')     ?: 'localhost';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';
$db_port = (int)(getenv('MYSQLPORT') ?: 3306);

$konek = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$konek) {
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}

// ── PARAMETER TANGGAL ────────────────────────────────────────────
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $tanggal = date('Y-m-d');
}

// ── QUERY DATA SENSOR PER TANGGAL ────────────────────────────────
// Ambil data per 1 jam (GROUP BY jam) untuk efisiensi grafik
$sql = "SELECT
            DATE_FORMAT(waktu, '%Y-%m-%d %H:00:00') AS waktu,
            ROUND(AVG(pH), 2)     AS pH,
            ROUND(AVG(cahaya))    AS cahaya,
            MAX(warna)            AS warna,
            MAX(pompa_basa)       AS pompa_basa,
            MAX(pompa_normal)     AS pompa_normal,
            MAX(pompa_nutrisi)    AS pompa_nutrisi,
            MAX(uv)               AS uv
        FROM mikroalga_sensor
        WHERE DATE(waktu) = '$tanggal'
          AND pH IS NOT NULL
        GROUP BY DATE_FORMAT(waktu, '%Y-%m-%d %H:00:00')
        ORDER BY waktu ASC";

$result = mysqli_query($konek, $sql);

if (!$result) {
    echo json_encode(['error' => mysqli_error($konek)]);
    mysqli_close($konek);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'waktu'         => $row['waktu'],
        'pH'            => (float)$row['pH'],
        'cahaya'        => (int)$row['cahaya'],
        'warna'         => $row['warna'] ?? 'tidak terdeteksi',
        'pompa_basa'    => $row['pompa_basa']    ?? 'IDLE',
        'pompa_normal'  => $row['pompa_normal']  ?? 'IDLE',
        'pompa_nutrisi' => $row['pompa_nutrisi'] ?? 'OFF',
        'uv'            => $row['uv']            ?? 'OFF',
    ];
}

echo json_encode([
    'tanggal' => $tanggal,
    'total'   => count($data),
    'data'    => $data,
]);

mysqli_close($konek);
?>
