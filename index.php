<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
  <title>Monitoring ESP32 • Mikroalga Spirulina</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    /* ============================================================
       VARIABEL
       ============================================================ */
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

    /* ============================================================
       RESET
       ============================================================ */
    *, *::before, *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      color: var(--text);
      font-size: 14px;
      line-height: 1.5;
      overflow: hidden;
    }

    /* ============================================================
       LAYOUT UTAMA
       ============================================================ */
    .shell {
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* ============================================================
       SIDEBAR - KIRI
       ============================================================ */
    .sidebar {
      width: 220px;
      min-width: 100px;
      height: 100vh;
      background: var(--green-dark);
      display: flex;
      flex-direction: column;
      transition: width 0.25s ease, min-width 0.25s ease;
      overflow: hidden;
      flex-shrink: 0;
      z-index: 10;
  
    }

    .sidebar.expanded {
      width: var(--sidebar-w);
      min-width: var(--sidebar-w);
    }

    /* Sidebar - Logo */
    .sb-logo {
      height: 56px;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 0 14px;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      flex-shrink: 0;
      overflow: hidden;
      white-space: nowrap;
      border-radius: 50%;
    }

    .sb-icon-wrap {
      width: 28px;
      height: 28px;
      border-radius: 7px;
      background: rgba(255,255,255,0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
      flex-shrink: 0;
    }

    .sb-texts,
    .nav-lbl,
    .sb-uname {
      flex: 1;
      overflow: hidden;
      opacity: 1 !important;
      pointer-events: auto !important;
      transition: opacity 0.2s;
    }

    .sidebar.expanded .sb-texts {
      opacity: 1;
      pointer-events: auto;
    }

    .sb-title {
      color: #fff;
      font-weight: 800;
      font-size: 0.85rem;
      line-height: 1.2;
    }

    .sb-sub {
      color: rgba(255,255,255,0.5);
      font-size: 0.6rem;
      font-weight: 500;
    }

    /* Sidebar - Navigasi */
    .sb-nav {
      flex: 1;
      padding: 10px 0;
      overflow: hidden;
    }

    .sidebar-item {
      display: flex;
      align-items: center;
      gap: 12px;
      height: 40px;
      padding: 0 16px;
      color: rgba(255,255,255,0.65);
      font-size: 0.78rem;
      font-weight: 600;
      cursor: pointer;
      border-left: 3px solid transparent;
      transition: all 0.15s;
      text-decoration: none;
      white-space: nowrap;
      overflow: hidden;
    }

    .sidebar-item:hover {
      color: #fff;
      background: rgba(255,255,255,0.07);
    }

    .sidebar-item.active {
      color: #fff;
      background: rgba(255,255,255,0.1);
      border-left-color: var(--green-light);
    }

    .sidebar-item i {
      font-size: 0.9rem;
      width: 18px;
      text-align: center;
      flex-shrink: 0;
    }

    .nav-lbl {
      opacity: 0;
      transition: opacity 0.15s;
    }

    .sidebar.expanded .nav-lbl {
      opacity: 1;
    }

    /* Sidebar - Footer */
    .sb-foot {
      padding: 10px 14px;
      border-top: 1px solid rgba(255,255,255,0.1);
      flex-shrink: 0;
      overflow: hidden;
      white-space: nowrap;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .sb-av {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: var(--green-light);
      color: #fff;
      font-weight: 800;
      font-size: 0.72rem;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .sb-uname {
      color: rgba(255,255,255,0.75);
      font-size: 0.75rem;
      font-weight: 600;
      opacity: 0;
      transition: opacity 0.15s;
    }

    .sidebar.expanded .sb-uname {
      opacity: 1;
    }

    /* ============================================================
       MAIN AREA - KANAN
       ============================================================ */
    .main-area {
      flex: 1;
      min-width: 0;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }

    .dashboard {
      width: 100%;
      min-height: 100vh;
      max-width: 1050px;
      margin: 0 auto;
      padding: 14px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    /* ============================================================
       HEADER
       ============================================================ */
    .top-header {
      background: var(--green-dark);
      color: #fff;
      padding: 14px 22px;
      border-radius: var(--radius-lg);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }

    .welcome-text h2 {
      font-size: 1.2rem;
      font-weight: 800;
      letter-spacing: -0.2px;
      margin-bottom: 2px;
    }

    .welcome-text p {
      font-size: 0.68rem;
      font-weight: 600;
      opacity: 0.75;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }

    .user-badge {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .user-badge i {
      font-size: 1.3rem;
    }

    .user-badge span {
      font-weight: 700;
      font-size: 0.85rem;
    }

    /* ============================================================
       STATUS BAR
       ============================================================ */
    .status-strip {
      background: var(--green-dark);
      color: #fff;
      padding: 10px 22px;
      border-radius: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.78rem;
      font-weight: 600;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 0;
      margin-bottom: 0;
    }

    .online-indicator {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .pulse-dot {
      width: 9px;
      height: 9px;
      background: #2ecc71;
      border-radius: 50%;
      animation: pulse 1.8s infinite;
    }

    @keyframes pulse {
      0%, 100% { box-shadow: 0 0 0 0 rgba(46,204,113,0.5); }
      50%      { box-shadow: 0 0 0 6px rgba(46,204,113,0); }
    }

    /* ============================================================
       MAIN CONTENT (KIRI-KANAN)
       ============================================================ */
    .main-content {
      display: grid;
      grid-template-columns: 1fr 280px;
      gap: 12px;
      flex: 1;
      align-items: stretch;
    }

    .left-col {
      display: flex;
      flex-direction: column;
      gap: 12px;
      flex: 1;
    }

    /* ============================================================
       SENSOR CARDS
       ============================================================ */
    .sensor-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }

    .sensor-card {
      background: var(--white);
      border-radius: var(--radius-lg);
      padding: 14px 16px;
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
      text-align: center;
      height: 140px;
      margin-bottom: 0;
    }

    .sensor-card-header {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
      justify-content: center;
      margin-top: 13px;
    }

    .sensor-card-header i {
      font-size: 1rem;
      color: var(--text-soft);
    }

    .sensor-card-header span {
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--text-soft);
      letter-spacing: 0.5px;
    }

    .sensor-value {
      font-size: 2.2rem;
      font-weight: 800;
      line-height: 1.1;
      color: var(--text);
      margin-bottom: 6px;
      text-align: center;
    }

    .sensor-unit {
      font-size: 0.65rem;
      font-weight: 600;
      color: var(--text-soft);
      margin-left: 3px;
    }

    .badge {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 20px;
      font-size: 0.65rem;
      font-weight: 700;
      letter-spacing: 0.3px;
    }

    .badge-danger  { background: #fde8e8; color: #b91c1c; }
    .badge-warning { background: #fff3e0; color: #b85e1a; }
    .badge-normal  { background: #e6f5ee; color: #1a7a4c; }

    .warna-box {
      width: 90%;
      height: 34px;
      border-radius: 6px;
      margin: 4px 0 8px;
      align-items: center;
      justify-content: center;
      text-align: center;

    }

    /* ============================================================
       CHART PANEL
       ============================================================ */
    .chart-panel {
      background: var(--white);
      border-radius: var(--radius-lg);
      padding: 16px 18px;
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
      flex: 1;
      display: flex;
      flex-direction: column;
      margin-top: 0;
    }

    .chart-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      flex-wrap: wrap;
      gap: 8px;
    }

    .chart-header h4 {
      font-size: 0.8rem;
      font-weight: 800;
      color: var(--green-dark);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .date-selector {
      display: flex;
      align-items: center;
      gap: 6px;
      background: #f0f5f2;
      padding: 3px 10px;
      border-radius: 8px;
    }

    .date-selector input {
      border: none;
      background: transparent;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-weight: 600;
      font-size: 0.7rem;
      outline: none;
      color: var(--text);
    }

    .btn-today {
      background: var(--green-dark);
      color: #fff;
      border: none;
      padding: 4px 10px;
      border-radius: 6px;
      font-weight: 700;
      font-size: 0.65rem;
      cursor: pointer;
      font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .charts-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      flex: 1;
    }

    .mini-chart {
      background: #f9fbfa;
      border-radius: var(--radius-md);
      padding: 10px;
      border: 1px solid var(--border);
    }

    .mini-chart h5 {
      font-size: 0.6rem;
      font-weight: 700;
      text-transform: uppercase;
      color: var(--text-soft);
      margin-bottom: 5px;
      letter-spacing: 0.4px;
    }

    .mini-chart canvas {
      width: 100% !important;
      height: 100px !important;
    }

    .chart-stats {
      display: flex;
      gap: 6px;
      margin-top: 4px;
      font-size: 0.6rem;
      font-weight: 600;
      color: var(--text-soft);
    }

    /* ============================================================
       RIGHT COLUMN (KONTROL & LOG)
       ============================================================ */
    .right-col {
      display: flex;
      flex-direction: column;
      gap: 12px;
      flex: 1;
    }

    /* Kontrol */
    .control-box {
      background: var(--white);
      border-radius: var(--radius-lg);
      padding: 14px 16px;
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
    }

    .section-title {
      font-size: 0.71rem;
      font-weight: 800;
      text-transform: uppercase;
      color: var(--green-dark);
      letter-spacing: 0.5px;
      margin-bottom: 10px;
    }

    .section-title span{
      font-size: 1rem;
    }

    .control-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 7px 0;
      border-bottom: 1px solid #eef3f0;
    }

    .control-item:last-child {
      border-bottom: none;
    }

    .control-left {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .control-left i {
      font-size: 0.85rem;
      color: #1e6f5c;
      width: 18px;
    }

    .control-left span {
      font-weight: 700;
      font-size: 0.75rem;
    }

    /* Toggle */
    .toggle {
      position: relative;
      display: inline-block;
      width: 38px;
      height: 20px;
    }

    .toggle input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .toggle-slider {
      position: absolute;
      inset: 0;
      background: #c5d0cb;
      border-radius: 20px;
      cursor: pointer;
      transition: 0.25s;
    }

    .toggle-slider::before {
      content: '';
      position: absolute;
      width: 14px;
      height: 14px;
      left: 3px;
      top: 3px;
      background: #fff;
      border-radius: 50%;
      transition: 0.25s;
      box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }

    .toggle input:checked + .toggle-slider {
      background: var(--red);
    }

    .toggle input:checked + .toggle-slider.green {
      background: var(--green);
    }

    .toggle input:checked + .toggle-slider::before {
      transform: translateX(18px);
    }

    /* Log */
    .log-box {
      background: var(--white);
      border-radius: var(--radius-lg);
      padding: 14px 16px;
      box-shadow: var(--shadow);
      border: 1px solid var(--border);
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .log-list {
      display: flex;
      flex-direction: column;
    }

    .log-item {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      padding: 6px 0;
      border-bottom: 1px solid #f0f4f2;
    }

    .log-item:last-child {
      border-bottom: none;
    }

    .log-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      margin-top: 4px;
      flex-shrink: 0;
    }

    .log-time {
      font-size: 0.65rem;
      font-weight: 600;
      color: var(--text-soft);
      min-width: 35px;
    }

    .log-msg {
      font-size: 0.7rem;
      font-weight: 600;
      color: var(--text);
    }

    .toggle input:disabled + .toggle-slider {
    opacity: 1;
    cursor: default;
    }

    /* ============================================================
       FOOTER
       ============================================================ */
    .footer-bar {
      background: var(--green-dark);
      color: #fff;
      padding: 10px 22px;
      border-radius: 10px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
      font-size: 0.68rem;
      font-weight: 600;
      margin-top: auto;
    }

    .footer-bar strong {
      font-weight: 800;
    }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 950px) {
      .main-content { grid-template-columns: 1fr; }
      .sensor-row { grid-template-columns: repeat(2, 1fr); }
      .charts-row { grid-template-columns: 1fr; }
    }

    @media (max-width: 600px) {
      .sensor-row { grid-template-columns: 1fr; }
      .sidebar { width: 0; min-width: 0; }
      .sidebar.expanded { width: var(--sidebar-w); min-width: var(--sidebar-w); }
    }

    /* Scrollbar */
    .main-area::-webkit-scrollbar { width: 5px; }
    .main-area::-webkit-scrollbar-track { background: transparent; }
    .main-area::-webkit-scrollbar-thumb { background: #c4d4cd; border-radius: 5px; }
  </style>
</head>
<body>

  <!-- ============================================================
       WRAPPER
       ============================================================ -->
  <div class="shell">

    <!-- SIDEBAR KIRI -->
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

    <!-- MAIN AREA KANAN -->
    <div class="main-area">
      <div class="dashboard">

        <!-- HEADER -->
        <div class="top-header">
          <div class="welcome-text">
            <h2>Welcome Back, Slamet</h2>
            <p>Monitoring Dan Kontrol Kolam Mikroalga Spirulina Sp.</p>
          </div>
          <div class="user-badge">
            <i class="fas fa-user-circle"></i>
            <span>SLAMET</span>
          </div>
        </div>

        <!-- MAIN CONTENT -->
                <!-- MAIN CONTENT -->
        <div class="main-content">

          <!-- LEFT COLUMN -->
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

              <!-- Warna Air -->
              <div class="sensor-card">
                <div class="sensor-card-header">
                  <i class="fas fa-tint"></i>
                  <span>Warna Air Kolam</span>
                </div>
                <div class="warna-box" id="warna-box" style="background:#b0bec5;"></div>
                <span class="badge badge-normal" id="badge-warna">Menunggu...</span>
              </div>
            </div>

            <!-- STATUS BAR (DIPINDAHKAN KE SINI) -->
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
                  <input type="date" id="datePicker" value="2026-05-11">
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
      <!-- KONTROL OTOMATIS -->
    <div class="control-box">

      <div class="section-title">
        <span>⚡</span>Kontrol Otomatis ★ FULL AUTO
      </div>

      <!-- POMPA BASA -->
      <div class="control-item">
        <div class="control-left">
          <i class="fas fa-water"></i>
          <span>Pompa Basa</span>
        </div>

        <label class="toggle">
          <input type="checkbox" id="togBasa" disabled>
          <span class="toggle-slider"></span>
        </label>
      </div>

      <!-- POMPA NETRAL -->
      <div class="control-item">
        <div class="control-left">
          <i class="fas fa-water"></i>
          <span>Pompa Air Netral</span>
        </div>

        <label class="toggle">
          <input type="checkbox" id="togNormal" disabled>
          <span class="toggle-slider"></span>
        </label>
      </div>

      <!-- POMPA NUTRISI -->
      <div class="control-item">
        <div class="control-left">
          <i class="fas fa-leaf"></i>
          <span>Pompa Nutrisi</span>
        </div>

        <label class="toggle">
          <input type="checkbox" id="togNutrisi" disabled>
          <span class="toggle-slider green"></span>
        </label>
      </div>

      <!-- LAMPU UV -->
      <div class="control-item">
        <div class="control-left">
          <i class="fas fa-lightbulb"></i>
          <span>Lampu UV</span>
        </div>

        <label class="toggle">
          <input type="checkbox" id="togUV" disabled>
          <span class="toggle-slider green"></span>
        </label>
      </div>

    </div>

            <!-- LOG AKTIVITAS -->
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

        <!-- FOOTER NUTRISI -->
        <div class="footer-bar">
          <span>🌱 Nutrisi terakhir diberikan : <strong>22 Maret 2026</strong></span>
        </div>

      </div>
    </div>

  </div>

  <!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ── SIDEBAR ──────────────────────────────────────────────────
    document.querySelector('.sb-logo').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('expanded');
    });

    // ── DATE PICKER — default hari ini ───────────────────────────
    function setToday() {
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('datePicker').value = today;
      loadCharts(today);
    }
    // SET HARI INI SAAT LOAD
    (function() {
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('datePicker').value = today;
    })();

    document.getElementById('datePicker').addEventListener('change', function() {
      loadCharts(this.value);
    });

    // ── INIT CHARTS ───────────────────────────────────────────────
    const charts = {};
    function initChart(id, color) {
      charts[id] = new Chart(document.getElementById(id), {
        type: 'line',
        data: {
          labels: [],
          datasets: [{
            data: [], borderColor: color, borderWidth: 2,
            pointRadius: 0, tension: 0.3, fill: true,
            backgroundColor: color + '22'
          }]
        },
        options: {
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: { ticks: { font: { size: 9 }, maxTicksLimit: 6 } },
            y: { ticks: { font: { size: 9 } } }
          }
        }
      });
    }
    initChart('chartPH',     '#2d7dd2');
    initChart('chartCahaya', '#e67e22');
    initChart('chartWarna',  '#27ae60');

    // ── LOAD GRAFIK HARIAN ────────────────────────────────────────
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
          const avg = arr => arr.length ? (arr.reduce((a,b) => a+b) / arr.length).toFixed(2) : '—';

          document.querySelectorAll('.chart-stats')[0].innerHTML =
            `<span>Avg: ${avg(ph)}</span><span>Terakhir: ${ph.at(-1) ?? '—'}</span>`;
          document.querySelectorAll('.chart-stats')[1].innerHTML =
            `<span>Avg: ${avg(lux)} lux</span><span>Terakhir: ${lux.at(-1) ?? '—'}</span>`;
          document.querySelectorAll('.chart-stats')[2].innerHTML =
            `<span>Total: ${d.length} record</span>`;
        })
        .catch(e => console.log('Chart error:', e));
    }

    // ── LOAD REAL-TIME ────────────────────────────────────────────
    function loadLatest() {
      // tambah ?t= untuk bypass cache InfinityFree
      fetch('cek_sensor.php?t=' + Date.now())
        .then(r => r.json())
        .then(s => {

          // pH
          document.getElementById('val-ph').textContent = parseFloat(s.pH).toFixed(2);
          const bp = document.getElementById('badge-ph');
          if (s.pH_status === 'rendah') {
            bp.className = 'badge badge-danger';
            bp.textContent = 'pH Rendah (< 8.5)';
          } else if (s.pH_status === 'tinggi') {
            bp.className = 'badge badge-warning';
            bp.textContent = 'pH Tinggi (> 9.0)';
          } else {
            bp.className = 'badge badge-normal';
            bp.textContent = 'pH Normal';
          }

          // Cahaya
          document.getElementById('val-lux').innerHTML =
            s.cahaya + ' <span class="sensor-unit">Lux</span>';
          const bl = document.getElementById('badge-lux');
          if (s.lux_status === 'error') {
            bl.className = 'badge badge-danger';
            bl.textContent = 'Sensor Error';
          } else if (s.uv === 'ON') {
            bl.className = 'badge badge-warning';
            bl.textContent = 'Lampu UV ON';
          } else {
            bl.className = 'badge badge-normal';
            bl.textContent = 'Cahaya Cukup';
          }

          // Warna
          document.getElementById('warna-box').style.background = warnaToHex(s.warna);
          document.getElementById('badge-warna').textContent =
            (s.status_warna && s.status_warna !== '-') ? s.status_warna : s.warna;

          // Last update
          const mnt = s.menit_lalu;
          const waktuLabel = mnt === null ? '—'
            : mnt < 1 ? 'Baru saja'
            : mnt + ' menit lalu';
          document.getElementById('last-update').textContent = 'KOLAM 1 · ' + waktuLabel;

          // ── TOGGLE — FIX: pakai field yang benar dari cek_sensor.php ──
          document.getElementById('togBasa').checked    = s.pompa_basa   === 'DOSING';
          document.getElementById('togNormal').checked  = s.pompa_normal === 'DOSING';
          document.getElementById('togUV').checked      = s.uv           === 'ON';
          document.getElementById('togNutrisi').checked = false; // belum ada datanya
        })
        .catch(() => {
          document.getElementById('last-update').textContent = 'KOLAM 1 · Offline';
        });
    }

    function warnaToHex(nama) {
      const map = {
        'hijau':'#4caf50','kuning':'#f9c74f','coklat':'#8d6e63',
        'biru':'#2196f3','hitam':'#212121','merah':'#e53935',
        'tidak terdeteksi':'#b0bec5'
      };
      return map[(nama||'').toLowerCase()] || '#b0bec5';
    }

    // ── JALANKAN ──────────────────────────────────────────────────
    loadLatest();
    loadCharts(document.getElementById('datePicker').value);
    setInterval(loadLatest, 10000);  // polling 10 detik
</script>

</body>
</html>