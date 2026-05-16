<?php
$token = "ISI_TOKEN_BOT_KAMU";
$konek = mysqli_connect("localhost", "root", "", "mikroalga");

// Terima pesan dari Telegram
$data    = json_decode(file_get_contents("php://input"), true);
$pesan   = strtolower(trim($data['message']['text']));
$chat_id = $data['message']['chat']['id'];

// Cek pesan yang masuk
if ($pesan == "cek") {

    // Ambil data sensor terbaru
    $sensor = mysqli_query($konek, "SELECT * FROM mikroalga_sensor ORDER BY id DESC LIMIT 1");
    $d      = mysqli_fetch_assoc($sensor);

    // Ambil status pompa
    $pompa = mysqli_query($konek, "SELECT * FROM status_pompa WHERE id=1");
    $p     = mysqli_fetch_assoc($pompa);

    // Susun pesan balasan
    $balas = "Kondisi Mikroalga:\n";
    $balas .= "pH     : " . $d['pH']   . "\n";
    $balas .= "Suhu   : " . $d['suhu'] . "°C\n";
    $balas .= "Cahaya : " . $d['cahaya'] . "\n";
    $balas .= "Pompa  : " . $p['status'] . "\n";
    $balas .= "Mode   : " . $p['mode'];

} else {
    $balas = "Ketik *cek* untuk lihat kondisi mikroalga.";
}

// Kirim balasan ke Telegram
$url = "https://api.telegram.org/bot$token/sendMessage";
file_get_contents("$url?chat_id=$chat_id&text=" . urlencode($balas));
?>