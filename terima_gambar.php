<?php
// ================================================================
// terima_gambar.php — Terima foto dari ESP32-CAM
// CATATAN: Di Railway, filesystem bersifat ephemeral (reset saat redeploy)
// Untuk penyimpanan permanen, sebaiknya pakai Google Drive / Cloudinary
// ================================================================

$imageData = file_get_contents("php://input");

if (empty($imageData)) {
    http_response_code(400);
    echo "KOSONG";
    exit;
}

$path   = __DIR__ . "/frame_terbaru.jpg";
$result = file_put_contents($path, $imageData);

if ($result === false) {
    http_response_code(500);
    echo "GAGAL SIMPAN";
} else {
    echo "OK";
}
?>
