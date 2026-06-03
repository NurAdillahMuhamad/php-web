<?php
// ================================================================
// setup_db.php — Buat tabel foto_metadata di Railway MySQL
// Jalankan SEKALI di browser: https://your-app.railway.app/setup_db.php
// Hapus file ini setelah berhasil!
// ================================================================

header('Content-Type: text/html; charset=utf-8');

$db_host = getenv('MYSQLHOST')     ?: 'localhost';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';
$db_port = (int)(getenv('MYSQLPORT') ?: 3306);

$results = [];

try {
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $results[] = ['ok' => true, 'msg' => "✅ Koneksi DB berhasil ($db_host:$db_port/$db_name)"];
} catch (PDOException $e) {
    die("<pre>❌ Koneksi DB gagal: " . $e->getMessage() . "</pre>");
}

// ── BUAT TABEL foto_metadata ─────────────────────────────────────
$sql_create = "
CREATE TABLE IF NOT EXISTS foto_metadata (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    device_id      VARCHAR(50)   NOT NULL DEFAULT 'unknown',
    kolam          INT           NOT NULL DEFAULT 1,
    gdrive_file_id VARCHAR(255)  DEFAULT NULL,
    gdrive_url     VARCHAR(512)  DEFAULT NULL,
    folder_name    VARCHAR(100)  DEFAULT NULL,
    file_name      VARCHAR(200)  DEFAULT NULL,
    warna          VARCHAR(100)  DEFAULT NULL,
    status_warna   VARCHAR(100)  DEFAULT NULL,
    skor           FLOAT         DEFAULT 0,
    waktu          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_waktu  (waktu),
    INDEX idx_kolam  (kolam),
    INDEX idx_warna  (warna)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $pdo->exec($sql_create);
    $results[] = ['ok' => true, 'msg' => '✅ Tabel foto_metadata berhasil dibuat (atau sudah ada)'];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'msg' => '❌ Gagal buat tabel foto_metadata: ' . $e->getMessage()];
}

// ── CEK STRUKTUR TABEL ───────────────────────────────────────────
try {
    $cols = $pdo->query("DESCRIBE foto_metadata")->fetchAll();
    $col_names = array_column($cols, 'Field');
    $results[] = ['ok' => true, 'msg' => '📋 Kolom tabel: ' . implode(', ', $col_names)];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'msg' => '❌ Gagal cek struktur: ' . $e->getMessage()];
}

// ── CEK JUMLAH DATA ──────────────────────────────────────────────
try {
    $count = $pdo->query("SELECT COUNT(*) as n FROM foto_metadata")->fetch()['n'];
    $results[] = ['ok' => true, 'msg' => "📊 Jumlah data foto_metadata saat ini: $count baris"];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'msg' => '⚠️ Tabel ada tapi tidak bisa dihitung: ' . $e->getMessage()];
}

// ── CEK TABEL mikroalga_sensor (pastikan ada) ────────────────────
try {
    $n = $pdo->query("SELECT COUNT(*) as n FROM mikroalga_sensor")->fetch()['n'];
    $results[] = ['ok' => true, 'msg' => "✅ mikroalga_sensor: $n baris (OK)"];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'msg' => '❌ mikroalga_sensor tidak ditemukan: ' . $e->getMessage()];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Setup DB — Foto Metadata</title>
<style>
body { font-family: monospace; background: #0d1117; color: #c9d1d9; padding: 40px; }
h1   { color: #58a6ff; border-bottom: 1px solid #30363d; padding-bottom: 10px; }
.ok  { color: #3fb950; }
.err { color: #f85149; }
.item { margin: 10px 0; font-size: 14px; }
.warn { background: #3d2b00; border: 1px solid #bb8009; padding: 16px; border-radius: 8px; margin-top: 24px; color: #e3b341; }
</style>
</head>
<body>
<h1>🛠️ Setup Database — foto_metadata</h1>
<?php foreach ($results as $r): ?>
<div class="item <?= $r['ok'] ? 'ok' : 'err' ?>"><?= htmlspecialchars($r['msg']) ?></div>
<?php endforeach; ?>

<div class="warn">
⚠️ <strong>PENTING:</strong> Hapus file <code>setup_db.php</code> setelah setup selesai!<br>
File ini tidak boleh ada di production karena bisa diakses siapa saja.
</div>

<p style="margin-top:24px;color:#8b949e;">
  Setelah berhasil, lanjutkan dengan deploy update <code>warna_endpoint.py</code>
  agar metadata foto tersimpan otomatis.
</p>
</body>
</html>
