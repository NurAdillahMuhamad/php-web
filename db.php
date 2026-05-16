<?php
// ================================================================
// db.php — Koneksi database terpusat
// Otomatis pakai Railway env vars jika tersedia, fallback ke hardcode
// ================================================================

$db_host = getenv('MYSQLHOST')     ?: 'interchange.proxy.rlwy.net';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: 'sYnrfrsKBLSVfxaKSqRKxbPSkuxYlZsL';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';
$db_port = getenv('MYSQLPORT')     ?: 11403;

$konek = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$konek) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi DB gagal: ' . mysqli_connect_error()]);
    exit;
}

mysqli_set_charset($konek, 'utf8mb4');
?>
