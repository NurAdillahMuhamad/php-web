<?php
// ================================================================
// api_download.php — Download foto range tanggal sebagai ZIP
// ================================================================

date_default_timezone_set('Asia/Jakarta');

// ── PARAMETER ────────────────────────────────────────────────────
$dari   = isset($_GET['dari'])   ? $_GET['dari']   : null;
$sampai = isset($_GET['sampai']) ? $_GET['sampai'] : null;
$kolam  = isset($_GET['kolam'])  ? (int)$_GET['kolam'] : 1;

// Validasi
if (!$dari || !$sampai || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter dari dan sampai harus format YYYY-MM-DD']);
    exit;
}

// ── KONEKSI DATABASE ─────────────────────────────────────────────
$db_host = getenv('MYSQLHOST')     ?: 'localhost';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';
$db_port = (int)(getenv('MYSQLPORT') ?: 3306);

$konek = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
if (!$konek) {
    http_response_code(500);
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}

// ── QUERY FOTO DALAM RANGE TANGGAL ────────────────────────────────
$dari_esc   = mysqli_real_escape_string($konek, $dari);
$sampai_esc = mysqli_real_escape_string($konek, $sampai);

$sql = "SELECT file_id, file_name, tanggal, jam, fase
        FROM foto_metadata
        WHERE tanggal BETWEEN '$dari_esc' AND '$sampai_esc'
        AND kolam_id = $kolam
        ORDER BY tanggal ASC, jam ASC";

$result = mysqli_query($konek, $sql);
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => mysqli_error($konek)]);
    exit;
}

$fotos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $fotos[] = $row;
}

mysqli_close($konek);

if (empty($fotos)) {
    http_response_code(404);
    echo json_encode(['error' => 'Tidak ada foto dalam range tanggal ini']);
    exit;
}

// ── CREATE TEMPORARY ZIP FILE ──────────────────────────────────────
$zip_name = 'foto_kolam_' . $dari . '_' . $sampai . '.zip';
$zip_path = sys_get_temp_dir() . '/' . $zip_name;

$zip = new ZipArchive();
if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal membuat ZIP file']);
    exit;
}

// ── DOWNLOAD FOTO DARI GOOGLE DRIVE & MASUKKAN KE ZIP ─────────────
require_once 'vendor/autoload.php';
$token_json = getenv('GOOGLE_TOKEN_JSON');

if (!$token_json) {
    http_response_code(500);
    echo json_encode(['error' => 'Google Drive token tidak configured']);
    exit;
}

try {
    $token_data = json_decode($token_json, true);
    $client = new \Google_Client();
    $client->setClientId($token_data['client_id']);
    $client->setClientSecret($token_data['client_secret']);
    $client->refreshToken($token_data['refresh_token']);
    
    $drive_service = new \Google_Service_Drive($client);
    
    $downloaded = 0;
    foreach ($fotos as $foto) {
        try {
            $file_id = $foto['file_id'];
            $response = $drive_service->files->get($file_id, array('alt' => 'media'), array('supportsAllDrives' => true));
            $content = $response->getBody()->getContents();
            
            // ★ Nama file dalam ZIP: Tanggal_Jam_Fase
            $zip_entry_name = $foto['tanggal'] . '_' . str_replace(':', '', $foto['jam']) . '_' . str_replace(' ', '_', $foto['fase']) . '.jpg';
            $zip->addFromString($zip_entry_name, $content);
            
            $downloaded++;
        } catch (Exception $e) {
            // Skip jika foto tidak bisa download, lanjut ke yang berikutnya
            error_log("Failed to download $file_id: " . $e->getMessage());
        }
    }
    
    $zip->close();
    
    if ($downloaded === 0) {
        http_response_code(500);
        echo json_encode(['error' => 'Tidak ada foto yang berhasil di-download dari Google Drive']);
        unlink($zip_path);
        exit;
    }
    
    // ── SEND ZIP FILE ────────────────────────────────────────────
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_name . '"');
    header('Content-Length: ' . filesize($zip_path));
    
    readfile($zip_path);
    
    // Cleanup
    unlink($zip_path);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    if (file_exists($zip_path)) unlink($zip_path);
}
?>
