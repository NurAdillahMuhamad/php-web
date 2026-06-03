<?php
// ================================================================
// migrate_foto_metadata.php
// Migrasi tabel foto_metadata dari struktur lama ke struktur baru.
// Jalankan SEKALI lalu hapus file ini.
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
    $pdo = new PDO($dsn, $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $results[] = ['ok' => true, 'msg' => "✅ Koneksi DB berhasil"];
} catch (PDOException $e) {
    die("<pre>❌ Koneksi DB gagal: " . $e->getMessage() . "</pre>");
}

// ── AMBIL KOLOM YANG ADA SEKARANG ───────────────────────────────
$existing_cols = [];
try {
    $cols = $pdo->query("DESCRIBE foto_metadata")->fetchAll(PDO::FETCH_ASSOC);
    $existing_cols = array_column($cols, 'Field');
    $results[] = ['ok' => true, 'msg' => "📋 Kolom saat ini: " . implode(', ', $existing_cols)];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'msg' => "❌ Tabel tidak ditemukan: " . $e->getMessage()];
    goto render;
}

// ── DEFINISI KOLOM YANG DIBUTUHKAN ──────────────────────────────
// key = nama kolom baru, value = [tipe, after_kolom, rename_dari (jika ada)]
$needed = [
    'device_id'      => ['VARCHAR(50) NOT NULL DEFAULT "unknown"', 'id',             null],
    'kolam'          => ['INT NOT NULL DEFAULT 1',                  'device_id',      'kolam_id'],
    'gdrive_file_id' => ['VARCHAR(255) DEFAULT NULL',               'kolam',          'file_id'],
    'gdrive_url'     => ['VARCHAR(512) DEFAULT NULL',               'gdrive_file_id', null],
    'folder_name'    => ['VARCHAR(100) DEFAULT NULL',               'gdrive_url',     'tanggal'],
    'file_name'      => ['VARCHAR(200) DEFAULT NULL',               'folder_name',    'file_name'], // sudah ada
    'warna'          => ['VARCHAR(100) DEFAULT NULL',               'file_name',      'fase'],
    'status_warna'   => ['VARCHAR(100) DEFAULT NULL',               'warna',          'status_warna'], // sudah ada
    'skor'           => ['FLOAT DEFAULT 0',                         'status_warna',   'skor'], // sudah ada
    'waktu'          => ['DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', 'skor',       'waktu_upload'],
];

// ── JALANKAN MIGRASI ─────────────────────────────────────────────
foreach ($needed as $new_col => [$type, $after, $rename_from]) {

    $col_ada = in_array($new_col, $existing_cols);

    if ($col_ada) {
        // Kolom sudah ada dengan nama yang benar, skip
        $results[] = ['ok' => true, 'msg' => "⏭️  Kolom '$new_col' sudah ada, skip"];
        continue;
    }

    if ($rename_from && in_array($rename_from, $existing_cols)) {
        // RENAME kolom lama → baru
        try {
            $pdo->exec("ALTER TABLE foto_metadata CHANGE `$rename_from` `$new_col` $type");
            $results[] = ['ok' => true, 'msg' => "🔄 RENAME kolom '$rename_from' → '$new_col' ($type)"];
            // Update daftar existing agar loop berikutnya tahu kolom sudah berganti
            $existing_cols = array_diff($existing_cols, [$rename_from]);
            $existing_cols[] = $new_col;
        } catch (PDOException $e) {
            $results[] = ['ok' => false, 'msg' => "❌ Gagal RENAME '$rename_from' → '$new_col': " . $e->getMessage()];
        }
    } else {
        // ADD kolom baru
        try {
            $pdo->exec("ALTER TABLE foto_metadata ADD COLUMN `$new_col` $type AFTER `$after`");
            $results[] = ['ok' => true, 'msg' => "➕ ADD kolom '$new_col' ($type)"];
            $existing_cols[] = $new_col;
        } catch (PDOException $e) {
            $results[] = ['ok' => false, 'msg' => "❌ Gagal ADD '$new_col': " . $e->getMessage()];
        }
    }
}

// ── HAPUS KOLOM LAMA YANG TIDAK DIPAKAI ─────────────────────────
$new_col_names = array_keys($needed);
$new_col_names[] = 'id'; // jangan hapus id
$to_drop = array_diff($existing_cols, $new_col_names);

foreach ($to_drop as $drop_col) {
    try {
        $pdo->exec("ALTER TABLE foto_metadata DROP COLUMN `$drop_col`");
        $results[] = ['ok' => true, 'msg' => "🗑️  DROP kolom '$drop_col' (tidak dipakai)"];
    } catch (PDOException $e) {
        $results[] = ['ok' => false, 'msg' => "⚠️  Gagal DROP '$drop_col': " . $e->getMessage()];
    }
}

// ── TAMBAH INDEX JIKA BELUM ADA ──────────────────────────────────
$indexes = [
    'idx_waktu' => 'CREATE INDEX idx_waktu ON foto_metadata (waktu)',
    'idx_kolam' => 'CREATE INDEX idx_kolam ON foto_metadata (kolam)',
    'idx_warna' => 'CREATE INDEX idx_warna ON foto_metadata (warna(50))',
];
foreach ($indexes as $idx_name => $idx_sql) {
    try {
        $pdo->exec($idx_sql);
        $results[] = ['ok' => true, 'msg' => "🔍 INDEX '$idx_name' ditambahkan"];
    } catch (PDOException $e) {
        // Index sudah ada = OK
        $results[] = ['ok' => true, 'msg' => "⏭️  INDEX '$idx_name' sudah ada, skip"];
    }
}

// ── CEK STRUKTUR AKHIR ───────────────────────────────────────────
try {
    $final_cols = $pdo->query("DESCRIBE foto_metadata")->fetchAll(PDO::FETCH_ASSOC);
    $col_names  = array_column($final_cols, 'Field');
    $results[]  = ['ok' => true, 'msg' => "✅ Struktur akhir: " . implode(', ', $col_names)];
    $count      = $pdo->query("SELECT COUNT(*) FROM foto_metadata")->fetchColumn();
    $results[]  = ['ok' => true, 'msg' => "📊 Jumlah data: $count baris"];
} catch (PDOException $e) {
    $results[] = ['ok' => false, 'msg' => "❌ " . $e->getMessage()];
}

render:
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Migrasi foto_metadata</title>
<style>
body  { font-family: monospace; background: #0d1117; color: #c9d1d9; padding: 40px; }
h1    { color: #58a6ff; border-bottom: 1px solid #30363d; padding-bottom: 10px; }
.ok   { color: #3fb950; }
.err  { color: #f85149; }
.item { margin: 8px 0; font-size: 13px; }
.warn { background: #3d2b00; border: 1px solid #bb8009; padding: 16px; border-radius: 8px; margin-top: 24px; color: #e3b341; }
.ok-box { background: #0d2818; border: 1px solid #238636; padding: 16px; border-radius: 8px; margin-top: 16px; color: #3fb950; }
</style>
</head>
<body>
<h1>🔧 Migrasi Tabel foto_metadata</h1>
<?php foreach ($results as $r): ?>
<div class="item <?= $r['ok'] ? 'ok' : 'err' ?>"><?= htmlspecialchars($r['msg']) ?></div>
<?php endforeach; ?>

<?php
$has_err = count(array_filter($results, fn($r) => !$r['ok'])) > 0;
if (!$has_err):
?>
<div class="ok-box">
  ✅ <strong>Migrasi selesai tanpa error!</strong><br><br>
  Langkah selanjutnya:<br>
  1. Hapus file <code>migrate_foto_metadata.php</code> ini dari server<br>
  2. Deploy <code>warna_endpoint.py</code> yang baru ke Railway Worker<br>
  3. Foto berikutnya dari ESP32-CAM akan otomatis tersimpan di <code>foto_metadata</code>
</div>
<?php endif; ?>

<div class="warn">
  ⚠️ <strong>PENTING:</strong> Hapus file <code>migrate_foto_metadata.php</code> setelah migrasi selesai!
</div>
</body>
</html>
