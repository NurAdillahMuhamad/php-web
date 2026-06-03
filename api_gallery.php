<?php
// ================================================================
// api_gallery.php — Ambil daftar foto dari foto_metadata
// Query params:
//   dari    = YYYY-MM-DD  (default: hari ini)
//   sampai  = YYYY-MM-DD  (default: hari ini)
//   page    = int         (default: 1)
//   per_page= int         (default: 12, max: 48)
//   kolam   = int         (default: 1)
// ================================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$db_host = getenv('MYSQLHOST')     ?: 'localhost';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';
$db_port = (int)(getenv('MYSQLPORT') ?: 3306);

$konek = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
if (!$konek) {
    echo json_encode(['error' => 'Koneksi DB gagal']);
    exit;
}

// ── PARAMETER ─────────────────────────────────────────────────────
$dari     = isset($_GET['dari'])     ? $_GET['dari']     : date('Y-m-d');
$sampai   = isset($_GET['sampai'])   ? $_GET['sampai']   : date('Y-m-d');
$page     = max(1, (int)($_GET['page']     ?? 1));
$per_page = min(48, max(1, (int)($_GET['per_page'] ?? 12)));
$kolam    = (int)($_GET['kolam'] ?? 1);

// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   $dari   = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) $sampai = date('Y-m-d');

$dari   = mysqli_real_escape_string($konek, $dari);
$sampai = mysqli_real_escape_string($konek, $sampai);
$offset = ($page - 1) * $per_page;

// ── CEK APAKAH TABEL ADA ─────────────────────────────────────────
$tbl_check = mysqli_query($konek,
    "SELECT COUNT(*) as n FROM information_schema.tables
     WHERE table_schema = DATABASE()
     AND table_name = 'foto_metadata'"
);
$tbl_row = mysqli_fetch_assoc($tbl_check);
if (!$tbl_row || (int)$tbl_row['n'] === 0) {
    echo json_encode([
        'error'       => 'Tabel foto_metadata belum dibuat. Jalankan setup_db.php terlebih dahulu.',
        'setup_url'   => '/setup_db.php',
        'dari'        => $dari,
        'sampai'      => $sampai,
        'kolam'       => $kolam,
        'page'        => $page,
        'per_page'    => $per_page,
        'total'       => 0,
        'total_pages' => 0,
        'fase_terbaru'=> null,
        'fotos'       => [],
    ]);
    mysqli_close($konek);
    exit;
}

// ── HITUNG TOTAL ──────────────────────────────────────────────────
$q_total = mysqli_query($konek, "
    SELECT COUNT(*) as n FROM foto_metadata
    WHERE DATE(waktu) BETWEEN '$dari' AND '$sampai'
    AND kolam = $kolam
");
$total      = (int)(mysqli_fetch_assoc($q_total)['n'] ?? 0);
$total_pages = $total > 0 ? ceil($total / $per_page) : 0;

// ── AMBIL FOTO ────────────────────────────────────────────────────
$q_fotos = mysqli_query($konek, "
    SELECT
        id,
        device_id,
        gdrive_file_id,
        gdrive_url,
        folder_name,
        file_name,
        warna,
        status_warna,
        skor,
        DATE_FORMAT(DATE_ADD(waktu, INTERVAL 7 HOUR), '%Y-%m-%d %H:%i:%s') AS waktu_wib
    FROM foto_metadata
    WHERE DATE(waktu) BETWEEN '$dari' AND '$sampai'
    AND kolam = $kolam
    ORDER BY waktu DESC
    LIMIT $per_page OFFSET $offset
");

$fotos = [];
while ($row = mysqli_fetch_assoc($q_fotos)) {
    // Buat thumbnail URL dari Google Drive
    $gdrive_id = $row['gdrive_file_id'];
    $thumb_url = $gdrive_id
        ? "https://drive.google.com/thumbnail?id={$gdrive_id}&sz=w400"
        : null;
    $view_url  = $gdrive_id
        ? "https://drive.google.com/file/d/{$gdrive_id}/view"
        : ($row['gdrive_url'] ?: null);

    $fotos[] = [
        'id'          => (int)$row['id'],
        'device_id'   => $row['device_id'],
        'thumb_url'   => $thumb_url,
        'view_url'    => $view_url,
        'folder_name' => $row['folder_name'],
        'file_name'   => $row['file_name'],
        'warna'       => $row['warna'] ?: 'tidak terdeteksi',
        'status_warna'=> $row['status_warna'] ?: '-',
        'skor'        => round((float)$row['skor'], 3),
        'waktu'       => $row['waktu_wib'],
    ];
}

// ── FASE TERBARU ──────────────────────────────────────────────────
$q_fase = mysqli_query($konek, "
    SELECT warna FROM foto_metadata
    WHERE warna IS NOT NULL
    AND warna != 'tidak terdeteksi'
    AND warna != ''
    AND kolam = $kolam
    ORDER BY waktu DESC LIMIT 1
");
$fase_terbaru = null;
if ($row_fase = mysqli_fetch_assoc($q_fase)) {
    $fase_terbaru = $row_fase['warna'];
}

mysqli_close($konek);

echo json_encode([
    'dari'        => $dari,
    'sampai'      => $sampai,
    'kolam'       => $kolam,
    'page'        => $page,
    'per_page'    => $per_page,
    'total'       => $total,
    'total_pages' => $total_pages,
    'fase_terbaru'=> $fase_terbaru,
    'fotos'       => $fotos,
]);
?>
