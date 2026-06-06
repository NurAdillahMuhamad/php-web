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
.sensor-row { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }

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
.warna-box { width:70%; height:38px; border-radius:6px; display:block; margin:0 auto; }


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
.select-sub {
    border:1.5px solid var(--border);
    border-radius:8px;
    padding:6px 10px;
    font-family:'Plus Jakarta Sans',sans-serif;
    font-size:.78rem;
    font-weight:600;
    color:var(--text);
    background:var(--bg);
    outline:none;
    cursor:pointer;
}
.select-sub:focus {
    border-color:var(--green-light);
}
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

/* ── GALLERY PAGE ─────────────────────────────────────────── */
.gallery-header-bar{background:var(--green-dark);color:#fff;padding:14px 22px;border-radius:var(--radius-lg);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.gallery-header-bar h2{font-size:1rem;font-weight:800;letter-spacing:-.2px}
.gallery-header-bar p{font-size:.68rem;opacity:.75;font-weight:500;margin-top:2px;font-style:italic}

.gallery-filter-bar{background:var(--white);border-radius:var(--radius-md);padding:14px 18px;box-shadow:var(--shadow);border:1px solid var(--border);display:flex;align-items:center;gap:12px;flex-wrap:wrap}

.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px}

.foto-card{background:var(--white);border-radius:var(--radius-md);border:1px solid var(--border);overflow:hidden;cursor:pointer;transition:transform .15s,box-shadow .15s;position:relative}
.foto-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.1)}
.foto-card-img{width:100%;aspect-ratio:4/3;object-fit:cover;display:block;background:#e8f0ec}
.foto-card-img-placeholder{width:100%;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;background:#f0f5f2;color:var(--text-soft);font-size:2rem}
.foto-card-body{padding:8px 10px}
.foto-card-fase{display:inline-block;padding:2px 8px;border-radius:20px;font-size:.62rem;font-weight:800;margin-bottom:4px}
.foto-card-time{font-size:.65rem;color:var(--text-soft);font-weight:600}
.foto-card-skor{font-size:.6rem;color:var(--text-soft);margin-top:2px}

.fase-pembibitan  {background:#FFF9E6;color:#8B6914}
.fase-pertumbuhan {background:#C8E6C9;color:#1B5E20}
.fase-optimal     {background:#A5D6A7;color:#1B5E20}
.fase-panen       {background:#BBDEFB;color:#0D47A1}
.fase-unknown     {background:#EEEEEE;color:#616161}

.gallery-empty{grid-column:1/-1;text-align:center;padding:48px 24px;color:var(--text-soft)}
.gallery-empty i{font-size:2.5rem;margin-bottom:12px;display:block}
.gallery-empty p{font-size:.85rem;font-weight:600}

.gallery-pagination{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;font-size:.72rem;font-weight:600;color:var(--text-soft)}

.lightbox-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:2000;align-items:center;justify-content:center;flex-direction:column;gap:16px}
.lightbox-overlay.open{display:flex}
.lightbox-img-wrap{position:relative;max-width:min(800px,92vw);max-height:70vh;border-radius:12px;overflow:hidden;background:#000}
.lightbox-img-wrap img{max-width:100%;max-height:70vh;object-fit:contain;display:block}
.lightbox-close{position:absolute;top:10px;right:10px;background:rgba(0,0,0,.6);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:.9rem;display:flex;align-items:center;justify-content:center}
.lightbox-info{display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:center}
.lightbox-fase-badge{padding:4px 14px;border-radius:20px;font-size:.75rem;font-weight:800}
.lightbox-meta{color:rgba(255,255,255,.6);font-size:.72rem;font-weight:600}
.lightbox-nav{position:absolute;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.5);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center}
.lightbox-nav.prev{left:-48px}
.lightbox-nav.next{right:-48px}
.lightbox-btn-drive{display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;padding:6px 14px;border-radius:8px;font-size:.72rem;font-weight:700;text-decoration:none;cursor:pointer}
.lightbox-btn-drive:hover{background:rgba(255,255,255,.25)}

.gallery-live-dot{display:inline-block;width:7px;height:7px;background:#2ecc71;border-radius:50%;animation:pulse 1.8s infinite;margin-right:4px}

@media(max-width:600px){.gallery-grid{grid-template-columns:repeat(2,1fr)}}

/* ── RESPONSIVE ── */
@media (max-width:950px) { .main-content{grid-template-columns:1fr;} .charts-row{grid-template-columns:1fr;} }
@media (max-width:600px) { .sensor-row{grid-template-columns:1fr;} .sidebar{width:0;min-width:0;} .sidebar.expanded{width:var(--sidebar-w);min-width:var(--sidebar-w);} }
.main-area::-webkit-scrollbar{width:5px;} .main-area::-webkit-scrollbar-track{background:transparent;} .main-area::-webkit-scrollbar-thumb{background:#c4d4cd;border-radius:5px;}
</style>
</head>
<body>
<div class="shell">

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar expanded" id="sidebar">
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
        <a class="sidebar-item" id="nav-gallery" onclick="showPage('gallery')">
        <i class="fas fa-images"></i>
        <span class="nav-lbl">Galeri Foto</span>
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

                <!-- INI DITAMBAH — masuk ke dalam sensor-row, sejajar pH & cahaya -->
                    <div class="sensor-card">
                        <div class="sensor-card-header">
                            <i class="fas fa-tint"></i><span>Warna Air Kolam</span>
                        </div>
                        <div id="warna-box" class="warna-box" style="background:#b0bec5;"></div>
                        <span class="badge badge-normal" id="badge-warna" style="margin-top:8px;display:inline-block;">Menunggu...</span>
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
                        <label class="toggle"><input type="checkbox" id="togBasa" disabled><span class="toggle-slider green"></span></label>
                    </div>
                    <div class="control-item">
                    <div class="control-left"><i class="fas fa-water"></i><span>Pompa Asam</span></div>
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
            <div class="filter-group">
                <label>Sub</label>
                <select class="select-sub" id="select-sub">
                    <option value="1">Control pH</option>
                    <option value="2">Control Cahaya</option>
                    <option value="3">Nutrisi</option>
                </select>
            </div>
            <button class="btn-filter" onclick="loadRekap()">Tampilkan</button>
            <div class="status-online-pill">
                <span class="pulse-dot" style="width:7px;height:7px;"></span> Online
            </div>
        </div>

        <div class="rekap-table-wrap">
            <div class="rekap-table-scroll">
                <table class="rekap-table">
                    <thead id="rekap-thead">
                        <tr>
                            <th>Tanggal</th>
                            <th>pH</th>
                            <th>Kondisi pH</th>
                            <th>Pompa Basa</th>
                            <th>Pompa Asam</th>
                            <th>Vol Basa</th>
                            <th>Vol Normal</th>
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

        <!-- <div class="rekap-footer">
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
        </div> -->

    </div><!-- /page-rekap -->

    <div class="page" id="page-gallery">

        <div class="rekap-header-bar">
            <div>
                <h2>GALERI FOTO KOLAM MIKROALGA <em>SPIRULINA SP.</em></h2>
                <p>Foto diambil setiap 20 detik · Filter berdasarkan rentang tanggal</p>
            </div>
        </div>

        <div class="rekap-filter-bar">
            <div class="filter-group">
                <i class="fas fa-calendar" style="color:var(--green-dark);"></i>
                <span style="font-size:.75rem;font-weight:700;color:var(--text);">Kolam 1</span>
            </div>
            <div class="filter-group">
                <label>Dari</label>
                <input type="date" id="gallery-dari">
            </div>
            <div class="filter-group">
                <label>Sampai</label>
                <input type="date" id="gallery-sampai">
            </div>
            <button class="btn-filter" onclick="loadGallery(1)">Tampilkan</button>
            <div class="status-online-pill">
                <span class="pulse-dot" style="width:7px;height:7px;"></span> Online
            </div>
        </div>

        <!-- STATS ROW -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <div class="control-box" style="text-align:center;">
                <div style="font-size:.68rem;font-weight:600;color:var(--text-soft);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Total Foto</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--green-dark);" id="gallery-total-foto">—</div>
                <div style="font-size:.65rem;color:var(--text-soft);margin-top:2px;">dalam rentang tanggal</div>
            </div>
            <div class="control-box" style="text-align:center;">
                <div style="font-size:.68rem;font-weight:600;color:var(--text-soft);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Fase Terbaru</div>
                <div style="font-size:.9rem;font-weight:800;color:var(--green-dark);" id="gallery-fase-terbaru">—</div>
                <div style="font-size:.65rem;color:var(--text-soft);margin-top:2px;">terdeteksi</div>
            </div>
            <div class="control-box" style="text-align:center;">
                <div style="font-size:.68rem;font-weight:600;color:var(--text-soft);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Halaman</div>
                <div style="font-size:1.4rem;font-weight:800;color:var(--green-dark);" id="gallery-page-info">—</div>
                <div style="font-size:.65rem;color:var(--text-soft);margin-top:2px;">dari total halaman</div>
            </div>
        </div>

        <!-- GRID FOTO -->
        <div class="rekap-table-wrap">
            <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:.71rem;font-weight:800;text-transform:uppercase;color:var(--green-dark);letter-spacing:.5px;">
                    📷 Foto Kolam
                </span>
                <span style="font-size:.68rem;color:var(--text-soft);font-weight:600;" id="gallery-last-refresh">—</span>
            </div>

            <div id="gallery-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;background:var(--white);padding:6px;">
                <div style="grid-column:1/-1;text-align:center;padding:48px 24px;color:var(--text-soft);background:var(--white);">
                    Pilih rentang tanggal lalu klik Tampilkan
                </div>
            </div>

            <div class="rekap-pagination">
                <span id="gallery-info">Menampilkan 0 foto</span>
                <div class="pagination-btns" id="gallery-pages"></div>
            </div>
        </div>

    </div><!-- /page-gallery -->

<!-- ── LIGHTBOX OVERLAY ─────────────────────────────────────── -->
<div class="modal-overlay" id="lightboxModal" onclick="closeLightboxOutside(event)">
    <div class="modal-box" style="max-width:min(800px,96vw);">
        <div class="modal-header">
            <div class="modal-header-left">
                <span class="modal-title" id="lightbox-title">—</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;margin-left:auto;">
                <a id="lightbox-gdrive-link" href="#" target="_blank"
                   style="color:rgba(255,255,255,.6);font-size:.7rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:4px;">
                    <i class="fas fa-external-link-alt"></i> Buka di Drive
                </a>
                <button class="modal-close" onclick="closeLightbox()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="modal-stream" style="background:#111;">
            <img id="lightbox-img" src="" alt="Foto Kolam"
                 style="width:100%;height:100%;object-fit:contain;display:block;">
            <div class="stream-error" id="lightbox-error">
                <i class="fas fa-image"></i>
                <span>Foto tidak tersedia</span>
                <small>Mungkin akses Google Drive terbatas</small>
            </div>
        </div>
        <div class="modal-overlay-info">
            <div class="fase-info">
                <span class="fase-badge none" id="lightbox-fase-badge">—</span>
                <span style="color:rgba(255,255,255,.4);font-size:.65rem;font-weight:600;" id="lightbox-skor">—</span>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
                <button onclick="lightboxNav(-1)"
                        style="background:rgba(255,255,255,.1);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:.9rem;">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <span style="color:rgba(255,255,255,.4);font-size:.65rem;font-weight:600;" id="lightbox-nav-info">—</span>
                <button onclick="lightboxNav(1)"
                        style="background:rgba(255,255,255,.1);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:.9rem;">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>

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
const GALLERY_PER_PAGE   = 12;
const GALLERY_REFRESH_MS = 60000;

let _galleryPage     = 1;
let _galleryData     = [];
let _galleryTotal    = 0;
let _galleryTotalPages = 0;
let _galleryRefreshTimer = null;
let _lightboxIndex   = 0;

// ── FASE HELPER ───────────────────────────────────────────────────
function getFaseClass(warna) {
    if (!warna) return 'none';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return 'fase1';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return 'fase2';
    if (w.includes('optimal')    || w.includes('fase 3')) return 'fase3';
    if (w.includes('panen')      || w.includes('fase 4')) return 'fase4';
    return 'none';
}

function getFaseBg(warna) {
    if (!warna) return '#EEEEEE';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return '#83ff5a';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return '#2dc922';
    if (w.includes('optimal')    || w.includes('fase 3')) return '#12700b';
    if (w.includes('panen')      || w.includes('fase 4')) return '#093c04';
    return '#EEEEEE';
}

function getFaseColor(warna) {
    if (!warna) return '#616161';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return '#1a4a00';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return '#1a4a00';
    if (w.includes('optimal')    || w.includes('fase 3')) return '#83ff5a';
    if (w.includes('panen')      || w.includes('fase 4')) return '#83ff5a';
    return '#616161';
}

function getFaseDotColor(warna) {
    if (!warna) return '#BDBDBD';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return '#83ff5a';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return '#2dc922';
    if (w.includes('optimal')    || w.includes('fase 3')) return '#12700b';
    if (w.includes('panen')      || w.includes('fase 4')) return '#093c04';
    return '#BDBDBD';
}

// ── INIT GALLERY ──────────────────────────────────────────────────
function initGallery() {
    const today = new Date().toISOString().split('T')[0];
    if (!document.getElementById('gallery-dari').value) {
        document.getElementById('gallery-dari').value  = today;
        document.getElementById('gallery-sampai').value = today;
    }
    loadGallery(1);

    // Auto-refresh setiap 60 detik saat halaman gallery aktif
    if (_galleryRefreshTimer) clearInterval(_galleryRefreshTimer);
    _galleryRefreshTimer = setInterval(() => {
        const galleryPage = document.getElementById('page-gallery');
        if (galleryPage && galleryPage.classList.contains('active')) {
            loadGallery(_galleryPage);
        }
    }, GALLERY_REFRESH_MS);
}

// ── LOAD GALLERY ─────────────────────────────────────────────────
function loadGallery(page) {
    _galleryPage = page || 1;
    const dari   = document.getElementById('gallery-dari').value;
    const sampai = document.getElementById('gallery-sampai').value;
    if (!dari || !sampai) return;

    const grid = document.getElementById('gallery-grid');
    grid.innerHTML = `
        <div style="grid-column:1/-1;text-align:center;padding:48px 24px;
                    color:var(--text-soft);background:var(--white);">
            <i class="fas fa-spinner fa-spin"></i> Memuat foto...
        </div>`;

    const url = `api_gallery.php?dari=${dari}&sampai=${sampai}&page=${_galleryPage}&per_page=4&kolam=1&t=${Date.now()}`;

    fetch(url)
        .then(r => r.json())
        .then(res => {
            if (res.error) {
                grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:48px 24px;
                    color:var(--red);background:var(--white);">${res.error}</div>`;
                return;
            }
            document.getElementById('gallery-total-foto').textContent =
                res.total > 0 ? res.total.toLocaleString('id-ID') : '0';
            document.getElementById('gallery-fase-terbaru').textContent =
                res.fase_terbaru || '—';
            document.getElementById('gallery-page-info').textContent =
                res.total_pages > 0 ? `${_galleryPage} / ${res.total_pages}` : '0 / 0';
            document.getElementById('gallery-info').textContent =
                res.total > 0
                    ? `Menampilkan ${((_galleryPage-1)*4)+1}–${Math.min(_galleryPage*4, res.total)} dari ${res.total} foto`
                    : 'Tidak ada foto';
            document.getElementById('gallery-last-refresh').textContent =
                'Diperbarui: ' + new Date().toLocaleTimeString('id-ID');

            if (!res.fotos || res.fotos.length === 0) {
                grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:48px 24px;
                    color:var(--text-soft);background:var(--white);">
                    Tidak ada foto untuk rentang tanggal ini</div>`;
                document.getElementById('gallery-pages').innerHTML = '';
                return;
            }

            grid.innerHTML = res.fotos.map(f => {
                const faseColor  = getFaseColor(f.warna);
                const waktuStr   = f.waktu
                    ? new Date(f.waktu).toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'})
                    : '—';
                const tanggalStr = f.waktu
                    ? new Date(f.waktu).toLocaleDateString('id-ID', {day:'numeric', month:'short'})
                    : '—';

                return `
                <div style="background:var(--white);cursor:pointer;overflow:hidden;
                            transition:transform .15s;position:relative;"
                     onclick="bukaFoto('${f.view_url || ''}')"
                     onmouseover="this.style.transform='scale(1.02)'"
                     onmouseout="this.style.transform='scale(1)'">
                    <div style="position:relative;aspect-ratio:4/3;background:#1a2b25;overflow:hidden;">
                        ${f.thumb_url
                            ? `<img src="${f.thumb_url}" alt="${f.file_name}"
                                   style="width:100%;height:100%;object-fit:cover;display:block;"
                                   onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                               <div style="display:none;width:100%;height:100%;align-items:center;
                                   justify-content:center;color:rgba(255,255,255,.3);font-size:.7rem;
                                   font-weight:600;flex-direction:column;gap:6px;position:absolute;inset:0;">
                                   <i class="fas fa-image" style="font-size:1.5rem;"></i>
                                   <span>Tidak tersedia</span>
                               </div>`
                            : `<div style="width:100%;height:100%;display:flex;align-items:center;
                                   justify-content:center;color:rgba(255,255,255,.3);">
                                   <i class="fas fa-image" style="font-size:2rem;"></i>
                               </div>`
                        }
                        <div style="position:absolute;bottom:0;left:0;right:0;
                                    background:linear-gradient(transparent,rgba(0,0,0,.7));
                                    padding:8px 8px 6px;">
                            <div style="color:#fff;font-size:.62rem;font-weight:700;">${tanggalStr} · ${waktuStr}</div>
                        </div>
                        <div style="position:absolute;top:6px;right:6px;background:${faseColor};
                                    color:#fff;font-size:.55rem;font-weight:800;padding:2px 7px;border-radius:10px;">
                            ${f.warna !== 'tidak terdeteksi' ? f.warna.replace('Fase ','F') : '—'}
                        </div>
                    </div>
                    <div style="padding:7px 8px;">
                        <div style="font-size:.65rem;font-weight:700;color:var(--text);
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            ${f.file_name || '—'}
                        </div>
                        <div style="font-size:.6rem;color:var(--text-soft);margin-top:2px;">
                            Skor: <strong>${f.skor}</strong>
                        </div>
                    </div>
                </div>`;
            }).join('');

            renderGalleryPages(res.total_pages);
        })
        .catch(e => {
            grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:48px 24px;
                color:var(--red);background:var(--white);">Gagal memuat data</div>`;
            console.error('Gallery error:', e);
        });
}

// ── RENDER GRID ───────────────────────────────────────────────────
function renderGalleryGrid() {
    const grid = document.getElementById('gallery-grid');

    if (!_galleryData.length) {
        grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:48px;color:var(--text-soft);background:var(--white);">
            Tidak ada foto untuk rentang tanggal ini
        </div>`;
        document.getElementById('gallery-info').textContent = 'Tidak ada foto';
        document.getElementById('gallery-pages').innerHTML  = '';
        return;
    }

    const start = (_galleryPage - 1) * GALLERY_PER_PAGE + 1;
    const end   = Math.min(_galleryPage * GALLERY_PER_PAGE, _galleryTotal);
    document.getElementById('gallery-info').textContent = `Menampilkan ${start} hingga ${end} dari ${_galleryTotal} foto`;

    grid.innerHTML = _galleryData.map((foto, idx) => {
        const warna     = foto.warna || 'tidak terdeteksi';
        const bg        = getFaseBg(warna);
        const color     = getFaseColor(warna);
        const dotColor  = getFaseDotColor(warna);
        const faseClass = getFaseClass(warna);
        const thumbUrl  = foto.thumb_url || '';
        const waktu     = foto.waktu ? foto.waktu.slice(11, 16) + ' WIB' : '—';
        const tgl       = foto.waktu ? (() => {
            const d = new Date(foto.waktu);
            return d.toLocaleDateString('id-ID', {day:'numeric', month:'short'});
        })() : '—';

        return `<div onclick="openLightbox(${idx})"
            style="background:var(--white);cursor:pointer;position:relative;overflow:hidden;aspect-ratio:4/3;display:flex;flex-direction:column;">
            <div style="flex:1;overflow:hidden;position:relative;">
                ${thumbUrl
                    ? `<img src="${thumbUrl}" alt="Foto kolam"
                           style="width:100%;height:100%;object-fit:cover;display:block;"
                           onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                       <div style="display:none;width:100%;height:100%;background:${bg};align-items:center;justify-content:center;flex-direction:column;gap:4px;">
                           <i class="fas fa-image" style="font-size:1.5rem;color:${color};opacity:.4;"></i>
                           <span style="font-size:.58rem;color:${color};opacity:.6;font-weight:600;">foto tidak tersedia</span>
                       </div>`
                    : `<div style="width:100%;height:100%;background:${bg};display:flex;align-items:center;justify-content:center;flex-direction:column;gap:4px;">
                           <i class="fas fa-image" style="font-size:1.5rem;color:${color};opacity:.4;"></i>
                           <span style="font-size:.58rem;color:${color};opacity:.6;font-weight:600;">belum ada foto</span>
                       </div>`
                }
                <div style="position:absolute;inset:0;background:rgba(10,92,71,.7);opacity:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;transition:opacity .2s;"
                     onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                    <i class="fas fa-search-plus" style="font-size:1.2rem;color:#fff;"></i>
                    <span style="background:rgba(255,255,255,.2);color:#fff;padding:2px 8px;border-radius:20px;font-size:.6rem;font-weight:700;">${warna !== 'tidak terdeteksi' ? warna : '—'}</span>
                </div>
            </div>
            <div style="padding:5px 8px;border-top:1px solid var(--border);">
                <div style="font-size:.6rem;color:var(--text-soft);font-weight:600;">${tgl} · ${waktu}</div>
                <div style="font-size:.62rem;font-weight:700;color:${color};display:flex;align-items:center;gap:4px;margin-top:2px;">
                    <span style="width:6px;height:6px;background:${dotColor};border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                    ${warna !== 'tidak terdeteksi' ? warna : '—'}
                </div>
            </div>
        </div>`;
    }).join('');
}

// ── RENDER PAGINATION ─────────────────────────────────────────────
function renderGalleryPagination() {
    const pagesEl    = document.getElementById('gallery-pages');
    const totalPages = _galleryTotalPages;
    if (!totalPages || totalPages <= 1) { pagesEl.innerHTML = ''; return; }

    let btns  = '';
    const cur = _galleryPage;

    // Selalu tampilkan halaman pertama
    btns += `<button class="page-btn ${cur === 1 ? 'active' : ''}" onclick="loadGallery(1)">1</button>`;

    if (cur > 3) btns += `<button class="page-btn" disabled>...</button>`;

    for (let i = Math.max(2, cur - 1); i <= Math.min(totalPages - 1, cur + 1); i++) {
        btns += `<button class="page-btn ${i === cur ? 'active' : ''}" onclick="loadGallery(${i})">${i}</button>`;
    }

    if (cur < totalPages - 2) btns += `<button class="page-btn" disabled>...</button>`;

    if (totalPages > 1) {
        btns += `<button class="page-btn ${cur === totalPages ? 'active' : ''}" onclick="loadGallery(${totalPages})">${totalPages}</button>`;
    }

    pagesEl.innerHTML = btns;
}

// ── LIGHTBOX ──────────────────────────────────────────────────────
function openLightbox(idx) {
    _lightboxIndex = idx;
    showLightboxPhoto(idx);
    document.getElementById('lightboxModal').classList.add('open');
}

function showLightboxPhoto(idx) {
    const foto = _galleryData[idx];
    if (!foto) return;

    const img   = document.getElementById('lightbox-img');
    const err   = document.getElementById('lightbox-error');
    const warna = foto.warna || 'tidak terdeteksi';
    const waktu = foto.waktu ? new Date(foto.waktu).toLocaleString('id-ID', {
        day:'numeric', month:'long', year:'numeric',
        hour:'2-digit', minute:'2-digit'
    }) + ' WIB' : '—';

    // Reset error state
    img.style.display = 'block';
    err.style.display = 'none';

    // Set gambar
    if (foto.thumb_url) {
        // Gunakan ukuran lebih besar untuk lightbox
        const fullUrl = foto.gdrive_file_id
            ? `https://drive.google.com/thumbnail?id=${foto.gdrive_file_id}&sz=w1200`
            : foto.thumb_url;
        img.src = fullUrl;
        img.onerror = () => { img.style.display = 'none'; err.style.display = 'flex'; };
        img.onload  = () => { img.style.display = 'block'; err.style.display = 'none'; };
    } else {
        img.style.display = 'none';
        err.style.display = 'flex';
    }

    // Update info
    document.getElementById('lightbox-title').textContent = waktu;
    document.getElementById('lightbox-skor').textContent  = `Skor: ${foto.skor}`;
    document.getElementById('lightbox-nav-info').textContent = `${idx + 1} / ${_galleryData.length}`;

    const badge = document.getElementById('lightbox-fase-badge');
    badge.textContent = warna !== 'tidak terdeteksi' ? warna : 'Tidak terdeteksi';
    badge.className   = 'fase-badge ' + getFaseClass(warna);

    const link = document.getElementById('lightbox-gdrive-link');
    if (foto.view_url) {
        link.href  = foto.view_url;
        link.style.display = 'flex';
    } else {
        link.style.display = 'none';
    }
}

function lightboxNav(dir) {
    const newIdx = _lightboxIndex + dir;
    if (newIdx < 0 || newIdx >= _galleryData.length) return;
    _lightboxIndex = newIdx;
    showLightboxPhoto(_lightboxIndex);
}

function closeLightbox() {
    document.getElementById('lightboxModal').classList.remove('open');
    document.getElementById('lightbox-img').src = '';
}

function closeLightboxOutside(e) {
    if (e.target === document.getElementById('lightboxModal')) closeLightbox();
}

// Keyboard navigation lightbox
document.addEventListener('keydown', e => {
    const modal = document.getElementById('lightboxModal');
    if (!modal.classList.contains('open')) return;
    if (e.key === 'ArrowLeft')  lightboxNav(-1);
    if (e.key === 'ArrowRight') lightboxNav(1);
});

// ── PAGE NAVIGATION ───────────────────────────────────────────────
function showPage(name) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
    document.getElementById('page-' + name).classList.add('active');
    document.getElementById('nav-' + name).classList.add('active');
    if (name === 'rekap') initRekap();
    if (name === 'gallery') initGallery();
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
    if (w.includes('pembibitan') || w.includes('fase 1')) return '#83ff5a';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return '#2dc922';
    if (w.includes('optimal')    || w.includes('fase 3')) return '#12700b';
    if (w.includes('panen')      || w.includes('fase 4')) return '#093c04';
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
    if (w.includes('pembibitan') || w.includes('fase 1')) return '#83ff5a';
    if (w.includes('pertumbuhan')|| w.includes('fase 2')) return '#2dc922';
    if (w.includes('optimal')    || w.includes('fase 3')) return '#12700b';
    if (w.includes('panen')      || w.includes('fase 4')) return '#093c04';
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
const ROWS_PER_PAGE = 6;
let _rekapData  = [];
let _rekapTotal = 0;
let _rekapPage  = 1;
let _currentSub = 1;
    
function updateTheadRekap() {
    const heads = {
        1: ['Tanggal','pH','Kondisi pH','Pompa Basa','Pompa Normal','Vol Basa','Vol Normal'],
        2: ['Tanggal','Cahaya (Lux)','UV'],
        3: ['Tanggal','pH','Warna Air','Pompa Nutrisi'],
    };
    document.getElementById('rekap-thead').innerHTML =
        '<tr>' + heads[_currentSub].map(h => `<th>${h}</th>`).join('') + '</tr>';
}

function setSub(n) {
    _currentSub = n;
    updateTheadRekap();
}

function initRekap() {
    updateTheadRekap();
    const today = new Date().toISOString().split('T')[0];
    const tiga  = new Date(Date.now() - 2 * 86400000).toISOString().split('T')[0];
    // Force set nilai tanggal setiap kali halaman dibuka
    document.getElementById('rekap-dari').value   = tiga;
    document.getElementById('rekap-sampai').value = today;
    loadRekap();
}

function loadRekap() {
    _currentSub = parseInt(document.getElementById('select-sub').value);
    updateTheadRekap();
    const dari   = document.getElementById('rekap-dari').value;
    const sampai = document.getElementById('rekap-sampai').value;
    if (!dari || !sampai) return;
    const tbody = document.getElementById('rekap-tbody');
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-soft);">
        <i class="fas fa-spinner fa-spin"></i> Memuat data...
    </td></tr>`;

    fetch(`api_rekap.php?dari=${dari}&sampai=${sampai}&sub=${_currentSub}&t=${Date.now()}`)
        .then(r => r.json())
        .then(res => {
            if (res.error) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--red);">
                    Error: ${res.error}</td></tr>`;
                return;
            }
            _rekapData  = res.data || [];
            _rekapTotal = res.total || 0;
            _rekapPage  = 1;
            renderRekap();

            // Footer nutrisi terakhir
            // if (res.nutrisi_terakhir) {
            //     const d = new Date(res.nutrisi_terakhir);
            //     document.getElementById('rekap-nutrisi-tgl').textContent =
            //         d.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
            //     document.getElementById('rekap-nutrisi-sub').textContent =
            //         d.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'}) + ' WIB';
            // } else {
            //     document.getElementById('rekap-nutrisi-tgl').textContent = '—';
            //     document.getElementById('rekap-nutrisi-sub').textContent  = 'Belum ada data';
            // }
        })
        .catch(e => {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:24px;color:var(--red);">
                Gagal memuat data</td></tr>`;
        });
}

function renderRekap() {
    const total = _rekapTotal;
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
    const tgl = new Date(r.waktu);
    const tglStr = tgl.toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'})
                 + ',<br>' + tgl.toLocaleTimeString('id-ID', {hour:'2-digit', minute:'2-digit'});

    if (_currentSub === 1) {
        const phClass = r.pH < 8.5 ? 'ph-rendah' : r.pH > 10.5 ? 'ph-tinggi' : 'ph-normal';
        const kondisiClass = r.kondisi_ph === 'Rendah' ? 'badge-danger' : r.kondisi_ph === 'Tinggi' ? 'badge-warning' : 'badge-normal';
        return `<tr>
            <td class="td-waktu">${tglStr}</td>
            <td class="td-ph ${phClass}">${r.pH}</td>
            <td><span class="badge ${kondisiClass}">${r.kondisi_ph}</span></td>
            <td>${pillStatus(r.pompa_basa)}</td>
            <td>${pillStatus(r.pompa_normal)}</td>
            <td>${r.vol_basa > 0 ? r.vol_basa + ' mL' : '—'}</td>
            <td>${r.vol_normal > 0 ? r.vol_normal + ' mL' : '—'}</td>
        </tr>`;
    } else if (_currentSub === 2) {
        return `<tr>
            <td class="td-waktu">${tglStr}</td>
            <td>${r.cahaya.toLocaleString('id-ID')} Lux</td>
            <td>${pillStatus(r.uv, true)}</td>
        </tr>`;
    } else {
        const faseDot = getFaseDot(r.warna);
        return `<tr>
            <td class="td-waktu">${tglStr}</td>
            <td class="td-ph">${r.pH}</td>
            <td><div class="td-fase"><span class="fase-dot ${faseDot}"></span>${r.warna !== 'tidak terdeteksi' ? r.warna : '—'}</div></td>
            <td>${pillStatus(r.pompa_nutrisi, true)}</td>
        </tr>`;
    }
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
    window.location.href = `api_rekap.php?dari=${dari}&sampai=${sampai}&sub=${_currentSub}&export=csv`;
}
// ══════════════════════════════════════════════════════════════════
// GALLERY
// ══════════════════════════════════════════════════════════════════

function initGallery() {
    const today = new Date().toISOString().split('T')[0];
    if (!document.getElementById('gallery-dari').value) {
        document.getElementById('gallery-dari').value   = today;
        document.getElementById('gallery-sampai').value = today;
    }
    loadGallery(1);
}

function loadGallery(page) {
    _galleryPage    = page || 1;
    const dari      = document.getElementById('gallery-dari').value;
    const sampai    = document.getElementById('gallery-sampai').value;
    if (!dari || !sampai) return;

    const grid = document.getElementById('gallery-grid');
    grid.innerHTML = `
        <div style="grid-column:1/-1;text-align:center;padding:48px 24px;
                    color:var(--text-soft);background:var(--white);">
            <i class="fas fa-spinner fa-spin"></i> Memuat foto...
        </div>`;

    const url = `api_gallery.php?dari=${dari}&sampai=${sampai}&page=${_galleryPage}&per_page=4&kolam=1&t=${Date.now()}`;

    fetch(url)
        .then(r => r.json())
        .then(res => {
            if (res.error) {
                grid.innerHTML = `
                    <div style="grid-column:1/-1;text-align:center;padding:48px 24px;
                                color:var(--red);background:var(--white);">
                        ${res.error}
                    </div>`;
                return;
            }

            // ── Stats ─────────────────────────────────────────────
            document.getElementById('gallery-total-foto').textContent =
                res.total > 0 ? res.total.toLocaleString('id-ID') : '0';
            document.getElementById('gallery-fase-terbaru').textContent =
                res.fase_terbaru || 'tidak terdeteksi';
            document.getElementById('gallery-page-info').textContent =
                res.total_pages > 0
                    ? `${_galleryPage} / ${res.total_pages}`
                    : '0 / 0';
            document.getElementById('gallery-info').textContent =
                res.total > 0
                    ? `Menampilkan ${((_galleryPage-1)*4)+1}–${Math.min(_galleryPage*4, res.total)} dari ${res.total} foto`
                    : 'Tidak ada foto';
            document.getElementById('gallery-last-refresh').textContent =
                'Diperbarui: ' + new Date().toLocaleTimeString('id-ID');

            // ── Grid foto ─────────────────────────────────────────
            if (!res.fotos || res.fotos.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column:1/-1;text-align:center;padding:48px 24px;
                                color:var(--text-soft);background:var(--white);">
                        Tidak ada foto untuk rentang tanggal ini
                    </div>`;
                document.getElementById('gallery-pages').innerHTML = '';
                return;
            }

            grid.innerHTML = res.fotos.map(f => {
                const faseClass  = getFaseClass(f.warna);
                const faseColor  = getFaseColor(f.warna);
                const waktuStr   = f.waktu
                    ? new Date(f.waktu).toLocaleTimeString('id-ID',
                        {hour:'2-digit', minute:'2-digit', second:'2-digit'})
                    : '—';
                const tanggalStr = f.waktu
                    ? new Date(f.waktu).toLocaleDateString('id-ID',
                        {day:'numeric', month:'short'})
                    : '—';
                const imgSrc = f.thumb_url || '';

                return `
                <div style="background:var(--white);cursor:pointer;overflow:hidden;
                            transition:transform .15s;position:relative;"
                     onclick="bukaFoto('${f.view_url || ''}', '${f.file_name || ''}', '${f.warna || ''}', '${f.status_warna || ''}', '${f.waktu || ''}', ${f.skor})"
                     onmouseover="this.style.transform='scale(1.02)'"
                     onmouseout="this.style.transform='scale(1)'">
                    <div style="position:relative;aspect-ratio:4/3;background:#1a2b25;overflow:hidden;">
                        <img src="${imgSrc}" alt="${f.file_name}"
                             style="width:100%;height:100%;object-fit:cover;display:block;"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                        <div style="display:none;width:100%;height:100%;align-items:center;
                                    justify-content:center;color:rgba(255,255,255,.3);
                                    font-size:.7rem;font-weight:600;flex-direction:column;gap:6px;
                                    position:absolute;inset:0;">
                            <i class="fas fa-image" style="font-size:1.5rem;"></i>
                            <span>Tidak tersedia</span>
                        </div>
                        <div style="position:absolute;bottom:0;left:0;right:0;
                                    background:linear-gradient(transparent,rgba(0,0,0,.7));
                                    padding:8px 8px 6px;">
                            <div style="color:#fff;font-size:.62rem;font-weight:700;">${tanggalStr} · ${waktuStr}</div>
                        </div>
                        <div style="position:absolute;top:6px;right:6px;
                                    background:${faseColor};color:#fff;
                                    font-size:.55rem;font-weight:800;
                                    padding:2px 7px;border-radius:10px;">
                            ${f.warna !== 'tidak terdeteksi' ? f.warna.replace('Fase ','F') : '—'}
                        </div>
                    </div>
                    <div style="padding:7px 8px;">
                        <div style="font-size:.65rem;font-weight:700;color:var(--text);
                                    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            ${f.file_name || '—'}
                        </div>
                        <div style="font-size:.6rem;color:var(--text-soft);margin-top:2px;">
                            Skor: <strong>${f.skor}</strong>
                        </div>
                    </div>
                </div>`;
            }).join('');

            // ── Pagination ────────────────────────────────────────
            renderGalleryPages(res.total_pages);
        })
        .catch(e => {
            grid.innerHTML = `
                <div style="grid-column:1/-1;text-align:center;padding:48px 24px;
                            color:var(--red);background:var(--white);">
                    Gagal memuat data
                </div>`;
            console.error('Gallery error:', e);
        });
}

function renderGalleryPages(totalPages) {
    const el = document.getElementById('gallery-pages');
    if (totalPages <= 1) { el.innerHTML = ''; return; }

    // Tampilkan max 7 tombol (prev, 1..5, next)
    let btns = '';
    const curr = _galleryPage;

    if (curr > 1) btns += `<button class="page-btn" onclick="loadGallery(${curr-1})">‹</button>`;

    let start = Math.max(1, curr - 2);
    let end   = Math.min(totalPages, start + 4);
    if (end - start < 4) start = Math.max(1, end - 4);

    if (start > 1) btns += `<button class="page-btn" onclick="loadGallery(1)">1</button>`;
    if (start > 2) btns += `<span style="padding:4px 6px;color:var(--text-soft);">…</span>`;

    for (let i = start; i <= end; i++) {
        btns += `<button class="page-btn ${i === curr ? 'active' : ''}"
                         onclick="loadGallery(${i})">${i}</button>`;
    }

    if (end < totalPages - 1) btns += `<span style="padding:4px 6px;color:var(--text-soft);">…</span>`;
    if (end < totalPages)     btns += `<button class="page-btn" onclick="loadGallery(${totalPages})">${totalPages}</button>`;
    if (curr < totalPages)    btns += `<button class="page-btn" onclick="loadGallery(${curr+1})">›</button>`;

    el.innerHTML = btns;
}

function bukaFoto(viewUrl, fileName, warna, statusWarna, waktu, skor) {
    if (!viewUrl) return;
    window.open(viewUrl, '_blank');
}

function getFaseClass(warna) {
    if (!warna) return 'none';
    const w = warna.toLowerCase();
    if (w.includes('fase 1') || w.includes('pembibitan'))  return 'fase1';
    if (w.includes('fase 2') || w.includes('pertumbuhan')) return 'fase2';
    if (w.includes('fase 3') || w.includes('optimal'))     return 'fase3';
    if (w.includes('fase 4') || w.includes('panen'))       return 'fase4';
    return 'none';
}

function getFaseColor(warna) {
    if (!warna) return '#78909c';
    const w = warna.toLowerCase();
    if (w.includes('fase 1') || w.includes('pembibitan'))  return '#f9a825';
    if (w.includes('fase 2') || w.includes('pertumbuhan')) return '#388e3c';
    if (w.includes('fase 3') || w.includes('optimal'))     return '#0a5c47';
    if (w.includes('fase 4') || w.includes('panen'))       return '#1a237e';
    return '#78909c';
}

</script>
</body>
</html>
