<?php
// ================================================================
// api_rekap.php — Data rekapitulasi untuk halaman rekap
// ★ UPDATED: Timezone WIB conversion
// ...
header('Access-Control-Allow-Origin: *');

date_default_timezone_set('Asia/Jakarta');

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
$sub       = isset($_GET['sub'])    ? (int)$_GET['sub'] : 1;
$export    = isset($_GET['export']) && $_GET['export'] === 'csv';

// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_awal))  $tgl_awal  = date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_akhir)) $tgl_akhir = date('Y-m-d');
if (!in_array($sub, [1, 2, 3])) $sub = 1;

// Escape
$tgl_awal  = mysqli_real_escape_string($konek, $tgl_awal);
$tgl_akhir = mysqli_real_escape_string($konek, $tgl_akhir);

// ── QUERY DATA PER JAM ───────────────────────────────────────────
$sql = "SELECT
            MIN(DATE_ADD(waktu, INTERVAL 7 HOUR))              AS waktu_jam,
            ROUND(AVG(pH), 2)                                  AS pH,
            ROUND(AVG(cahaya))                                 AS cahaya,
            MAX(warna)                                         AS warna,
            MAX(CASE WHEN pompa_basa   = 'DOSING' THEN 'ON' ELSE 'OFF' END) AS pompa_basa,
            MAX(CASE WHEN pompa_normal = 'DOSING' THEN 'ON' ELSE 'OFF' END) AS pompa_normal,
            MAX(pompa_nutrisi)                                 AS pompa_nutrisi,
            MAX(uv)                                            AS uv,
            MAX(vol_basa)                                      AS vol_basa,
            MAX(vol_normal)                                    AS vol_normal
        FROM mikroalga_sensor
        WHERE DATE(DATE_ADD(waktu, INTERVAL 7 HOUR)) BETWEEN '$tgl_awal' AND '$tgl_akhir'
          AND pH IS NOT NULL
        GROUP BY DATE_FORMAT(DATE_ADD(waktu, INTERVAL 7 HOUR), '%Y-%m-%d %H')
        ORDER BY waktu_jam ASC";

$result = mysqli_query($konek, $sql);
if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => mysqli_error($konek)]);
    mysqli_close($konek);
    exit;
}

// ── PROSES DATA ──────────────────────────────────────────────────
$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ph = (float)($row['pH'] ?? 0);

    if ($ph < 8.5) {
        $kondisi_ph = 'Rendah';
    } elseif ($ph > 10.5) {
        $kondisi_ph = 'Tinggi';
    } else {
        $kondisi_ph = 'Normal';
    }

    $data[] = [
        'waktu'         => $row['waktu_jam'],
        'pH'            => $ph,
        'kondisi_ph'    => $kondisi_ph,
        'cahaya'        => (int)($row['cahaya'] ?? 0),
        'warna'         => $row['warna'] ?? 'tidak terdeteksi',
        'pompa_basa'    => $row['pompa_basa']    ?? 'OFF',
        'pompa_normal'  => $row['pompa_normal']  ?? 'OFF',
        'pompa_nutrisi' => $row['pompa_nutrisi'] ?? 'OFF',
        'uv'            => $row['uv']            ?? 'OFF',
        'vol_basa'      => (float)($row['vol_basa']   ?? 0),
        'vol_normal'    => (float)($row['vol_normal']  ?? 0),
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
    header('Content-Disposition: attachment; filename="rekap_sub' . $sub . '_' . $tgl_awal . '_' . $tgl_akhir . '.csv"');
    $out = fopen('php://output', 'w');
    // BOM untuk Excel/WPS
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
    // Hint delimiter untuk WPS/Excel
    fprintf($out, "sep=;\n");

    if ($sub === 1) {
        fputcsv($out, ['Tanggal', 'Jam', 'pH', 'Kondisi pH', 'Pompa Basa', 'Pompa Normal', 'Vol Basa (mL)', 'Vol Normal (mL)'], ';');
        foreach ($data as $r) {
            $dt = new DateTime($r['waktu']);
            fputcsv($out, [
                $dt->format('d/m/Y'),
                $dt->format('H:i'),
                $r['pH'],
                $r['kondisi_ph'],
                $r['pompa_basa'],
                $r['pompa_normal'],
                $r['vol_basa'],
                $r['vol_normal'],
            ], ';');
        }
    } elseif ($sub === 2) {
        fputcsv($out, ['Tanggal', 'Jam', 'Cahaya (Lux)', 'UV'], ';');
        foreach ($data as $r) {
            $dt = new DateTime($r['waktu']);
            fputcsv($out, [
                $dt->format('d/m/Y'),
                $dt->format('H:i'),
                $r['cahaya'],
                $r['uv'],
            ], ';');
        }
    } elseif ($sub === 3) {
        fputcsv($out, ['Tanggal', 'Jam', 'pH', 'Warna Air', 'Pompa Nutrisi'], ';');
        foreach ($data as $r) {
            $dt = new DateTime($r['waktu']);
            fputcsv($out, [
                $dt->format('d/m/Y'),
                $dt->format('H:i'),
                $r['pH'],
                $r['warna'],
                $r['pompa_nutrisi'],
            ], ';');
        }
    }

    fclose($out);
    exit;
}

// ── RESPONSE JSON ────────────────────────────────────────────────
header('Content-Type: application/json');
echo json_encode([
    'dari'             => $tgl_awal,
    'sampai'           => $tgl_akhir,
    'sub'              => $sub,
    'total'            => count($data),
    'nutrisi_terakhir' => $nutrisi_terakhir,
    'data'             => $data,
]);
?>
