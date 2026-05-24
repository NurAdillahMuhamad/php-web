<?php
require_once 'auth_check.php';
$username_display = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
<title>Monitoring ESP32 • Mikroalga Spirulina</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
    --green-dark : #0a5c47;
    --green-mid  : #0d6b53;
    --green-light: #2dc4a2;
    --bg         : #f2f6f4;
    --white      : #ffffff;
    --text       : #1a2b25;
    --text-soft  : #6b8078;
    --border     : #dce8e3;
    --shadow     : 0 4px 14px rgba(0,0,0,0.04);
    --radius-lg  : 20px;
    --radius-md  : 14px;
    --radius-sm  : 10px;
    --blue       : #2b7bd6;
    --orange     : #e67e22;
    --red        : #d94141;
    --green      : #1f9e5c;
    --sidebar-w  : 200px;
}
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Plus Jakarta Sans',sans-serif; background:var(--bg); color:var(--text); font-size:14px; line-height:1.5; overflow:hidden; }
.shell { display:flex; height:100vh; overflow:hidden; }

/* ── SIDEBAR ── */
.sidebar { width:220px; min-width:54px; height:100vh; background:var(--green-dark); display:flex; flex-direction:column; transition:width .25s,min-width .25s; overflow:hidden; flex-shrink:0; z-index:10; }
.sidebar.expanded { width:var(--sidebar-w); min-width:var(--sidebar-w); }
.sb-logo { height:56px; display:flex; align-items:center; gap:10px; padding:0 14px; border-bottom:1px solid rgba(255,255,255,.1); flex-shrink:0; overflow:hidden; white-space:nowrap; cursor:pointer; }
.sb-icon-wrap { width:28px; height:28px; border-radius:7px; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.sb-texts { flex:1; overflow:hidden; }
.sb-title { color:#fff; font-weight:800; font-size:.85rem; line-height:1.2; }
.sb-sub { color:rgba(255,255,255,.5); font-size:.6rem; font-weight:500; }
.sb-nav { flex:1; padding:10px 0; overflow:hidden; }
.sidebar-item { display:flex; align-items:center; gap:12px; height:40px; padding:0 16px; color:rgba(255,255,255,.65); font-size:.78rem; font-weight:600; cursor:pointer; border-left:3px solid transparent; transition:all .15s; text-decoration:none; white-space:nowrap; overflow:hidden; }
.sidebar-item:hover { color:#fff; background:rgba(255,255,255,.07); }
.sidebar-item.active { color:#fff; background:rgba(255,255,255,.1); border-left-color:var(--green-light); }
.sidebar-item i { font-size:.9rem; width:18px; text-align:center; flex-shrink:0; }
.nav-lbl { opacity:0; transition:opacity .15s; }
.sidebar.expanded .nav-lbl { opacity:1; }
.sb-foot { padding:10px 14px; border-top:1px solid rgba(255,255,255,.1); flex-shrink:0; overflow:hidden; white-space:nowrap; display:flex; align-items:center; gap:10px; }
.sb-av { width:28px; height:28px; border-radius:50%; background:var(--green-light); color:#fff; font-weight:800; font-size:.72rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.sb-uname { color:rgba(255,255,255,.75); font-size:.75rem; font-weight:600; opacity:0; transition:opacity .15s; }
.sidebar.expanded .sb-uname { opacity:1; }

/* ── MAIN AREA ── */
.main-area { flex:1; min-width:0; overflow-y:auto; display:flex; flex-direction:column; }
.page { display:none; width:100%; min-height:100vh; max-width:1050px; margin:0 auto; padding:14px; flex-direction:column; gap:12px; }
.page.active { display:flex; }

/* ── HEADER ── */
.top-header { background:var(--green-dark); color:#fff; padding:14px 22px; border-radius:var(--radius-lg); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
.welcome-text h2 { font-size:1.2rem; font-weight:800; letter-spacing:-.2px; margin-bottom:2px; }
.welcome-text p { font-size:.68rem; font-weight:600; opacity:.75; letter-spacing:.5px; text-transform:uppercase; }
.btn-live-header { display:flex; align-items:center; gap:8px; background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.3); color:#fff; padding:8px 16px; border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:800; cursor:pointer; transition:background .15s; }
.btn-live-header:hover { background:rgba(220,30,30,.7); border-color:transparent; }
.btn-live-header .live-dot-h { width:8px; height:8px; background:#ff4444; border-radius:50%; animation:pulse 1.2s infinite; }

/* ── STATUS STRIP ── */
.status-strip { background:var(--green-dark); color:#fff; padding:10px 22px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; font-size:.78rem; font-weight:600; flex-wrap:wrap; gap:8px; }
.online-indicator { display:flex; align-items:center; gap:8px; }
.pulse-dot { width:9px; height:9px; background:#2ecc71; border-radius:50%; animation:pulse 1.8s infinite; }
@keyframes pulse { 0%,100%{box-shadow:0 0 0 0 rgba(46,204,113,.5);} 50%{box-shadow:0 0 0 6px rgba(46,204,113,0);} }

/* ── LAYOUT ── */
.main-content { display:grid; grid-template-columns:1fr 280px; gap:12px; flex:1; align-items:stretch; }
.left-col { display:flex; flex-direction:column; gap:12px; flex:1; }
.sensor-row { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }

/* ── SENSOR CARDS ── */
.sensor-card { background:var(--white); border-radius:var(--radius-lg); padding:14px 16px; box-shadow:var(--shadow); border:1px solid var(--border); text-align:center; height:140px; }
.sensor-card-header { display:flex; align-items:center; gap:8px; margin-bottom:8px; justify-content:center; margin-top:13px; }
.sensor-card-header i { font-size:1rem; color:var(--text-soft); }
.sensor-card-header span { font-size:.65rem; font-weight:700; text-transform:uppercase; color:var(--text-soft); letter-spacing:.5px; }
.sensor-value { font-size:2.2rem; font-weight:800; line-height:1.1; color:var(--text); margin-bottom:6px; }
.sensor-unit { font-size:.65rem; font-weight:600; color:var(--text-soft); margin-left:3px; }
.badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.65rem; font-weight:700; letter-spacing:.3px; }
.badge-danger  { background:#fde8e8; color:#b91c1c; }
.badge-warning { background:#fff3e0; color:#b85e1a; }
.badge-normal  { background:#e6f5ee; color:#1a7a4c; }

/* ── WARNA CARD ── */
.warna-card-body { display:flex; flex-direction:column; align-items:center; gap:4px; margin-top:2px; }
.warna-box-wrap { position:relative; width:90%; }
.warna-box { width:100%; height:28px; border-radius:6px; display:block; }
.btn-live { position:absolute; right:4px; top:50%; transform:translateY(-50%); background:rgba(0,0,0,.55); color:#fff; border:none; border-radius:5px; font-size:.58rem; font-weight:700; padding:2px 7px; cursor:pointer; display:flex; align-items:center; gap:4px; font-family:'Plus Jakarta Sans',sans-serif; }
.btn-live:hover { background:rgba(220,20,20,.85); }
.btn-live .live-dot { width:6px; height:6px; background:#ff4444; border-radius:50%; animation:pulse 1.2s infinite; }

/* ── CHART ── */
.chart-panel { background:var(--white); border-radius:var(--radius-lg); padding:16px 18px; box-shadow:var(--shadow); border:1px solid var(--border); flex:1; display:flex; flex-direction:column; }
.chart-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; flex-wrap:wrap; gap:8px; }
.chart-header h4 { font-size:.8rem; font-weight:800; color:var(--green-dark); text-transform:uppercase; letter-spacing:.5px; }
.date-selector { display:flex; align-items:center; gap:6px; background:#f0f5f2; padding:3px 10px; border-radius:8px; }
.date-selector input { border:none; background:transparent; font-family:'Plus Jakarta Sans',sans-serif; font-weight:600; font-size:.7rem; outline:none; color:var(--text); }
.btn-today { background:var(--green-dark); color:#fff; border:none; padding:4px 10px; border-radius:6px; font-weight:700; font-size:.65rem; cursor:pointer; font-family:'Plus Jakarta Sans',sans-serif; }
.charts-row { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; flex:1; }
.mini-chart { background:#f9fbfa; border-radius:var(--radius-md); padding:10px; border:1px solid var(--border); }
.mini-chart h5 { font-size:.6rem; font-weight:700; text-transform:uppercase; color:var(--text-soft); margin-bottom:5px; letter-spacing:.4px; }
.mini-chart canvas { width:100%!important; height:100px!important; }
.chart-stats { display:flex; gap:6px; margin-top:4px; font-size:.6rem; font-weight:600; color:var(--text-soft); }

/* ── RIGHT COL ── */
.right-col { display:flex; flex-direction:column; gap:12px; flex:1; }
.control-box { background:var(--white); border-radius:var(--radius-lg); padding:14px 16px; box-shadow:var(--shadow); border:1px solid var(--border); }
.section-title { font-size:.71rem; font-weight:800; text-transform:uppercase; color:var(--green-dark); letter-spacing:.5px; margin-bottom:10px; }
.control-item { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #eef3f0; }
.control-item:last-child { border-bottom:none; }
.control-left { display:flex; align-items:center; gap:8px; }
.control-left i { font-size:.85rem; color:#1e6f5c; width:18px; }
.control-left span { font-weight:700; font-size:.75rem; }
.toggle { position:relative; display:inline-block; width:38px; height:20px; }
.toggle input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; inset:0; background:#c5d0cb; border-radius:20px; cursor:pointer; transition:.25s; }
.toggle-slider::before { content:''; position:absolute; width:14px; height:14px; left:3px; top:3px; background:#fff; border-radius:50%; transition:.25s; box-shadow:0 1px 3px rgba(0,0,0,.15); }
.toggle input:checked + .toggle-slider { background:var(--red); }
.toggle input:checked + .toggle-slider.green { background:var(--green); }
.toggle input:checked + .toggle-slider::before { transform:translateX(18px); }
.toggle input:disabled + .toggle-slider { opacity:1; cursor:default; }

/* ── LOG BOX ── */
.log-box { background:var(--white); border-radius:var(--radius-lg); padding:14px 16px; box-shadow:var(--shadow); border:1px solid var(--border); flex:1; display:flex; flex-direction:column; }
.log-list { display:flex; flex-direction:column; }
.log-item { display:flex; align-items:flex-start; gap:8px; padding:6px 0; border-bottom:1px solid #f0f4f2; }
.log-item:last-child { border-bottom:none; }
.log-dot { width:7px; height:7px; border-radius:50%; margin-top:4px; flex-shrink:0; }
.log-time { font-size:.65rem; font-weight:600; color:var(--text-soft); min-width:35px; }
.log-msg { font-size:.7rem; font-weight:600; color:var(--text); }

/* ── FOOTER ── */
.footer-bar { background:var(--green-dark); color:#fff; padding:10px 22px; border-radius:10px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; font-size:.68rem; font-weight:600; margin-top:auto; }

/* ── MODAL STREAM ── */
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.72); z-index:1000; align-items:center; justify-content:center; }
.modal-overlay.open { display:flex; }
.modal-box { background:#0f1e18; border-radius:18px; width:min(700px,96vw); overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.5); display:flex; flex-direction:column; }
.modal-header { display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-bottom:1px solid rgba(255,255,255,.08); }
.modal-header-left { display:flex; align-items:center; gap:10px; }
.modal-live-badge { background:#e53935; color:#fff; font-size:.62rem; font-weight:800; padding:3px 8px; border-radius:5px; letter-spacing:.5px; display:flex; align-items:center; gap:5px; }
.modal-live-badge .dot { width:6px; height:6px; background:#fff; border-radius:50%; animation:pulse 1s infinite; }
.modal-title { color:#fff; font-size:.85rem; font-weight:700; }
.modal-close { background:rgba(255,255,255,.1); border:none; color:#fff; width:28px; height:28px; border-radius:50%; cursor:pointer; font-size:.8rem; display:flex; align-items:center; justify-content:center; }
.modal-close:hover { background:rgba(255,255,255,.2); }
.modal-stream { position:relative; width:100%; aspect-ratio:4/3; background:#000; overflow:hidden; }
#stream-img { width:100%; height:100%; object-fit:contain; display:block; }
#bbox-overlay { position:absolute; top:0; left:0; width:100%; height:100%; pointer-events:none; }
.bbox-box { position:absolute; box-sizing:border-box; border:2.5px dashed currentColor; border-radius:4px; transition:all .3s ease; }
.bbox-corner { position:absolute; width:14px; height:14px; border-color:inherit; border-style:solid; }
.bbox-corner.tl { top:-2px; left:-2px; border-width:3px 0 0 3px; border-radius:3px 0 0 0; }
.bbox-corner.tr { top:-2px; right:-2px; border-width:3px 3px 0 0; border-radius:0 3px 0 0; }
.bbox-corner.bl { bottom:-2px; left:-2px; border-width:0 0 3px 3px; border-radius:0 0 0 3px; }
.bbox-corner.br { bottom:-2px; right:-2px; border-width:0 3px 3px 0; border-radius:0 0 3px 0; }
.bbox-label { position:absolute; top:-26px; left:-2px; background:currentColor; color:#fff; font-size:.62rem; font-weight:800; padding:3px 8px; border-radius:4px; white-space:nowrap; }
.bbox-label.inside { top:4px; }
.bbox-label span { color:#fff; }
.stream-error { display:none; flex-direction:column; align-items:center; gap:10px; color:rgba(255,255,255,.5); font-size:.8rem; font-weight:600; position:absolute; inset:0; justify-content:center; background:#000; }
.bbox-toggle-wrap { display:flex; align-items:center; gap:6px; margin-left:auto; }
.bbox-toggle-label { color:rgba(255,255,255,.55); font-size:.65rem; font-weight:600; cursor:pointer; }
.bbox-chk { cursor:pointer; accent-color:var(--green-light); width:14px; height:14px; }
.modal-overlay-info { padding:12px 18px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; border-top:1px solid rgba(255,255,255,.08); }
.fase-info { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.fase-badge { padding:4px 12px; border-radius:20px; font-size:.68rem; font-weight:700; }
.fase-badge.fase1 { background:#f9c74f; color:#5a3e00; }
.fase-badge.fase2 { background:#4caf50; color:#fff; }
.fase-badge.fase3 { background:#0a5c47; color:#fff; }
.fase-badge.fase4 { background:#1a237e; color:#fff; }
.fase-badge.none  { background:#78909c; color:#fff; }
.menit-info { color:rgba(255,255,255,.35); font-size:.62rem; font-weight:600; }

/* ══════════════════════════════════════════════
   HALAMAN REKAPITULASI
══════════════════════════════════════════════ */
.rekap-header-bar { background:var(--green-dark); color:#fff; padding:14px 22px; border-radius:var(--radius-lg); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; }
.rekap-header-bar h2 { font-size:1rem; font-weight:800; letter-spacing:-.2px; }
.rekap-header-bar p { font-size:.68rem; opacity:.75; font-weight:500; margin-top:2px; font-style:italic; }
.btn-csv { display:flex; align-items:center; gap:7px; background:var(--green-light); color:#fff; border:none; padding:9px 18px; border-radius:10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:800; cursor:pointer; transition:background .15s; }
.btn-csv:hover { background:#24a98a; }

.rekap-filter-bar { background:var(--white); border-radius:var(--radius-md); padding:14px 18px; box-shadow:var(--shadow); border:1px solid var(--border); display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.filter-group { display:flex; align-items:center; gap:8px; }
.filter-group label { font-size:.7rem; font-weight:700; color:var(--text-soft); text-transform:uppercase; letter-spacing:.4px; white-space:nowrap; }
.filter-group input[type=date] { border:1.5px solid var(--border); border-radius:8px; padding:6px 10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.78rem; font-weight:600; color:var(--text); outline:none; background:var(--bg); }
.filter-group input[type=date]:focus { border-color:var(--green-light); }
.btn-filter { background:var(--green-dark); color:#fff; border:none; padding:7px 16px; border-radius:8px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.75rem; font-weight:700; cursor:pointer; }
.status-online-pill { display:flex; align-items:center; gap:6px; background:#e6f5ee; border-radius:20px; padding:4px 12px; font-size:.68rem; font-weight:700; color:#1a7a4c; margin-left:auto; }

.rekap-table-wrap { background:var(--white); border-radius:var(--radius-lg); box-shadow:var(--shadow); border:1px solid var(--border); overflow:hidden; }
.rekap-table-scroll { overflow-x:auto; }
table.rekap-table { width:100%; border-collapse:collapse; font-size:.75rem; }
table.rekap-table thead { background:var(--green-dark); color:#fff; }
table.rekap-table thead th { padding:10px 14px; font-weight:700; text-align:left; font-size:.68rem; text-transform:uppercase; letter-spacing:.4px; white-space:nowrap; }
table.rekap-table tbody tr { border-bottom:1px solid #f0f4f2; transition:background .1s; }
table.rekap-table tbody tr:hover { background:#f7faf8; }
table.rekap-table tbody td { padding:10px 14px; font-weight:600; vertical-align:middle; white-space:nowrap; }
.td-waktu { color:var(--text-soft); font-size:.68rem!important; }
.td-ph { font-weight:800!important; }
.ph-normal  { color:#1a7a4c; }
.ph-rendah  { color:#b91c1c; }
.ph-tinggi  { color:#b85e1a; }
.td-fase { display:flex; align-items:center; gap:6px; }
.fase-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.fase-dot.f1 { background:#f9c74f; }
.fase-dot.f2 { background:#4caf50; }
.fase-dot.f3 { background:#0a5c47; }
.fase-dot.f4 { background:#1a237e; }
.fase-dot.fx { background:#b0bec5; }
.pill-on  { background:#e6f5ee; color:#1a7a4c; padding:2px 10px; border-radius:20px; font-size:.65rem; font-weight:800; }
.pill-off { background:#f5f5f5; color:#9e9e9e; padding:2px 10px; border-radius:20px; font-size:.65rem; font-weight:700; }
.pill-dosing { background:#fff3e0; color:#b85e1a; padding:2px 10px; border-radius:20px; font-size:.65rem; font-weight:800; }

.rekap-pagination { display:flex; justify-content:space-between; align-items:center; padding:12px 18px; border-top:1px solid var(--border); font-size:.72rem; font-weight:600; color:var(--text-soft); flex-wrap:wrap; gap:8px; }
.pagination-btns { display:flex; gap:4px; }
.page-btn { background:var(--bg); border:1px solid var(--border); border-radius:6px; padding:4px 10px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.7rem; font-weight:700; cursor:pointer; color:var(--text); }
.page-btn.active { background:var(--green-dark); color:#fff; border-color:var(--green-dark); }
.page-btn:hover:not(.active) { background:var(--border); }

.rekap-footer { background:var(--white); border-radius:var(--radius-md); padding:14px 18px; box-shadow:var(--shadow); border:1px solid var(--border); display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
.rekap-footer-item { display:flex; align-items:center; gap:8px; }
.rekap-footer-item i { color:var(--green-dark); font-size:.9rem; }
.rekap-footer-item .label { font-size:.68rem; font-weight:600; color:var(--text-soft); }
.rekap-footer-item .value { font-size:.85rem; font-weight:800; color:var(--text); }
.rekap-footer-item .sub { font-size:.65rem; color:var(--text-soft); font-weight:500; }

/* ── RESPONSIVE ── */
@media (max-width:950px) { .main-content{grid-template-columns:1fr;} .charts-row{grid-template-columns:1fr;} }
@media (max-width:600px) { .sensor-row{grid-template-columns:1fr;} .sidebar{width:0;min-width:0;} .sidebar.expanded{width:var(--sidebar-w);min-width:var(--sidebar-w);} }
.main-area::-webkit-scrollbar{width:5px;} .main-area::-webkit-scrollbar-track{background:transparent;} .main-area::-webkit-scrollbar-thumb{background:#c4d4cd;border-radius:5px;}
</style>
</head>
<body>
<div class="shell">

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar" id="sidebar">
    <div class="sb-logo" onclick="toggleSidebar()">
        <div class="sb-icon-wrap">🌿</div>
        <div class="sb-texts">
            <div class="sb-title">MikroAlga</div>
            <div class="sb-sub">Monitoring &amp; Kontrol</div>
        </div>
    </div>
    <nav class="sb-nav">
        <a class="sidebar-item active" id="nav-dashboard" onclick="showPage('dashboard')">
            <i class="fas fa-th-large"></i>
            <span class="nav-lbl">Dashboard</span>
        </a>
        <a class="sidebar-item" id="nav-rekap" onclick="showPage('rekap')">
            <i class="fas fa-table"></i>
            <span class="nav-lbl">Rekapitulasi</span>
        </a>
        <a class="sidebar-item" href="logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span class="nav-lbl">Logout</span>
        </a>
    </nav>
    <div class="sb-foot">
        <div class="sb-av"><?= strtoupper(substr($username_display, 0, 2)) ?></div>
        <span class="sb-uname"><?= htmlspecialchars($username_display) ?></span>
    </div>
</aside>

<!-- ══ MAIN AREA ══ -->
<div class="main-area">

    <!-- ════════════════════ HALAMAN DASHBOARD ════════════════════ -->
    <div class="page active" id="page-dashboard">

        <div class="top-header">
            <div class="welcome-text">
                <h2>Welcome Back, <?= htmlspecialchars($username_display) ?></h2>
                <p>Monitoring Dan Kontrol Kolam Mikroalga Spirulina Sp.</p>
            </div>
            <button class="btn-live-header" onclick="openLiveModal()">
                <span class="live-dot-h"></span>
                <i class="fas fa-video"></i> LIVE STREAM
            </button>
        </div>

        <div class="main-content">
            <div class="left-col">

                <!-- SENSOR CARDS -->
                <div class="sensor-row">
                    <!-- pH -->
                    <div class="sensor-card">
                        <div class="sensor-card-header">
                            <i class="fas fa-flask"></i><span>PH AIR</span>
                        </div>
                        <div class="sensor-value" id="val-ph">—</div>
                        <span class="badge badge-danger" id="badge-ph">Menunggu...</span>
                    </div>
                    <!-- Cahaya -->
                    <div class="sensor-card">
                        <div class="sensor-card-header">
                            <i class="fas fa-sun"></i><span>Intensitas Cahaya</span>
                        </div>
                        <div class="sensor-value" id="val-lux">— <span class="sensor-unit">Lux</span></div>
                        <span class="badge badge-warning" id="badge-lux">Menunggu...</span>
                    </div>
                </div>

                <!-- WARNA AIR CARD (full width) -->
                <div class="sensor-card" style="height:auto;padding:14px 16px 16px;">
                    <div class="sensor-card-header" style="justify-content:flex-start;">
                        <i class="fas fa-tint"></i><span>Warna Air Kolam</span>
                    </div>
                    <div class="warna-card-body" style="flex-direction:row;justify-content:space-between;align-items:center;gap:12px;margin-top:8px;">
                        <div class="warna-box-wrap" style="flex:1;">
                            <div class="warna-box" id="warna-box" style="background:#b0bec5;height:36px;"></div>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge badge-normal" id="badge-warna" style="font-size:.72rem;">Menunggu...</span>
                            <div style="font-size:.65rem;color:var(--text-soft);font-weight:600;margin-top:4px;" id="status-warna-text">—</div>
                        </div>
                        <button class="btn-live" onclick="openLiveModal()">
                            <span class="live-dot"></span> LIVE
                        </button>
                    </div>
                </div>

                <!-- STATUS BAR -->
                <div class="status-strip">
                    <div class="online-indicator"><span class="pulse-dot"></span> Status: Online</div>
                    <div><strong id="last-update">KOLAM 1 : —</strong></div>
                </div>

                <!-- CHART PANEL (hanya pH & Cahaya) -->
                <div class="chart-panel">
                    <div class="chart-header">
                        <h4>📊 Grafik Data Sensor Harian</h4>
                        <div class="date-selector">
                            <input type="date" id="datePicker">
                            <button class="btn-today" onclick="setToday()">Hari Ini</button>
                        </div>
                    </div>
                    <div class="charts-row">
                        <div class="mini-chart">
                            <h5>PH Air (normal 8.5 – 10.5)</h5>
                            <canvas id="chartPH"></canvas>
                            <div class="chart-stats" id="stats-ph"><span>Avg: —</span><span>Terakhir: —</span></div>
                        </div>
                        <div class="mini-chart">
                            <h5>Intensitas Cahaya (Lux)</h5>
                            <canvas id="chartCahaya"></canvas>
                            <div class="chart-stats" id="stats-lux"><span>Avg: —</span><span>Terakhir: —</span></div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="right-col">
                <div class="control-box">
                    <div class="section-title"><span>⚡</span> Kontrol Otomatis ★ FULL AUTO</div>
                    <div class="control-item">
                        <div class="control-left"><i class="fas fa-water"></i><span>Pompa Basa</span></div>
                        <label class="toggle"><input type="checkbox" id="togBasa" disabled><span class="toggle-slider"></span></label>
                    </div>
                    <div class="control-item">
                        <div class="control-left"><i class="fas fa-water"></i><span>Pompa Air Netral</span></div>
                        <label class="toggle"><input type="checkbox" id="togNormal" disabled><span class="toggle-slider"></span></label>
                    </div>
                    <div class="control-item">
                        <div class="control-left"><i class="fas fa-leaf"></i><span>Pompa Nutrisi</span></div>
                        <label class="toggle"><input type="checkbox" id="togNutrisi" disabled><span class="toggle-slider green"></span></label>
                    </div>
                    <div class="control-item">
                        <div class="control-left"><i class="fas fa-lightbulb"></i><span>Lampu UV</span></div>
                        <label class="toggle"><input type="checkbox" id="togUV" disabled><span class="toggle-slider green"></span></label>
                    </div>
                </div>

                <div class="log-box">
                    <div class="section-title"><span>📋</span> Log Aktivitas</div>
                    <div class="log-list" id="log-list">
                        <div class="log-item">
                            <div class="log-dot" style="background:#7b1fa2;"></div>
                            <div class="log-time">—</div>
                            <div class="log-msg">Menunggu data sensor...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bar">
            <span>🌱 Nutrisi terakhir: <strong id="footer-nutrisi">—</strong></span>
            <span id="footer-interval">Interval: 3 hari</span>
        </div>
    </div><!-- /page-dashboard -->


    <!-- ════════════════════ HALAMAN REKAPITULASI ════════════════════ -->
    <div class="page" id="page-rekap">

        <div class="rekap-header-bar">
            <div>
                <h2>REKAPITULASI DATA MONITORING DAN KONTROL KOLAM MIKROALGA <em>SPIRULINA SP.</em></h2>
                <p>Data diambil per 1 jam · Filter berdasarkan rentang tanggal</p>
            </div>
            <button class="btn-csv" onclick="downloadCSV()">
                <i class="fas fa-download"></i> Unduh Data (.CSV)
            </button>
        </div>

        <div class="rekap-filter-bar">
            <div class="filter-group">
                <i class="fas fa-calendar" style="color:var(--green-dark);"></i>
                <span style="font-size:.75rem;font-weight:700;color:var(--text);">Kolam 1</span>
            </div>
            <div class="filter-group">
                <label>Dari</label>
                <input type="date" id="rekap-dari">
            </div>
            <div class="filter-group">
                <label>Sampai</label>
                <input type="date" id="rekap-sampai">
            </div>
            <button class="btn-filter" onclick="loadRekap()">Tampilkan</button>
            <div class="status-online-pill">
                <span class="pulse-dot" style="width:7px;height:7px;"></span> Online
            </div>
        </div>

        <div class="rekap-table-wrap">
            <div class="rekap-table-scroll">
                <table class="rekap-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>pH</th>
                            <th>Cahaya (Lux)</th>
                            <th>Warna Air</th>
                            <th>Pompa Basa</th>
                            <th>Pompa Air</th>
                            <th>Nutrisi</th>
                            <th>UV</th>
                        </tr>
                    </thead>
                    <tbody id="rekap-tbody">
                        <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-soft);">Pilih rentang tanggal lalu klik Tampilkan</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="rekap-pagination">
                <span id="rekap-info">Menampilkan 0 entri</span>
                <div class="pagination-btns" id="rekap-pages"></div>
            </div>
        </div>

        <div class="rekap-footer">
            <div class="rekap-footer-item">
                <i class="fas fa-calendar-check"></i>
                <div>
                    <div class="label">Nutrisi Terakhir</div>
                    <div class="value" id="rekap-nutrisi-tgl">—</div>
                    <div class="sub" id="rekap-nutrisi-sub">—</div>
                </div>
            </div>
            <div class="rekap-footer-item" style="margin-left:auto;">
                <i class="fas fa-clock"></i>
                <div>
                    <div class="label">Interval Pemberian</div>
                    <div class="value">3 Menit (Testing)</div>
                </div>
            </div>
        </div>

    </div><!-- /page-rekap -->

</div><!-- /main-area -->
</div><!-- /shell -->

<!-- ══ MODAL LIVE STREAM ══ -->
<div class="modal-overlay" id="liveModal" onclick="closeLiveModalOutside(event)">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-header-left">
                <div class="modal-live-badge"><span class="dot"></span> LIVE</div>
                <span class="modal-title">ESP32-CAM — Kolam Spirulina</span>
            </div>
            <div class="bbox-toggle-wrap">
                <input type="checkbox" id="bbox-toggle" class="bbox-chk" checked>
                <label for="bbox-toggle" class="bbox-toggle-label">Tampilkan bbox</label>
            </div>
            <button class="modal-close" onclick="closeLiveModal()" style="margin-left:10px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-stream" id="modal-stream-wrap">
            <img id="stream-img" src="" alt="Live Stream ESP32-CAM"
                 onerror="showStreamError()" onload="hideStreamError()">
            <div id="bbox-overlay"></div>
            <div class="stream-error" id="stream-error">
                <i class="fas fa-video-slash"></i>
                <span>Stream tidak tersedia</span>
                <small>Pastikan ESP32-CAM aktif dan terhubung ke WiFi</small>
            </div>
        </div>
        <div class="modal-overlay-info">
            <div class="fase-info">
                <span class="fase-badge none" id="modal-fase-badge">Tidak terdeteksi</span>
                <span style="color:rgba(255,255,255,.4);font-size:.65rem;font-weight:600;" id="modal-status-warna">—</span>
            </div>
            <span class="menit-info" id="modal-menit">— menit lalu</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── CONFIG ────────────────────────────────────────────────────────
const STREAM_URL  = 'http://192.168.0.150:81/stream';
const RAILWAY_URL = 'https://worker-production-c170.up.railway.app/hasil_warna';

// ── PAGE NAVIGATION ───────────────────────────────────────────────
function showPage(name) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
    document.getElementById('page-' + name).classList.add('active');
    document.getElementById('nav-' + name).classList.add('active');
    if (name === 'rekap') initRekap();
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('expanded');
}

// ── DATE PICKER ───────────────────────────────────────────────────
function setToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('datePicker').value = today;
    loadCharts(today);
}
(function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('datePicker').value = today;
})();
document.getElementById('datePicker').addEventListener('change', function() { loadCharts(this.value); });

// ── CHARTS (pH & Cahaya only — grafik warna dihapus) ─────────────
const charts = {};
function initChart(id, color) {
    charts[id] = new Chart(document.getElementById(id), {
        type: 'line',
        data: { labels:[], datasets:[{ data:[], borderColor:color, borderWidth:2, pointRadius:0, tension:0.3, fill:true, backgroundColor:color+'22' }] },
        options: {
            maintainAspectRatio: false,
            plugins: { legend:{display:false} },
            scales: {
                x: { ticks:{font:{size:9}, maxTicksLimit:6} },
                y: { ticks:{font:{size:9}} }
            }
        }
    });
}
initChart('chartPH',     '#2d7dd2');
initChart('chartCahaya', '#e67e22');

function loadCharts(tanggal) {
    fetch('api_sensor.php?tanggal=' + tanggal + '&t=' + Date.now())
        .then(r => r.json())
        .then(res => {
            if (res.error || !res.data || !res.data.length) return;
            const d = res.data;
            const labels = d.map(r => r.waktu.slice(11,16));

            charts['chartPH'].data.labels = labels;
            charts['chartPH'].data.datasets[0].data = d.map(r => r.pH);
            charts['chartPH'].update();

            charts['chartCahaya'].data.labels = labels;
            charts['chartCahaya'].data.datasets[0].data = d.map(r => r.cahaya);
            charts['chartCahaya'].update();

            const ph  = d.map(r => parseFloat(r.pH)).filter(v => v > 0);
            const lux = d.map(r => parseInt(r.cahaya)).filter(v => v > 0);
            const avg = arr => arr.length ? (arr.reduce((a,b)=>a+b)/arr.length).toFixed(2) : '—';
            document.getElementById('stats-ph').innerHTML  = `<span>Avg: ${avg(ph)}</span><span>Terakhir: ${ph.at(-1)??'—'}</span>`;
            document.getElementById('stats-lux').innerHTML = `<span>Avg: ${avg(lux)} lux</span><span>Terakhir: ${lux.at(-1)??'—'}</span>`;
        })
        .catch(e => console.log('Chart error:', e));
}

// ── WARNA HELPER ──────────────────────────────────────────────────
function faseToBg(warna) {
    if (!warna) return '#b0bec5';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return '#f9c74f';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return '#4caf50';
    if (w.includes('optimal')    || w.includes('fase 3')) return '#0d6b53';
    if (w.includes('panen')      || w.includes('fase 4')) return '#1a237e';
    return '#b0bec5';
}
function faseToClass(warna) {
    if (!warna) return 'none';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return 'fase1';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return 'fase2';
    if (w.includes('optimal')    || w.includes('fase 3')) return 'fase3';
    if (w.includes('panen')      || w.includes('fase 4')) return 'fase4';
    return 'none';
}
function faseToBboxColor(warna) {
    if (!warna) return '#78909c';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return '#f9c74f';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return '#4caf50';
    if (w.includes('optimal')    || w.includes('fase 3')) return '#2dc4a2';
    if (w.includes('panen')      || w.includes('fase 4')) return '#7986cb';
    return '#78909c';
}

// ── STATE GLOBAL ──────────────────────────────────────────────────
let _warnaData = { warna:'tidak terdeteksi', status_warna:'-', warna_menit_lalu:null, bbox:null };
let _logItems  = [];

// ── LOAD REAL-TIME ────────────────────────────────────────────────
function addLog(color, msg) {
    const now = new Date();
    const jam = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
    _logItems.unshift({ color, jam, msg });
    if (_logItems.length > 8) _logItems.pop();
    const el = document.getElementById('log-list');
    el.innerHTML = _logItems.map(l =>
        `<div class="log-item">
            <div class="log-dot" style="background:${l.color};"></div>
            <div class="log-time">${l.jam}</div>
            <div class="log-msg">${l.msg}</div>
        </div>`
    ).join('');
}

let _prevState = {};
function loadLatest() {
    fetch('cek_sensor.php?t=' + Date.now())
        .then(r => r.json())
        .then(s => {
            // pH
            const ph = parseFloat(s.pH);
            document.getElementById('val-ph').textContent = ph.toFixed(2);
            const bp = document.getElementById('badge-ph');
            if (s.pH_status === 'rendah') {
                bp.className = 'badge badge-danger'; bp.textContent = 'pH Rendah (< 8.5)';
            } else if (s.pH_status === 'tinggi') {
                bp.className = 'badge badge-warning'; bp.textContent = 'pH Tinggi (> 10.5)';
            } else {
                bp.className = 'badge badge-normal'; bp.textContent = 'pH Normal (8.5–10.5)';
            }

            // Cahaya
            document.getElementById('val-lux').innerHTML = s.cahaya + ' <span class="sensor-unit">Lux</span>';
            const bl = document.getElementById('badge-lux');
            if (s.lux_status === 'error') {
                bl.className = 'badge badge-danger'; bl.textContent = 'Sensor Error';
            } else if (s.uv === 'ON') {
                bl.className = 'badge badge-warning'; bl.textContent = 'Lampu UV ON';
            } else {
                bl.className = 'badge badge-normal'; bl.textContent = 'Cahaya Cukup';
            }

            // Warna
            const warna   = s.warna   || 'tidak terdeteksi';
            const statusW = s.status_warna || '-';
            _warnaData = { warna, status_warna: statusW, bbox: s.bbox || null };
            document.getElementById('warna-box').style.background = faseToBg(warna);
            document.getElementById('badge-warna').textContent    = warna !== 'tidak terdeteksi' ? warna : 'Belum terdeteksi';
            document.getElementById('status-warna-text').textContent = statusW !== '-' ? statusW : '—';
            renderBboxOverlay();
            updateModalOverlay();

            // Last update
            const mnt = s.menit_lalu;
            document.getElementById('last-update').textContent =
                'KOLAM 1 · ' + (mnt === null ? '—' : mnt < 1 ? 'Baru saja' : mnt + ' menit lalu');

            // Toggles (FIX: pakai nama kolom yang benar)
            document.getElementById('togBasa').checked    = s.pompa_basa    === 'DOSING';
            document.getElementById('togNormal').checked  = s.pompa_normal  === 'DOSING';
            document.getElementById('togNutrisi').checked = s.pompa_nutrisi === 'ON';
            document.getElementById('togUV').checked      = s.uv            === 'ON';

            // Footer nutrisi
            if (s.nutrisi_terakhir) {
                const d = new Date(s.nutrisi_terakhir);
                document.getElementById('footer-nutrisi').textContent =
                    d.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
            }

            // Log otomatis
            if (_prevState.pompa_basa !== s.pompa_basa) {
                if (s.pompa_basa === 'DOSING') addLog('#2196f3', 'Pompa Basa AKTIF — pH rendah');
                else if (_prevState.pompa_basa === 'DOSING') addLog('#607d8b', 'Pompa Basa SELESAI');
            }
            if (_prevState.pompa_normal !== s.pompa_normal) {
                if (s.pompa_normal === 'DOSING') addLog('#ff9800', 'Pompa Air Netral AKTIF — pH tinggi');
                else if (_prevState.pompa_normal === 'DOSING') addLog('#607d8b', 'Pompa Netral SELESAI');
            }
            if (_prevState.pompa_nutrisi !== s.pompa_nutrisi) {
                if (s.pompa_nutrisi === 'ON') addLog('#4caf50', 'Pompa Nutrisi AKTIF');
                else if (_prevState.pompa_nutrisi === 'ON') addLog('#607d8b', 'Pompa Nutrisi SELESAI');
            }
            if (_prevState.uv !== s.uv) {
                if (s.uv === 'ON') addLog('#7b1fa2', 'Lampu UV AKTIF — cahaya kurang');
                else addLog('#607d8b', 'Lampu UV MATI — cahaya cukup');
            }
            _prevState = s;
        })
        .catch(() => {
            document.getElementById('last-update').textContent = 'KOLAM 1 · Offline';
        });
}

// Load charts & start polling
setToday();
loadLatest();
setInterval(loadLatest, 10000);
setInterval(() => loadCharts(document.getElementById('datePicker').value), 60000);

// ── BOUNDING BOX ──────────────────────────────────────────────────
let _bboxVisible = true;
document.getElementById('bbox-toggle').addEventListener('change', function() {
    _bboxVisible = this.checked; renderBboxOverlay();
});
function renderBboxOverlay() {
    const overlay = document.getElementById('bbox-overlay');
    overlay.innerHTML = '';
    const { bbox, warna } = _warnaData;
    if (!_bboxVisible || !bbox || !warna || warna === 'tidak terdeteksi') return;
    const color = faseToBboxColor(warna);
    const box   = document.createElement('div');
    box.className = 'bbox-box';
    box.style.cssText = `left:${(bbox.x*100).toFixed(4)}%;top:${(bbox.y*100).toFixed(4)}%;width:${(bbox.w*100).toFixed(4)}%;height:${(bbox.h*100).toFixed(4)}%;color:${color};`;
    ['tl','tr','bl','br'].forEach(pos => {
        const c = document.createElement('div');
        c.className = `bbox-corner ${pos}`; box.appendChild(c);
    });
    const label = document.createElement('div');
    label.className = 'bbox-label' + (bbox.y < 0.05 ? ' inside' : '');
    label.innerHTML = `<span>${warna}</span>`;
    label.style.background = color;
    box.appendChild(label);
    overlay.appendChild(box);
}

// ── MODAL STREAM ──────────────────────────────────────────────────
function openLiveModal() {
    document.getElementById('liveModal').classList.add('open');
    document.getElementById('stream-img').src = STREAM_URL;
    document.getElementById('stream-error').style.display = 'none';
    updateModalOverlay(); renderBboxOverlay();
}
function closeLiveModal() {
    document.getElementById('liveModal').classList.remove('open');
    document.getElementById('stream-img').src = '';
    document.getElementById('bbox-overlay').innerHTML = '';
}
function closeLiveModalOutside(e) { if (e.target === document.getElementById('liveModal')) closeLiveModal(); }
function showStreamError() { document.getElementById('stream-error').style.display = 'flex'; }
function hideStreamError() { document.getElementById('stream-error').style.display = 'none'; }
function updateModalOverlay() {
    const { warna, status_warna } = _warnaData;
    const badge = document.getElementById('modal-fase-badge');
    badge.textContent  = warna || 'Tidak terdeteksi';
    badge.className    = 'fase-badge ' + faseToClass(warna);
    document.getElementById('modal-status-warna').textContent = (status_warna && status_warna !== '-') ? status_warna : '—';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLiveModal(); });

// ══════════════════════════════════════════════════════════════════
// REKAPITULASI
// ══════════════════════════════════════════════════════════════════
const ROWS_PER_PAGE = 7;
let _rekapData  = [];
let _rekapPage  = 1;

function initRekap() {
    const today = new Date().toISOString().split('T')[0];
    const tiga  = new Date(Date.now() - 2 * 86400000).toISOString().split('T')[0];
    if (!document.getElementById('rekap-dari').value) {
        document.getElementById('rekap-dari').value   = tiga;
        document.getElementById('rekap-sampai').value = today;
    }
    loadRekap();
}

function loadRekap() {
    const dari   = document.getElementById('rekap-dari').value;
    const sampai = document.getElementById('rekap-sampai').value;
    if (!dari || !sampai) return;
    const tbody = document.getElementById('rekap-tbody');
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-soft);">
        <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </td></tr>`;

    fetch(`api_rekap.php?dari=${dari}&sampai=${sampai}&t=${Date.now()}`)
        .then(r => r.json())
        .then(res => {
            if (res.error) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--red);">
                    Error: ${res.error}</td></tr>`;
                return;
            }
            _rekapData = res.data || [];
            _rekapPage = 1;
            renderRekap();

            // Footer nutrisi terakhir
            if (res.nutrisi_terakhir) {
                const d = new Date(res.nutrisi_terakhir);
                document.getElementById('rekap-nutrisi-tgl').textContent =
                    d.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
                document.getElementById('rekap-nutrisi-sub').textContent =
                    d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) + ' WIB';
            } else {
                document.getElementById('rekap-nutrisi-tgl').textContent = '—';
                document.getElementById('rekap-nutrisi-sub').textContent  = 'Belum ada data';
            }
        })
        .catch(e => {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--red);">
                Gagal memuat data</td></tr>`;
        });
}

function renderRekap() {
    const total  = _rekapData.length;
    const start  = (_rekapPage - 1) * ROWS_PER_PAGE;
    const end    = Math.min(start + ROWS_PER_PAGE, total);
    const slice  = _rekapData.slice(start, end);
    const tbody  = document.getElementById('rekap-tbody');

    if (!total) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-soft);">
            Tidak ada data untuk rentang tanggal ini</td></tr>`;
        document.getElementById('rekap-info').textContent  = 'Tidak ada data';
        document.getElementById('rekap-pages').innerHTML  = '';
        return;
    }

    tbody.innerHTML = slice.map(r => {
        const phClass = r.pH < 8.5 ? 'ph-rendah' : r.pH > 10.5 ? 'ph-tinggi' : 'ph-normal';
        const faseDot = getFaseDot(r.warna);
        const tgl     = new Date(r.waktu);
        const tglStr  = tgl.toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})
                      + ',<br>' + tgl.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});
        return `<tr>
            <td class="td-waktu">${tglStr}</td>
            <td class="td-ph ${phClass}">${r.pH}</td>
            <td>${r.cahaya.toLocaleString('id-ID')} Lux</td>
            <td><div class="td-fase"><span class="fase-dot ${faseDot}"></span>${r.warna !== 'tidak terdeteksi' ? r.warna : '—'}</div></td>
            <td>${pillStatus(r.pompa_basa)}</td>
            <td>${pillStatus(r.pompa_normal)}</td>
            <td>${pillStatus(r.pompa_nutrisi, true)}</td>
            <td>${pillStatus(r.uv, true)}</td>
        </tr>`;
    }).join('');

    document.getElementById('rekap-info').textContent =
        `Menampilkan ${start+1} hingga ${end} dari ${total} entri`;

    // Pagination
    const totalPages = Math.ceil(total / ROWS_PER_PAGE);
    const pagesEl    = document.getElementById('rekap-pages');
    let btns = '';
    for (let i = 1; i <= totalPages; i++) {
        btns += `<button class="page-btn ${i === _rekapPage ? 'active' : ''}" onclick="goPage(${i})">${i}</button>`;
    }
    pagesEl.innerHTML = btns;
}

function goPage(n) { _rekapPage = n; renderRekap(); }

function getFaseDot(warna) {
    if (!warna) return 'fx';
    const w = warna.toLowerCase();
    if (w.includes('fase 1') || w.includes('pembibitan'))  return 'f1';
    if (w.includes('fase 2') || w.includes('pertumbuhan')) return 'f2';
    if (w.includes('fase 3') || w.includes('optimal'))     return 'f3';
    if (w.includes('fase 4') || w.includes('panen'))       return 'f4';
    return 'fx';
}

function pillStatus(val, isOnOff = false) {
    if (!val) return `<span class="pill-off">—</span>`;
    const v = val.toUpperCase();
    if (v === 'ON' || v === 'DOSING') {
        return `<span class="pill-on">${v}</span>`;
    }
    return `<span class="pill-off">${v}</span>`;
}

function downloadCSV() {
    const dari   = document.getElementById('rekap-dari').value;
    const sampai = document.getElementById('rekap-sampai').value;
    if (!dari || !sampai) return alert('Pilih rentang tanggal terlebih dahulu');
    window.location.href = `api_rekap.php?dari=${dari}&sampai=${sampai}&export=csv`;
}
</script>
</body>
</html>
