<?php
// ================================================================
// db.php — Koneksi database terpusat (PDO)
// ================================================================

$db_host = getenv('MYSQLHOST')     ?: 'interchange.proxy.rlwy.net';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: 'sYnrfrsKBLSVfxaKSqRKxbPSkuxYlZsL';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';
$db_port = getenv('MYSQLPORT')     ?: 11403;

try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 10,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi DB gagal: ' . $e->getMessage()]);
    exit;
}
?>
