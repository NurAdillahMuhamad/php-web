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
      --green-dark: #0a5c47;
      --green-mid: #0d6b53;
      --green-light: #2dc4a2;
      --bg: #f2f6f4;
      --white: #ffffff;
      --text: #1a2b25;
      --text-soft: #6b8078;
      --border: #dce8e3;
      --shadow: 0 4px 14px rgba(0,0,0,0.04);
      --radius-lg: 20px;
      --radius-md: 14px;
      --radius-sm: 10px;
      --blue: #2b7bd6;
      --orange: #e67e22;
      --red: #d94141;
      --green: #1f9e5c;
      --sidebar-mini: 54px;
      --sidebar-w: 200px;
    }

    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      font-size: 14px;
      line-height: 1.5;
      overflow: hidden;
    }

    .shell { display:flex; height:100vh; overflow:hidden; }

    .sidebar {
      width: 220px; min-width: 100px; height: 100vh;
      background: var(--green-dark);
      display: flex; flex-direction: column;
      transition: width 0.25s ease, min-width 0.25s ease;
      overflow: hidden; flex-shrink: 0; z-index: 10;
    }
    .sidebar.expanded { width: var(--sidebar-w); min-width: var(--sidebar-w); }

    .sb-logo {
      height: 56px; display: flex; align-items: center; gap: 10px;
      padding: 0 14px; border-bottom: 1px solid rgba(255,255,255,0.1);
      flex-shrink: 0; overflow: hidden; white-space: nowrap; border-radius: 50%;
    }
    .sb-icon-wrap {
      width:28px; height:28px; border-radius:7px;
      background:rgba(255,255,255,0.15);
      display:flex; align-items:center; justify-content:center;
      font-size:1rem; flex-shrink:0;
    }
    .sb-texts, .nav-lbl, .sb-uname {
      flex:1; overflow:hidden; opacity:1 !important;
      pointer-events:auto !important; transition:opacity 0.2s;
    }
    .sb-title { color:#fff; font-weight:800; font-size:0.85rem; line-height:1.2; }
    .sb-sub { color:rgba(255,255,255,0.5); font-size:0.6rem; font-weight:500; }

    .sb-nav { flex:1; padding:10px 0; overflow:hidden; }
    .sidebar-item {
      display:flex; align-items:center; gap:12px; height:40px; padding:0 16px;
      color:rgba(255,255,255,0.65); font-size:0.78rem; font-weight:600;
      cursor:pointer; border-left:3px solid transparent;
      transition:all 0.15s; text-decoration:none; white-space:nowrap; overflow:hidden;
    }
    .sidebar-item:hover { color:#fff; background:rgba(255,255,255,0.07); }
    .sidebar-item.active { color:#fff; background:rgba(255,255,255,0.1); border-left-color:var(--green-light); }
    .sidebar-item i { font-size:0.9rem; width:18px; text-align:center; flex-shrink:0; }
    .nav-lbl { opacity:0; transition:opacity 0.15s; }
    .sidebar.expanded .nav-lbl { opacity:1; }

    .sb-foot {
      padding:10px 14px; border-top:1px solid rgba(255,255,255,0.1);
      flex-shrink:0; overflow:hidden; white-space:nowrap;
      display:flex; align-items:center; gap:10px;
    }
    .sb-av {
      width:28px; height:28px; border-radius:50%;
      background:var(--green-light); color:#fff; font-weight:800; font-size:0.72rem;
      display:flex; align-items:center; justify-content:center; flex-shrink:0;
    }
    .sb-uname { color:rgba(255,255,255,0.75); font-size:0.75rem; font-weight:600; opacity:0; transition:opacity 0.15s; }
    .sidebar.expanded .sb-uname { opacity:1; }

    .main-area { flex:1; min-width:0; overflow-y:auto; display:flex; flex-direction:column; }
    .dashboard {
      width:100%; min-height:100vh; max-width:1050px; margin:0 auto;
      padding:14px; display:flex; flex-direction:column; gap:12px;
    }

    .top-header {
      background:var(--green-dark); color:#fff; padding:14px 22px;
      border-radius:var(--radius-lg);
      display:flex; justify-content:space-between; align-items:center;
      flex-wrap:wrap; gap:10px;
    }
    .welcome-text h2 { font-size:1.2rem; font-weight:800; letter-spacing:-0.2px; margin-bottom:2px; }
    .welcome-text p { font-size:0.68rem; font-weight:600; opacity:0.75; letter-spacing:0.5px; text-transform:uppercase; }
    .btn-live-header {
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,0.15);
      border: 1.5px solid rgba(255,255,255,0.3);
      color: #fff; padding: 8px 16px; border-radius: 10px;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.78rem; font-weight: 800; cursor: pointer;
      transition: background 0.15s;
      letter-spacing: 0.3px;
    }
    .btn-live-header:hover { background: rgba(220,30,30,0.7); border-color: transparent; }
    .btn-live-header .live-dot-h {
      width: 8px; height: 8px; background: #ff4444;
      border-radius: 50%; animation: pulse 1.2s infinite;
    }

    .status-strip {
      background:var(--green-dark); color:#fff; padding:10px 22px; border-radius:10px;
      display:flex; justify-content:space-between; align-items:center;
      font-size:0.78rem; font-weight:600; flex-wrap:wrap; gap:8px;
    }
    .online-indicator { display:flex; align-items:center; gap:8px; }
    .pulse-dot {
      width:9px; height:9px; background:#2ecc71; border-radius:50%;
      animation:pulse 1.8s infinite;
    }
    @keyframes pulse {
      0%,100% { box-shadow:0 0 0 0 rgba(46,204,113,0.5); }
      50%      { box-shadow:0 0 0 6px rgba(46,204,113,0); }
    }

    .main-content { display:grid; grid-template-columns:1fr 280px; gap:12px; flex:1; align-items:stretch; }
    .left-col { display:flex; flex-direction:column; gap:12px; flex:1; }

    .sensor-row { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    .sensor-card {
      background:var(--white); border-radius:var(--radius-lg); padding:14px 16px;
      box-shadow:var(--shadow); border:1px solid var(--border);
      text-align:center; height:140px; margin-bottom:0;
    }
    .sensor-card-header {
      display:flex; align-items:center; gap:8px; margin-bottom:8px;
      justify-content:center; margin-top:13px;
    }
    .sensor-card-header i { font-size:1rem; color:var(--text-soft); }
    .sensor-card-header span { font-size:0.65rem; font-weight:700; text-transform:uppercase; color:var(--text-soft); letter-spacing:0.5px; }
    .sensor-value { font-size:2.2rem; font-weight:800; line-height:1.1; color:var(--text); margin-bottom:6px; text-align:center; }
    .sensor-unit { font-size:0.65rem; font-weight:600; color:var(--text-soft); margin-left:3px; }

    .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.65rem; font-weight:700; letter-spacing:0.3px; }
    .badge-danger  { background:#fde8e8; color:#b91c1c; }
    .badge-warning { background:#fff3e0; color:#b85e1a; }
    .badge-normal  { background:#e6f5ee; color:#1a7a4c; }

    /* Warna card - area box + tombol live */
    .warna-card-body {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      margin-top: 2px;
    }
    .warna-box-wrap {
      position: relative;
      width: 90%;
    }
    .warna-box {
      width: 100%;
      height: 28px;
      border-radius: 6px;
      display: block;
    }
    .btn-live {
      position: absolute;
      right: 4px;
      top: 50%;
      transform: translateY(-50%);
      background: rgba(0,0,0,0.55);
      color: #fff;
      border: none;
      border-radius: 5px;
      font-size: 0.58rem;
      font-weight: 700;
      padding: 2px 7px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      transition: background 0.15s;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .btn-live:hover { background: rgba(220,20,20,0.85); }
    .btn-live .live-dot {
      width: 6px; height: 6px; background: #ff4444;
      border-radius: 50%; animation: pulse 1.2s infinite;
    }

    /* Skor warna kecil */
    .warna-skor {
      font-size: 0.58rem;
      color: var(--text-soft);
      font-weight: 600;
    }

    /* ============================================================
       MODAL LIVE STREAM
       ============================================================ */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.72);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    .modal-box {
      background: #0f1e18;
      border-radius: 18px;
      width: min(700px, 96vw);
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
      display: flex;
      flex-direction: column;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 18px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .modal-header-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .modal-live-badge {
      background: #e53935;
      color: #fff;
      font-size: 0.62rem;
      font-weight: 800;
      padding: 3px 8px;
      border-radius: 5px;
      letter-spacing: 0.5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .modal-live-badge .dot {
      width: 6px; height: 6px;
      background: #fff; border-radius: 50%;
      animation: pulse 1s infinite;
    }
    .modal-title {
      color: #fff;
      font-size: 0.85rem;
      font-weight: 700;
    }
    .modal-close {
      background: rgba(255,255,255,0.1);
      border: none;
      color: #fff;
      width: 28px; height: 28px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 0.8rem;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.15s;
    }
    .modal-close:hover { background: rgba(255,255,255,0.2); }

    .modal-stream {
      position: relative;
      width: 100%;
      background: #000;
      aspect-ratio: 4/3;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .modal-stream img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .stream-error {
      display: none;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      color: rgba(255,255,255,0.5);
      font-size: 0.8rem;
      font-weight: 600;
    }
    .stream-error i { font-size: 2rem; opacity: 0.4; }

    .modal-overlay-info {
      padding: 12px 18px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
      border-top: 1px solid rgba(255,255,255,0.08);
    }

    .fase-info {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }
    .fase-badge {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.68rem;
      font-weight: 700;
      background: var(--green-light);
      color: #fff;
    }
    .fase-badge.fase1 { background: #f9c74f; color: #5a3e00; }
    .fase-badge.fase2 { background: #4caf50; color: #fff; }
    .fase-badge.fase3 { background: #0a5c47; color: #fff; }
    .fase-badge.fase4 { background: #1a237e; color: #fff; }
    .fase-badge.none  { background: #78909c; color: #fff; }

    .skor-bar-wrap {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .skor-label {
      color: rgba(255,255,255,0.5);
      font-size: 0.65rem;
      font-weight: 600;
      white-space: nowrap;
    }
    .skor-bar {
      width: 100px;
      height: 6px;
      background: rgba(255,255,255,0.1);
      border-radius: 3px;
      overflow: hidden;
    }
    .skor-fill {
      height: 100%;
      background: var(--green-light);
      border-radius: 3px;
      transition: width 0.4s ease;
    }
    .skor-val {
      color: #fff;
      font-size: 0.7rem;
      font-weight: 700;
      min-width: 32px;
    }
    .menit-info {
      color: rgba(255,255,255,0.35);
      font-size: 0.62rem;
      font-weight: 600;
    }

    /* Chart */
    .chart-panel {
      background:var(--white); border-radius:var(--radius-lg);
      padding:16px 18px; box-shadow:var(--shadow); border:1px solid var(--border);
      flex:1; display:flex; flex-direction:column; margin-top:0;
    }
    .chart-header {
      display:flex; justify-content:space-between; align-items:center;
      margin-bottom:12px; flex-wrap:wrap; gap:8px;
    }
    .chart-header h4 { font-size:0.8rem; font-weight:800; color:var(--green-dark); text-transform:uppercase; letter-spacing:0.5px; }
    .date-selector { display:flex; align-items:center; gap:6px; background:#f0f5f2; padding:3px 10px; border-radius:8px; }
    .date-selector input { border:none; background:transparent; font-family:'Plus Jakarta Sans',sans-serif; font-weight:600; font-size:0.7rem; outline:none; color:var(--text); }
    .btn-today { background:var(--green-dark); color:#fff; border:none; padding:4px 10px; border-radius:6px; font-weight:700; font-size:0.65rem; cursor:pointer; font-family:'Plus Jakarta Sans',sans-serif; }
    .charts-row { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; flex:1; }
    .mini-chart { background:#f9fbfa; border-radius:var(--radius-md); padding:10px; border:1px solid var(--border); }
    .mini-chart h5 { font-size:0.6rem; font-weight:700; text-transform:uppercase; color:var(--text-soft); margin-bottom:5px; letter-spacing:0.4px; }
    .mini-chart canvas { width:100% !important; height:100px !important; }
    .chart-stats { display:flex; gap:6px; margin-top:4px; font-size:0.6rem; font-weight:600; color:var(--text-soft); }

    .right-col { display:flex; flex-direction:column; gap:12px; flex:1; }
    .control-box { background:var(--white); border-radius:var(--radius-lg); padding:14px 16px; box-shadow:var(--shadow); border:1px solid var(--border); }
    .section-title { font-size:0.71rem; font-weight:800; text-transform:uppercase; color:var(--green-dark); letter-spacing:0.5px; margin-bottom:10px; }
    .section-title span { font-size:1rem; }
    .control-item { display:flex; justify-content:space-between; align-items:center; padding:7px 0; border-bottom:1px solid #eef3f0; }
    .control-item:last-child { border-bottom:none; }
    .control-left { display:flex; align-items:center; gap:8px; }
    .control-left i { font-size:0.85rem; color:#1e6f5c; width:18px; }
    .control-left span { font-weight:700; font-size:0.75rem; }

    .toggle { position:relative; display:inline-block; width:38px; height:20px; }
    .toggle input { opacity:0; width:0; height:0; }
    .toggle-slider { position:absolute; inset:0; background:#c5d0cb; border-radius:20px; cursor:pointer; transition:0.25s; }
    .toggle-slider::before { content:''; position:absolute; width:14px; height:14px; left:3px; top:3px; background:#fff; border-radius:50%; transition:0.25s; box-shadow:0 1px 3px rgba(0,0,0,0.15); }
    .toggle input:checked + .toggle-slider { background:var(--red); }
    .toggle input:checked + .toggle-slider.green { background:var(--green); }
    .toggle input:checked + .toggle-slider::before { transform:translateX(18px); }
    .toggle input:disabled + .toggle-slider { opacity:1; cursor:default; }

    .log-box { background:var(--white); border-radius:var(--radius-lg); padding:14px 16px; box-shadow:var(--shadow); border:1px solid var(--border); flex:1; display:flex; flex-direction:column; }
    .log-list { display:flex; flex-direction:column; }
    .log-item { display:flex; align-items:flex-start; gap:8px; padding:6px 0; border-bottom:1px solid #f0f4f2; }
    .log-item:last-child { border-bottom:none; }
    .log-dot { width:7px; height:7px; border-radius:50%; margin-top:4px; flex-shrink:0; }
    .log-time { font-size:0.65rem; font-weight:600; color:var(--text-soft); min-width:35px; }
    .log-msg { font-size:0.7rem; font-weight:600; color:var(--text); }

    .footer-bar {
      background:var(--green-dark); color:#fff; padding:10px 22px; border-radius:10px;
      display:flex; justify-content:space-between; align-items:center;
      flex-wrap:wrap; gap:8px; font-size:0.68rem; font-weight:600; margin-top:auto;
    }
    .footer-bar strong { font-weight:800; }

    @media (max-width:950px) {
      .main-content { grid-template-columns:1fr; }
      .sensor-row { grid-template-columns:repeat(2,1fr); }
      .charts-row { grid-template-columns:1fr; }
    }
    @media (max-width:600px) {
      .sensor-row { grid-template-columns:1fr; }
      .sidebar { width:0; min-width:0; }
      .sidebar.expanded { width:var(--sidebar-w); min-width:var(--sidebar-w); }
    }
    .main-area::-webkit-scrollbar { width:5px; }
    .main-area::-webkit-scrollbar-track { background:transparent; }
    .main-area::-webkit-scrollbar-thumb { background:#c4d4cd; border-radius:5px; }
  </style>
</head>
<body>

<div class="shell">

  <!-- SIDEBAR -->
  <aside class="sidebar" id="sidebar">
    <div class="sb-logo">
      <div class="sb-icon-wrap">🌿</div>
      <div class="sb-texts">
        <div class="sb-title">MikroAlga</div>
        <div class="sb-sub">Monitoring &amp; Kontrol</div>
      </div>
    </div>
    <nav class="sb-nav">
      <a class="sidebar-item active" href="#">
        <i class="fas fa-th-large"></i>
        <span class="nav-lbl">Dashboard</span>
      </a>
      <a class="sidebar-item" href="#">
        <i class="fas fa-cog"></i>
        <span class="nav-lbl">Settings</span>
      </a>
      <a class="sidebar-item" href="#">
        <i class="fas fa-sign-out-alt"></i>
        <span class="nav-lbl">Logout</span>
      </a>
    </nav>
    <div class="sb-foot">
      <div class="sb-av">SL</div>
      <span class="sb-uname">Slamet</span>
    </div>
  </aside>

  <!-- MAIN -->
  <div class="main-area">
    <div class="dashboard">

      <div class="top-header">
        <div class="welcome-text">
          <h2>Welcome Back, Slamet</h2>
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
                <i class="fas fa-flask"></i>
                <span>PH AIR</span>
              </div>
              <div class="sensor-value" id="val-ph">—</div>
              <span class="badge badge-danger" id="badge-ph">Menunggu...</span>
            </div>

            <!-- Cahaya -->
            <div class="sensor-card">
              <div class="sensor-card-header">
                <i class="fas fa-sun"></i>
                <span>Intesitas Cahaya</span>
              </div>
              <div class="sensor-value" id="val-lux">— <span class="sensor-unit">Lux</span></div>
              <span class="badge badge-warning" id="badge-lux">Menunggu...</span>
            </div>

            <!-- Warna Air — dengan tombol LIVE -->
            <div class="sensor-card">
              <div class="sensor-card-header">
                <i class="fas fa-tint"></i>
                <span>Warna Air Kolam</span>
              </div>
              <div class="warna-card-body">
                <div class="warna-box-wrap">
                  <div class="warna-box" id="warna-box" style="background:#b0bec5;"></div>
                </div>
                <span class="warna-skor" id="warna-skor"></span>
              </div>
              <span class="badge badge-normal" id="badge-warna">Menunggu...</span>
            </div>

          </div>

          <!-- STATUS BAR -->
          <div class="status-strip">
            <div class="online-indicator">
              <span class="pulse-dot"></span> Status: Online
            </div>
            <div><strong id="last-update">KOLAM 1 : —</strong></div>
          </div>

          <!-- CHART PANEL -->
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
                <h5>PH Air (normal 8.5-10.5)</h5>
                <canvas id="chartPH"></canvas>
                <div class="chart-stats"><span>Avg: —</span><span>Terakhir: —</span></div>
              </div>
              <div class="mini-chart">
                <h5>Intensitas Cahaya (Lux)</h5>
                <canvas id="chartCahaya"></canvas>
                <div class="chart-stats"><span>Avg: —</span><span>Terakhir: —</span></div>
              </div>
              <div class="mini-chart">
                <h5>Persentase Warna (%)</h5>
                <canvas id="chartWarna"></canvas>
                <div class="chart-stats"><span>Total: 0 record</span><span>Warna: —</span></div>
              </div>
            </div>
          </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="right-col">
          <div class="control-box">
            <div class="section-title"><span>⚡</span>Kontrol Otomatis ★ FULL AUTO</div>
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
            <div class="log-list">
              <div class="log-item">
                <div class="log-dot" style="background:#7b1fa2;"></div>
                <div class="log-time">14:20</div>
                <div class="log-msg">Pompa UV Aktif</div>
              </div>
              <div class="log-item">
                <div class="log-dot" style="background:#e86c2f;"></div>
                <div class="log-time">11:00</div>
                <div class="log-msg">pH Turun, Pompa Basa ON</div>
              </div>
              <div class="log-item">
                <div class="log-dot" style="background:#2d7dd2;"></div>
                <div class="log-time">08:30</div>
                <div class="log-msg">Data Terkirim ke Server</div>
              </div>
              <div class="log-item">
                <div class="log-dot" style="background:#1a9e60;"></div>
                <div class="log-time">07:00</div>
                <div class="log-msg">Sensor Online, pH 8.7</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-bar">
        <span>🌱 Nutrisi terakhir diberikan : <strong>22 Maret 2026</strong></span>
      </div>

    </div>
  </div>

</div>

<!-- ============================================================
     MODAL LIVE STREAM
     ============================================================ -->
<div class="modal-overlay" id="liveModal" onclick="closeLiveModalOutside(event)">
  <div class="modal-box">

    <div class="modal-header">
      <div class="modal-header-left">
        <div class="modal-live-badge"><span class="dot"></span> LIVE</div>
        <span class="modal-title">ESP32-CAM — Kolam Spirulina</span>
      </div>
      <button class="modal-close" onclick="closeLiveModal()"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-stream">
      <img id="stream-img"
           src=""
           alt="Live Stream"
           onerror="showStreamError()"
           onload="hideStreamError()">
      <div class="stream-error" id="stream-error">
        <i class="fas fa-video-slash"></i>
        <span>Stream tidak tersedia</span>
        <small>Pastikan ESP32-CAM aktif dan terhubung ke WiFi KOST 2</small>
      </div>
    </div>

    <div class="modal-overlay-info">
      <div class="fase-info">
        <span class="fase-badge none" id="modal-fase-badge">Tidak terdeteksi</span>
        <span style="color:rgba(255,255,255,0.4); font-size:0.65rem; font-weight:600;" id="modal-status-warna">—</span>
      </div>
      <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
        <div class="skor-bar-wrap">
          <span class="skor-label">Skor:</span>
          <div class="skor-bar"><div class="skor-fill" id="modal-skor-fill" style="width:0%"></div></div>
          <span class="skor-val" id="modal-skor-val">0.00</span>
        </div>
        <span class="menit-info" id="modal-menit">— menit lalu</span>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  const STREAM_URL  = 'http://192.168.0.150:81/stream';
  const RAILWAY_URL = 'https://worker-production-c170.up.railway.app/hasil_warna';

  // ── SIDEBAR ───────────────────────────────────────────────────
  document.querySelector('.sb-logo').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('expanded');
  });

  // ── DATE PICKER ───────────────────────────────────────────────
  function setToday() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('datePicker').value = today;
    loadCharts(today);
  }
  (function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('datePicker').value = today;
  })();
  document.getElementById('datePicker').addEventListener('change', function() {
    loadCharts(this.value);
  });

  // ── CHARTS ────────────────────────────────────────────────────
  const charts = {};
  function initChart(id, color) {
    charts[id] = new Chart(document.getElementById(id), {
      type: 'line',
      data: {
        labels: [],
        datasets: [{ data:[], borderColor:color, borderWidth:2, pointRadius:0, tension:0.3, fill:true, backgroundColor:color+'22' }]
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { display:false } },
        scales: {
          x: { ticks: { font:{size:9}, maxTicksLimit:6 } },
          y: { ticks: { font:{size:9} } }
        }
      }
    });
  }
  initChart('chartPH',     '#2d7dd2');
  initChart('chartCahaya', '#e67e22');
  initChart('chartWarna',  '#27ae60');

  function loadCharts(tanggal) {
    fetch('api_sensor.php?tanggal=' + tanggal + '&t=' + Date.now())
      .then(r => r.json())
      .then(res => {
        if (res.error || !res.data || !res.data.length) return;
        const d = res.data;
        charts['chartPH'].data.labels = d.map(r => r.waktu.slice(11,16));
        charts['chartPH'].data.datasets[0].data = d.map(r => r.pH);
        charts['chartPH'].update();
        charts['chartCahaya'].data.labels = d.map(r => r.waktu.slice(11,16));
        charts['chartCahaya'].data.datasets[0].data = d.map(r => r.cahaya);
        charts['chartCahaya'].update();
        charts['chartWarna'].data.labels = d.map(r => r.waktu.slice(11,16));
        charts['chartWarna'].data.datasets[0].data = d.map(r => r.persentase_warna);
        charts['chartWarna'].update();
        const ph  = d.map(r => parseFloat(r.pH)).filter(v => v > 0);
        const lux = d.map(r => parseInt(r.cahaya)).filter(v => v > 0);
        const avg = arr => arr.length ? (arr.reduce((a,b)=>a+b)/arr.length).toFixed(2) : '—';
        document.querySelectorAll('.chart-stats')[0].innerHTML = `<span>Avg: ${avg(ph)}</span><span>Terakhir: ${ph.at(-1)??'—'}</span>`;
        document.querySelectorAll('.chart-stats')[1].innerHTML = `<span>Avg: ${avg(lux)} lux</span><span>Terakhir: ${lux.at(-1)??'—'}</span>`;
        document.querySelectorAll('.chart-stats')[2].innerHTML = `<span>Total: ${d.length} record</span>`;
      })
      .catch(e => console.log('Chart error:', e));
  }

  // ── WARNA HELPER ─────────────────────────────────────────────
  function faseToBg(warna) {
    if (!warna) return '#b0bec5';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return '#f9c74f';
    if (w.includes('pertumbuhan') || w.includes('fase 2')) return '#4caf50';
    if (w.includes('optimal') || w.includes('fase 3'))    return '#0d6b53';
    if (w.includes('panen') || w.includes('fase 4'))      return '#1a237e';
    return '#b0bec5';
  }

  function faseToClass(warna) {
    if (!warna) return 'none';
    const w = warna.toLowerCase();
    if (w.includes('pembibitan') || w.includes('fase 1')) return 'fase1';
    if (w.includes('pertumbuhan') || w.includes('fase 2')) return 'fase2';
    if (w.includes('optimal') || w.includes('fase 3'))    return 'fase3';
    if (w.includes('panen') || w.includes('fase 4'))      return 'fase4';
    return 'none';
  }

  // ── STATE WARNA GLOBAL (dari cek_sensor.php / Railway) ───────
  let _warnaData = { warna:'tidak terdeteksi', status_warna:'-', skor_warna:0, warna_menit_lalu:null };

  // ── LOAD REAL-TIME ────────────────────────────────────────────
  function loadLatest() {
    fetch('cek_sensor.php?t=' + Date.now())
      .then(r => r.json())
      .then(s => {
        // pH
        document.getElementById('val-ph').textContent = parseFloat(s.pH).toFixed(2);
        const bp = document.getElementById('badge-ph');
        if (s.pH_status === 'rendah') {
          bp.className = 'badge badge-danger'; bp.textContent = 'pH Rendah (< 8.5)';
        } else if (s.pH_status === 'tinggi') {
          bp.className = 'badge badge-warning'; bp.textContent = 'pH Tinggi (> 9.0)';
        } else {
          bp.className = 'badge badge-normal'; bp.textContent = 'pH Normal';
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
        const warna      = s.warna        || 'tidak terdeteksi';
        const statusW    = s.status_warna || '-';
        const skorW      = parseFloat(s.skor_warna || s.persentase_warna || 0);
        const menitW     = s.warna_menit_lalu !== undefined ? s.warna_menit_lalu : null;

        _warnaData = { warna, status_warna: statusW, skor_warna: skorW, warna_menit_lalu: menitW };

        document.getElementById('warna-box').style.background = faseToBg(warna);
        document.getElementById('badge-warna').textContent = (statusW && statusW !== '-') ? statusW : warna;

        // Tampilkan skor kecil di bawah warna-box
        const skorEl = document.getElementById('warna-skor');
        if (skorW > 0) {
          skorEl.textContent = 'Skor: ' + skorW.toFixed(3);
        } else {
          skorEl.textContent = '';
        }

        // Update overlay modal kalau sedang terbuka
        updateModalOverlay();

        // Last update
        const mnt = s.menit_lalu;
        const waktuLabel = mnt === null ? '—' : mnt < 1 ? 'Baru saja' : mnt + ' menit lalu';
        document.getElementById('last-update').textContent = 'KOLAM 1 · ' + waktuLabel;

        // Toggle
        document.getElementById('togBasa').checked    = s.pompa_basa   === 'DOSING';
        document.getElementById('togNormal').checked  = s.pompa_normal === 'DOSING';
        document.getElementById('togUV').checked      = s.uv           === 'ON';
        document.getElementById('togNutrisi').checked = false;
      })
      .catch(() => {
        document.getElementById('last-update').textContent = 'KOLAM 1 · Offline';
      });
  }

  // ── MODAL LIVE STREAM ─────────────────────────────────────────
  function openLiveModal() {
    const modal = document.getElementById('liveModal');
    modal.classList.add('open');
    // Set src stream
    const img = document.getElementById('stream-img');
    img.src = STREAM_URL;
    document.getElementById('stream-error').style.display = 'none';
    img.style.display = 'block';
    updateModalOverlay();
  }

  function closeLiveModal() {
    document.getElementById('liveModal').classList.remove('open');
    // Stop stream dengan hapus src
    document.getElementById('stream-img').src = '';
  }

  function closeLiveModalOutside(e) {
    if (e.target === document.getElementById('liveModal')) closeLiveModal();
  }

  function showStreamError() {
    document.getElementById('stream-error').style.display = 'flex';
    document.getElementById('stream-img').style.display = 'none';
  }

  function hideStreamError() {
    document.getElementById('stream-error').style.display = 'none';
    document.getElementById('stream-img').style.display = 'block';
  }

  function updateModalOverlay() {
    const { warna, status_warna, skor_warna, warna_menit_lalu } = _warnaData;
    const badge = document.getElementById('modal-fase-badge');
    badge.textContent = warna || 'Tidak terdeteksi';
    badge.className   = 'fase-badge ' + faseToClass(warna);

    document.getElementById('modal-status-warna').textContent = (status_warna && status_warna !== '-') ? status_warna : '—';

    const pct = Math.min(100, Math.round((skor_warna || 0) * 100));
    document.getElementById('modal-skor-fill').style.width = pct + '%';
    document.getElementById('modal-skor-val').textContent  = (skor_warna || 0).toFixed(3);

    const mnt = warna_menit_lalu;
    document.getElementById('modal-menit').textContent =
      mnt === null ? '— menit lalu' :
      mnt < 1 ? 'Deteksi baru saja' :
      'Deteksi ' + mnt + ' menit lalu';
  }

  // ESC untuk tutup modal
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLiveModal(); });

  // ── JALANKAN ──────────────────────────────────────────────────
  loadLatest();
  loadCharts(document.getElementById('datePicker').value);
  setInterval(loadLatest, 10000);
</script>

</body>
</html>
