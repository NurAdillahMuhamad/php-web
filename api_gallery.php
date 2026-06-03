<?php
// ================================================================
// api_gallery.php — Gallery foto dengan pagination & filter tanggal
// ================================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Asia/Jakarta');

// ── PARAMETER ────────────────────────────────────────────────────
$dari   = isset($_GET['dari'])   ? $_GET['dari']   : date('Y-m-d');
$sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
$page   = isset($_GET['page'])   ? max(1, (int)$_GET['page']) : 1;
$kolam  = isset($_GET['kolam'])  ? (int)$_GET['kolam'] : 1;

$per_page = 12;
$offset = ($page - 1) * $per_page;

// ── VALIDASI TANGGAL ─────────────────────────────────────────────
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))  $dari  = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) $sampai = date('Y-m-d');

// ── KONEKSI DATABASE ─────────────────────────────────────────────
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

// ── ESCAPE INPUT ──────────────────────────────────────────────────
$dari  = mysqli_real_escape_string($konek, $dari);
$sampai = mysqli_real_escape_string($konek, $sampai);

// ── COUNT TOTAL FOTO ──────────────────────────────────────────────
$q_count = mysqli_query($konek,
    "SELECT COUNT(*) as total FROM foto_metadata
     WHERE tanggal BETWEEN '$dari' AND '$sampai'
     AND kolam_id = $kolam"
);
$count_row = mysqli_fetch_assoc($q_count);
$total = $count_row['total'] ?? 0;
$total_pages = ceil($total / $per_page);

// ── QUERY FOTO DENGAN PAGINATION ──────────────────────────────────
$sql = "SELECT 
            id,
            file_id,
            file_name,
            tanggal,
            jam,
            fase,
            status_warna,
            skor
        FROM foto_metadata
        WHERE tanggal BETWEEN '$dari' AND '$sampai'
        AND kolam_id = $kolam
        ORDER BY tanggal DESC, jam DESC
        LIMIT $offset, $per_page";

$result = mysqli_query($konek, $sql);
if (!$result) {
    echo json_encode(['error' => mysqli_error($konek)]);
    mysqli_close($konek);
    exit;
}

$fotos = [];
while ($row = mysqli_fetch_assoc($result)) {
    // ★ Construct Google Drive preview URL
    // Format: https://drive.google.com/uc?id=FILE_ID&export=view
    $preview_url = "https://drive.google.com/uc?id=" . urlencode($row['file_id']) . "&export=view";
    
    $fotos[] = [
        'id'          => (int)$row['id'],
        'file_id'     => $row['file_id'],
        'file_name'   => $row['file_name'],
        'tanggal'     => $row['tanggal'],
        'jam'         => $row['jam'],
        'fase'        => $row['fase'] ?? 'tidak terdeteksi',
        'status_warna'=> $row['status_warna'] ?? '-',
        'skor'        => (float)$row['skor'],
        'file_url'    => $preview_url,  // ★ URL untuk preview/download
    ];
}

// ── FASE STATS (terbaru terdeteksi) ────────────────────────────────
$q_fase = mysqli_query($konek,
    "SELECT fase FROM foto_metadata
     WHERE tanggal BETWEEN '$dari' AND '$sampai'
     AND kolam_id = $kolam
     AND fase IS NOT NULL
     AND fase != 'tidak terdeteksi'
     ORDER BY tanggal DESC, jam DESC LIMIT 1"
);
$fase_row = mysqli_fetch_assoc($q_fase);
$fase_terbaru = $fase_row['fase'] ?? null;

// ── RESPONSE JSON ──────────────────────────────────────────────────
$response = [
    'dari'          => $dari,
    'sampai'        => $sampai,
    'kolam'         => $kolam,
    'page'          => $page,
    'per_page'      => $per_page,
    'total'         => $total,
    'total_pages'   => $total_pages,
    'fase_terbaru'  => $fase_terbaru,
    'fotos'         => $fotos,
];

echo json_encode($response, JSON_UNESCAPED_SLASHES);
mysqli_close($konek);
?>
