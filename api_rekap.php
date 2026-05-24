<?php
// ================================================================
// api_rekap.php — Data rekapitulasi untuk halaman rekap
// Filter: tanggal awal - tanggal akhir
// Data per 1 jam, dengan export CSV
// ================================================================

header('Access-Control-Allow-Origin: *');

// ── KONEKSI DATABASE ─────────────────────────────────────────────
$db_host = getenv('MYSQLHOST')     ?: 'localhost';
$db_user = getenv('MYSQLUSER')     ?: 'root';
$db_pass = getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('MYSQLDATABASE') ?: 'railway';
$db_port = (int)(getenv('MYSQLPORT') ?: 3306);

$konek = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);
if (!$konek) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}

// ── PARAMETER ────────────────────────────────────────────────────
$tgl_awal  = isset($_GET['dari'])   ? $_GET['dari']   : date('Y-m-d');
$tgl_akhir = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
$export    = isset($_GET['export']) && $_GET['export'] === 'csv';

// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_awal))  $tgl_awal  = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_akhir)) $tgl_akhir = date('Y-m-d');

// Escape
$tgl_awal  = mysqli_real_escape_string($konek, $tgl_awal);
$tgl_akhir = mysqli_real_escape_string($konek, $tgl_akhir);

// ── QUERY DATA PER JAM ───────────────────────────────────────────
$sql = "SELECT
            DATE_FORMAT(waktu, '%Y-%m-%d %H:00:00') AS waktu_jam,
            ROUND(AVG(pH), 2)     AS pH,
            ROUND(AVG(cahaya))    AS cahaya,
            MAX(warna)            AS warna,
            MAX(pompa_basa)       AS pompa_basa,
            MAX(pompa_normal)     AS pompa_normal,
            MAX(pompa_nutrisi)    AS pompa_nutrisi,
            MAX(uv)               AS uv
        FROM mikroalga_sensor
        WHERE DATE(waktu) BETWEEN '$tgl_awal' AND '$tgl_akhir'
          AND pH IS NOT NULL
        GROUP BY DATE_FORMAT(waktu, '%Y-%m-%d %H:00:00')
        ORDER BY waktu_jam DESC";

$result = mysqli_query($konek, $sql);
if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => mysqli_error($konek)]);
    mysqli_close($konek);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'waktu'         => $row['waktu_jam'],
        'pH'            => (float)($row['pH'] ?? 0),
        'cahaya'        => (int)($row['cahaya'] ?? 0),
        'warna'         => $row['warna'] ?? 'tidak terdeteksi',
        'pompa_basa'    => $row['pompa_basa']    ?? 'IDLE',
        'pompa_normal'  => $row['pompa_normal']  ?? 'IDLE',
        'pompa_nutrisi' => $row['pompa_nutrisi'] ?? 'OFF',
        'uv'            => $row['uv']            ?? 'OFF',
    ];
}

// ── NUTRISI TERAKHIR ─────────────────────────────────────────────
$q_nut = mysqli_query($konek,
    "SELECT waktu FROM mikroalga_sensor
     WHERE pompa_nutrisi = 'ON'
     ORDER BY id DESC LIMIT 1"
);
$nut_row          = mysqli_fetch_assoc($q_nut);
$nutrisi_terakhir = $nut_row['waktu'] ?? null;

mysqli_close($konek);

// ── EXPORT CSV ───────────────────────────────────────────────────
if ($export) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="rekap_mikroalga_' . $tgl_awal . '_' . $tgl_akhir . '.csv"');
    $out = fopen('php://output', 'w');
    // BOM untuk Excel
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($out, ['Tanggal & Jam', 'pH', 'Cahaya (Lux)', 'Warna Air', 'Pompa Basa', 'Pompa Air', 'Pompa Nutrisi', 'Lampu UV']);
    foreach ($data as $r) {
        fputcsv($out, [
            $r['waktu'],
            $r['pH'],
            $r['cahaya'],
            $r['warna'],
            $r['pompa_basa'],
            $r['pompa_normal'],
            $r['pompa_nutrisi'],
            $r['uv'],
        ]);
    }
    fclose($out);
    exit;
}

// ── RESPONSE JSON ────────────────────────────────────────────────
header('Content-Type: application/json');
echo json_encode([
    'dari'             => $tgl_awal,
    'sampai'           => $tgl_akhir,
    'total'            => count($data),
    'nutrisi_terakhir' => $nutrisi_terakhir,
    'data'             => $data,
]);
?>
