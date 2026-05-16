<?php
$imageData = file_get_contents("php://input");

if (empty($imageData)) {
    http_response_code(400);
    echo "KOSONG";
    exit;
}

$path = __DIR__ . "/frame_terbaru.jpg";
$result = file_put_contents($path, $imageData);

if ($result === false) {
    echo "GAGAL SIMPAN";
} else {
    echo "OK";
}
?>