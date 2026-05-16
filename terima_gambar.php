<?php
$imageData = file_get_contents("php://input");
if (empty($imageData)) { http_response_code(400); echo "KOSONG"; exit; }
$result = file_put_contents(__DIR__ . "/frame_terbaru.jpg", $imageData);
echo $result === false ? "GAGAL" : "OK";
?>
