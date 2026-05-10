<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gazebo — AI Güvenlik ve Uçak Kontrol</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" type="image/png" href="assets/gazebo.png">
<script src="https://unpkg.com/three@0.147.0/build/three.min.js"></script>
<script src="https://unpkg.com/three@0.147.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="https://unpkg.com/three@0.147.0/examples/js/controls/OrbitControls.js"></script>
<style>
@font-face {
  font-family: 'Thunderbolt';
  src: url('assets/thunderbolt.ttf') format('truetype');
}

:root {
  --bg-dark: #050608;
  --bg-panel: #08090b;
  --bg-card: #0d0f12;
  --border: #1a1d23;
  --text-main: #ffffff;
  --text-muted: #717782;
  --primary: #c4001d;
  --primary-bright: #ff1f3d;
  --red: #ff2a44;
  --green: #00e676;
  --cyan: #00f0ff;
  --yellow: #ffaa00;
  --gray: #2d3038;
  --glass: rgba(255, 255, 255, 0.03);
}

* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
body { background: var(--bg-dark); color: var(--text-main); height: 100vh; overflow: hidden; display: flex; }

/* LAYOUT */
.app-container { display: flex; width: 100%; height: 100%; }

/* LEFT SIDEBAR */
.sidebar-left { width: 260px; background: var(--bg-panel); border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 24px 0; }
.logo { display: flex; align-items: center; justify-content: center; padding: 12px 24px 24px 24px; border-bottom: 1px solid var(--border); }

.logo-text { display: flex; flex-direction: column; }
.logo-title { font-weight: 800; font-size: 1.2rem; letter-spacing: 1px; }
.logo-subtitle { font-size: 0.65rem; color: var(--text-muted); letter-spacing: 2px; }

.menu { flex: 1; padding: 24px; display: flex; flex-direction: column; gap: 8px; }
.menu-label { font-size: 0.65rem; color: var(--text-muted); letter-spacing: 1px; margin-bottom: 8px; }
.menu-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 20px; color: var(--text-muted); text-decoration: none; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; transition: all 0.2s; border: 1px solid transparent; }
.menu-item:hover { color: var(--text-main); background: rgba(255,255,255,0.02); }
.menu-item.active { color: #fff; background: linear-gradient(90deg, rgba(196, 0, 29, 0.2) 0%, rgba(196, 0, 29, 0) 100%); border-left: 3px solid var(--primary); box-shadow: -10px 0 20px rgba(196, 0, 29, 0.1); border-radius: 0 20px 20px 0; }
.menu-item.active .menu-icon { color: var(--primary-bright); }
.menu-icon { width: 16px; text-align: center; font-size: 1rem; }

.version { padding: 24px; font-size: 0.65rem; color: var(--text-muted); letter-spacing: 1px; font-weight: 600; }

/* MAIN CONTENT */
.main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

/* HEADER */
.top-bar { display: flex; justify-content: space-between; align-items: center; padding: 20px 32px; border-bottom: 1px solid var(--border); background: var(--bg-panel); }
.breadcrumb { font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 12px; }
.breadcrumb .dot { color: var(--primary); font-size: 1.2rem; line-height: 0; }
.breadcrumb .muted { color: var(--text-muted); font-weight: 600; letter-spacing: 0.5px; }
.live-badge { display: flex; align-items: center; padding: 6px 16px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; border: 1px solid rgba(0,230,118,0.3); color: var(--green); background: rgba(0,230,118,0.05); transition: all 0.3s; }
.live-badge span:not(:empty) { margin-left: 8px; }
.live-badge.offline { border-color: var(--text-muted); color: var(--text-muted); background: rgba(107,114,128,0.1); }
.live-badge.threat { border-color: var(--red); color: var(--red); background: rgba(255,42,68,0.1); animation: pulse 1s infinite alternate; }
.badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; box-shadow: 0 0 8px currentColor; }
@keyframes pulse { to { opacity: 0.5; } }

/* CONTENT WRAPPER */
.content-wrapper { display: flex; flex: 1; overflow: hidden; padding: 24px; gap: 24px; }

/* VIDEO CONTAINER */
.video-container { flex: 1; background: #050507; border-radius: 24px; border: 1px solid var(--border); position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: inset 0 0 50px rgba(0,0,0,0.5); }
#live-img { width: 100%; height: 100%; object-fit: contain; display: none; }
#no-signal { color: var(--text-muted); font-size: 0.9rem; font-weight: 500; display: flex; flex-direction: column; align-items: center; gap: 12px; }

/* PILL OVERLAY */
.video-pill { position: absolute; bottom: 32px; padding: 12px 32px; background: rgba(10,11,14,0.8); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 30px; display: flex; align-items: center; gap: 20px; font-size: 1.1rem; font-weight: 800; letter-spacing: 1px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
.pill-label { color: var(--text-muted); }
.pill-val { color: #fff; font-size: 1.5rem; }
.pill-val.red { color: var(--red); }
.video-pill .divider { width: 1px; height: 24px; background: rgba(255,255,255,0.2); }

/* RIGHT SIDEBAR */
.sidebar-right { width: 340px; display: flex; flex-direction: column; gap: 32px; overflow-y: auto; padding-right: 8px; }
.sidebar-right::-webkit-scrollbar { width: 4px; }
.sidebar-right::-webkit-scrollbar-thumb { background: var(--gray); border-radius: 4px; }

.tab-content { display: none; flex-direction: column; animation: fadeIn 0.3s; }
.tab-content.active { display: flex; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }

.section-title { font-size: 0.65rem; color: var(--text-muted); letter-spacing: 2px; font-weight: 700; margin-bottom: 16px; }

/* STATS GRID */
.stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.stat-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 20px; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
.stat-label { font-size: 0.6rem; color: var(--text-muted); letter-spacing: 1px; font-weight: 600; }
.stat-val { font-size: 1.8rem; font-weight: 800; display: flex; align-items: baseline; gap: 6px; }
.stat-val.red { color: var(--red); }
.stat-val.primary { color: var(--primary); }
.stat-val .small { font-size: 0.65rem; color: var(--text-muted); font-weight: 700; letter-spacing: 1px; }

/* BUTTONS */
.motor-controls { display: flex; gap: 12px; }
.btn { flex: 1; padding: 12px 0; border-radius: 12px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; cursor: pointer; transition: all 0.2s; background: transparent; border: 1px solid; }
.btn-green { color: var(--green); border-color: rgba(0,230,118,0.2); }
.btn-green:hover { background: rgba(0,230,118,0.1); border-color: var(--green); box-shadow: 0 0 15px rgba(0,230,118,0.1); }
.btn-gray { color: var(--text-muted); border-color: var(--border); background: var(--bg-card); }
.btn-gray:hover { color: #fff; border-color: var(--text-muted); background: var(--glass); }
.btn-red { background: linear-gradient(135deg, #c4001d 0%, #800013 100%); color: #fff; border: none; box-shadow: 0 4px 15px rgba(196, 0, 29, 0.3); }
.btn-red:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(196, 0, 29, 0.4); }
.btn:disabled { opacity: 0.4; cursor: not-allowed; transform: scale(0.96); filter: grayscale(0.5); border-color: var(--border) !important; background: rgba(0,0,0,0.2) !important; }

/* SLIDER */
.sens-control { display: flex; flex-direction: column; gap: 12px; }
.sens-header, .sens-footer { display: flex; justify-content: space-between; font-size: 0.65rem; font-weight: 700; letter-spacing: 1px; color: var(--text-muted); }
.sens-header .primary { color: var(--primary); }
input[type=range] { -webkit-appearance: none; width: 100%; background: transparent; }
input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; height: 16px; width: 16px; border-radius: 50%; background: var(--primary); cursor: pointer; margin-top: -6px; box-shadow: 0 0 10px rgba(255,31,61,0.5); }
input[type=range]::-webkit-slider-runnable-track { width: 100%; height: 4px; cursor: pointer; background: var(--gray); border-radius: 2px; }

/* RADIO GROUP */
.radio-group { display: flex; flex-direction: column; gap: 16px; }
.radio-label { display: flex; align-items: center; gap: 12px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; cursor: pointer; color: var(--text-muted); transition: color 0.2s; }
.radio-label input { display: none; }
.custom-radio { width: 16px; height: 16px; border-radius: 50%; border: 2px solid var(--border); position: relative; transition: all 0.2s; }
.radio-label input:checked + .custom-radio { border-color: var(--primary); }
.radio-label input:checked + .custom-radio::after { content: ''; position: absolute; top: 3px; left: 3px; width: 6px; height: 6px; background: var(--primary); border-radius: 50%; }
.radio-label input:checked ~ span, .radio-label:hover { color: var(--text-main); }

/* UPLOAD ZONE */
.upload-zone { margin-top: 32px; border: 1px dashed rgba(255,255,255,0.1); border-radius: 20px; background: var(--bg-card); padding: 40px 20px; text-align: center; cursor: pointer; transition: all 0.2s; }
.upload-zone:hover { border-color: var(--primary); background: rgba(255,31,61,0.02); }
.upload-icon { font-size: 2rem; margin-bottom: 12px; opacity: 0.5; }
.upload-text { font-size: 0.75rem; font-weight: 700; letter-spacing: 1px; color: var(--primary); }
#upload-status { margin-top: 12px; font-size: 0.75rem; text-align: center; font-weight: 600; }

/* ALERTS */
.alerts-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.btn-clear { background: transparent; border: none; color: var(--red); font-size: 0.65rem; font-weight: 700; letter-spacing: 1px; cursor: pointer; }
.btn-clear:hover { opacity: 0.8; }
.alerts-list { display: flex; flex-direction: column; gap: 12px; }
.alert-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 12px; display: flex; gap: 12px; align-items: center; }
.alert-img { width: 60px; height: 40px; border-radius: 8px; object-fit: cover; background: #000; }
.alert-info { flex: 1; }
.alert-title { font-size: 0.75rem; font-weight: 700; color: var(--red); margin-bottom: 4px; letter-spacing: 0.5px; }
.alert-time { font-size: 0.65rem; color: var(--text-muted); }
.alert-dl { color: var(--text-muted); cursor: pointer; padding: 8px; }
.alert-dl:hover { color: #fff; }

/* MODALS */
.modal-bg { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); backdrop-filter: blur(10px); z-index: 100; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.4s ease; }
.modal-bg.show { display: flex; opacity: 1; }
.modal { background: var(--bg-panel); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 32px; width: 90%; max-width: 900px; max-height: 90vh; overflow-y: auto; box-shadow: 0 40px 100px rgba(0,0,0,0.8); transform: scale(0.9) translateY(20px); filter: blur(10px); transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-bg.show .modal { transform: scale(1) translateY(0); filter: blur(0); }
.modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.modal-head h2 { font-size: 1.2rem; font-weight: 800; letter-spacing: 1px; }
.modal-body { display: flex; gap: 24px; }
.modal-img-container { flex: 1; display: flex; align-items: center; justify-content: center; background: #000; border-radius: 16px; border: 1px solid var(--border); overflow: hidden; padding: 2px; }
.modal-info { width: 320px; display: flex; flex-direction: column; gap: 16px; }

/* Score Box for Plane Modal */
.score-box { text-align: center; padding: 20px 24px; border-radius: 16px; font-weight: 800; letter-spacing: 1px; }
.score-go { background: rgba(0,230,118,0.08); color: var(--green); border: 1px solid rgba(0,230,118,0.25); }
.score-limited { background: rgba(0,240,255,0.08); color: #00f0ff; border: 1px solid rgba(0,240,255,0.25); }
.score-conditional { background: rgba(255,170,0,0.1); color: #ffaa00; border: 1px solid rgba(255,170,0,0.3); }
.score-nogo { background: rgba(255,42,68,0.1); color: var(--red); border: 1px solid rgba(255,42,68,0.3); }
.score-val { font-size: 2.8rem; line-height: 1; margin-bottom: 6px; font-weight: 900; }
.score-label { font-size: 0.78rem; font-weight: 800; letter-spacing: 2px; }

.finding-list { display: flex; flex-direction: column; gap: 10px; max-height: 480px; overflow-y: auto; padding-right: 6px; }
.finding-list::-webkit-scrollbar { width: 3px; }
.finding-list::-webkit-scrollbar-thumb { background: var(--gray); border-radius: 3px; }
.finding-item { padding: 12px 14px; border: 1px solid var(--border); border-radius: 12px; background: var(--bg-card); transition: border-color 0.2s; }
.finding-item:hover { border-color: rgba(255,255,255,0.08); }
.finding-kritik { border-left: 3px solid var(--red); }
.finding-yüksek { border-left: 3px solid #ffaa00; }
.finding-orta { border-left: 3px solid #00f0ff; }
.finding-düşük { border-left: 3px solid var(--green); }
.f-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px; gap: 8px; }
.f-title { font-weight: 700; font-size: 0.78rem; color: var(--text-main); flex: 1; line-height: 1.4; }
.f-badge { font-size: 0.58rem; font-weight: 800; letter-spacing: 1px; padding: 2px 7px; border-radius: 20px; white-space: nowrap; }
.f-badge-kritik { background: rgba(255,42,68,0.15); color: var(--red); }
.f-badge-yüksek { background: rgba(255,170,0,0.15); color: #ffaa00; }
.f-badge-orta { background: rgba(0,240,255,0.1); color: #00f0ff; }
.f-badge-düşük { background: rgba(0,230,118,0.1); color: var(--green); }
.f-action { color: var(--text-muted); font-size: 0.7rem; margin-bottom: 8px; line-height: 1.5; }
.f-meta { display: flex; align-items: center; justify-content: space-between; font-size: 0.62rem; font-weight: 700; color: var(--text-muted); gap: 8px; }
.f-bar-wrap { flex: 1; height: 3px; background: rgba(255,255,255,0.07); border-radius: 2px; overflow: hidden; }
.f-bar { height: 100%; border-radius: 2px; transition: width 0.6s ease; }
.f-zone { font-size: 0.55rem; letter-spacing: 1px; padding: 1px 6px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); color: var(--text-muted); }
/* Detection list for security modal */
.detection-list { display: flex; flex-direction: column; gap: 8px; margin-top: 16px; max-height: 180px; overflow-y: auto; }
.det-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 10px; font-size: 0.75rem; }
.det-icon { font-size: 1rem; }
.det-name { font-weight: 700; flex: 1; }
.det-conf-bar { width: 60px; height: 4px; background: rgba(255,255,255,0.07); border-radius: 2px; overflow: hidden; }
.det-conf-fill { height: 100%; border-radius: 2px; }
.det-conf-val { font-size: 0.65rem; color: var(--text-muted); font-weight: 600; width: 32px; text-align: right; }
/* Threat banner */
.threat-banner { background: rgba(255,42,68,0.12); border: 1px solid rgba(255,42,68,0.3); border-radius: 12px; padding: 10px 16px; display: flex; align-items: center; gap: 10px; font-size: 0.78rem; font-weight: 700; color: var(--red); letter-spacing: 0.5px; animation: pulse 1.2s infinite alternate; }
/* Priority counts row */
.prio-row { display: flex; gap: 8px; flex-wrap: wrap; }
.prio-chip { display: flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; letter-spacing: 0.5px; }
.prio-chip-kritik { background: rgba(255,42,68,0.12); color: var(--red); border: 1px solid rgba(255,42,68,0.2); }
.prio-chip-yüksek { background: rgba(255,170,0,0.12); color: #ffaa00; border: 1px solid rgba(255,170,0,0.2); }
.prio-chip-orta { background: rgba(0,240,255,0.08); color: #00f0ff; border: 1px solid rgba(0,240,255,0.15); }
.prio-chip-düşük { background: rgba(0,230,118,0.08); color: var(--green); border: 1px solid rgba(0,230,118,0.15); }

@media (max-width: 900px) {
  .modal-body { flex-direction: column; }
  .modal-info { width: 100%; }
  .content-wrapper { flex-direction: column; overflow-y: auto; }
  .sidebar-right { width: 100%; }
}

/* QUOTE CARD */
.quote-card {
  margin-top: 24px;
  position: relative;
  background: linear-gradient(135deg, rgba(196,0,29,0.06) 0%, rgba(8,9,11,0) 60%);
  border: 1px solid rgba(196,0,29,0.18);
  border-radius: 20px;
  padding: 28px 24px 22px;
  cursor: pointer;
  overflow: hidden;
  transition: border-color 0.3s;
}
.quote-card:hover { border-color: rgba(196,0,29,0.38); }
.quote-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(196,0,29,0.5), transparent);
}
.quote-mark {
  font-family: Georgia, serif;
  font-size: 4rem;
  line-height: 0.6;
  color: rgba(196,0,29,0.25);
  margin-bottom: 12px;
  display: block;
  font-weight: 900;
}
.quote-text {
  font-size: 0.82rem;
  font-weight: 500;
  line-height: 1.7;
  color: rgba(255,255,255,0.88);
  font-style: italic;
  letter-spacing: 0.3px;
  transition: opacity 0.6s ease, transform 0.6s ease;
}
.quote-text.fading {
  opacity: 0;
  transform: translateY(6px);
}
.quote-author {
  margin-top: 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: opacity 0.6s ease;
}
.quote-author.fading { opacity: 0; }
.quote-author-line {
  width: 24px; height: 1px;
  background: var(--primary);
}
.quote-author-name {
  font-size: 0.65rem;
  font-weight: 800;
  letter-spacing: 2px;
  color: var(--primary);
  text-transform: uppercase;
}
.quote-dot-row {
  display: flex;
  gap: 6px;
  margin-top: 18px;
  justify-content: center;
}
.quote-dot {
  width: 4px; height: 4px;
  border-radius: 50%;
  background: rgba(255,255,255,0.12);
  transition: background 0.3s, transform 0.3s;
}
.quote-dot.active {
  background: var(--primary);
  transform: scale(1.4);
}

/* STARTUP OVERLAY */
#startup-overlay {
  position: fixed; inset: 0;
  background: url('assets/bg_red_smoke.png') no-repeat center center;
  background-size: cover;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}


  cursor: pointer;
  overflow: hidden;
  /* Blur + fade-out kapanma */
  transition: opacity 0.9s ease, filter 0.9s ease, visibility 0.9s;
  filter: blur(0px);
}
#startup-overlay.closing {
  opacity: 0;
  filter: blur(18px);
  pointer-events: none;
}
#startup-overlay.hidden { display: none; }
#particle-canvas { position: absolute; inset: 0; z-index: 0; }
/* Three.js tüm ekranı kaplıyor — uçak boydan boya */
#three-container {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
}
/* Logo + içerik alta sabitlenmiş */
.startup-content {
  position: absolute;
  bottom: 48px;
  left: 50%;
  transform: translateX(-50%);
  text-align: center;
  z-index: 10;
  pointer-events: none;
}
.startup-logo {
  width: 140px; height: auto;
  filter: drop-shadow(0 0 20px rgba(196,0,29,0.5)) drop-shadow(0 0 6px rgba(255,255,255,0.1));
  opacity: 0.9;
  margin-bottom: 6px;
}
.startup-subtitle {
  font-size: 0.68rem;
  letter-spacing: 5px;
  color: rgba(255,255,255,0.4);
  font-weight: 700;
  text-transform: uppercase;
}
/* Tıkla iması */
.tap-hint {
  margin-top: 20px;
  font-size: 0.65rem;
  letter-spacing: 4px;
  color: rgba(255,255,255,0.25);
  font-weight: 600;
  text-transform: uppercase;
  animation: hintPulse 2.5s ease-in-out infinite;
}
@keyframes hintPulse {
  0%, 100% { opacity: 0.25; }
  50% { opacity: 0.7; }
}
.loading-bar-container {
  width: 220px; height: 2px;
  background: rgba(255,255,255,0.06);
  margin: 18px auto 0;
  position: relative; overflow: hidden;
  border-radius: 2px;
}
.loading-bar {
  position: absolute; left: 0; top: 0; height: 100%; width: 0%;
  background: linear-gradient(90deg, var(--primary), #ff6080);
  box-shadow: 0 0 12px var(--primary);
  transition: width 0.4s ease;
  border-radius: 2px;
}

@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
@keyframes floatUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
</style>
</head>
<body>

<div id="startup-overlay">
  <canvas id="particle-canvas"></canvas>
  <div id="three-container"></div>
  <div class="startup-content">
    <img src="assets/gazebo.png" class="startup-logo" alt="GAZEBO">
    <div class="startup-subtitle">Tactical AI &amp; Aircraft Inspection System</div>
    <div class="loading-bar-container">
      <div class="loading-bar" id="loading-bar"></div>
    </div>
    <div class="tap-hint" id="tap-hint" style="display:none;">Devam etmek için tıklayın</div>
  </div>
</div>



<div class="app-container">
  <!-- LEFT SIDEBAR -->
  <aside class="sidebar-left">
    <div class="logo" style="border:none; padding: 30px 24px; justify-content: center; align-items: center;">
      <img src="assets/gazebo.png" alt="GAZEBO" style="width: 220px; height: auto; filter: drop-shadow(0 0 10px rgba(255,255,255,0.1));" onerror="this.style.display='none'; document.getElementById('logo-fallback').style.display='block'">
      <div id="logo-fallback" style="display:none;">
        <div style="font-size: 1.5rem; font-weight: 900; letter-spacing: 4px; color: var(--primary-bright); text-shadow: 0 0 15px rgba(255,31,61,0.5);">GAZEBO</div>
        <div style="font-size: 0.6rem; letter-spacing: 6px; color: var(--text-muted); margin-top: 4px; font-weight: 700;">TESPİT TAKİP</div>
      </div>
    </div>
    <div class="menu">
      <div class="menu-label">ANA KONTROL</div>
      <a href="#" class="menu-item active" data-tab="tab-live">
        <span class="menu-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M15 5a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
            <path d="M7 5a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
            <path d="M7 19a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
            <path d="M16 12h.01" />
            <path d="M8 12h.01" />
            <path d="M16 19h.01" />
          </svg>
        </span> CANLI İZLEME
      </a>
      <a href="#" class="menu-item" data-tab="tab-assets">
        <span class="menu-icon">🛢</span> VARLIK KAYDI
      </a>
      <a href="#" class="menu-item" data-tab="tab-ai">
        <span class="menu-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M7 8l-4 4l4 4" />
            <path d="M17 8l3.111 3.111" />
            <path d="M14 4l-2.175 8.7" />
            <path d="M14 21v-4a2 2 0 1 1 4 0v4" />
            <path d="M14 19h4" />
            <path d="M21 15v6" />
          </svg>
        </span> YAPAY ZEKA
      </a>
      <a href="#" class="menu-item" data-tab="tab-config">
        <span class="menu-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0" />
            <path d="M12 9h.01" />
            <path d="M11 12h1v4h1" />
          </svg>
        </span> YAPILANDIRMA
      </a>
      <a href="#" class="menu-item" data-tab="tab-osint">
        <span class="menu-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M3 17a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
            <path d="M3 7a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
            <path d="M19 17a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
            <path d="M19 7a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
            <path d="M11 3a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
            <path d="M11 21a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
            <path d="M12 21l-8 -4v-10l8 -4l8 4v10l-8 4" />
            <path d="M9.1 15a2.1 2.1 0 0 1 -.648 -4.098c.282 -1.648 1.319 -2.902 3.048 -2.902c1.694 0 2.906 1.203 3.23 2.8h.17a2.1 2.1 0 0 1 .202 4.19l-.202 .01h-5.8" />
            <path d="M4 7l4.323 2.702" />
            <path d="M16.413 14.758l3.587 2.242" />
            <path d="M4 17l3.529 -2.206" />
            <path d="M14.609 10.37l5.391 -3.37" />
            <path d="M12 3v5" />
            <path d="M12 15v6" />
          </svg>
        </span> PALANTİR
      </a>
      <a href="#" class="menu-item" onclick="document.getElementById('guide-modal').classList.add('show')">
        <span class="menu-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
            <path d="M9 5a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2" />
            <path d="M9 14l2 2l4 -4" />
          </svg>
        </span> KULLANIM REHBERİ
      </a>
      <a href="#" class="menu-item" onclick="document.getElementById('about-modal').classList.add('show')">
        <span class="menu-icon">ℹ</span> HAKKIMIZDA
      </a>
    </div>
    <div class="version">GAZEBO TACTICAL V4.0.2</div>
  </aside>

  <!-- MAIN AREA -->
  <main class="main-content">
    <header class="top-bar">
      <div class="breadcrumb" style="font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 12px;">
        <img src="assets/gazebo.png" alt="Logo" style="height: 30px; width: auto; filter: drop-shadow(0 0 5px rgba(255,255,255,0.2));"> 
        <span style="color: var(--primary); font-size: 1.5rem; text-shadow: 0 0 8px rgba(255,42,68,0.6);">•</span> 
        <span style="color: rgba(255,255,255,0.7); font-weight: 600; font-size: 0.85rem; letter-spacing: 3px;">AI GÜVENLİK</span>
      </div>

      <div class="live-badge offline" id="status-badge">
        <div class="badge-dot"></div> <span id="status-text"></span>
      </div>
    </header>

    <div class="content-wrapper">
      <!-- OSINT CONTAINER -->
      <div id="osint-container" style="display:none; flex: 1; border-radius: 24px; overflow: hidden; background: var(--bg-panel); border: 1px solid var(--border);">
         <?php include 'osint_module.php'; ?>
      </div>

      <!-- VIDEO CONTAINER -->
      <div class="video-container" id="main-video-container">
        <div id="no-signal">
          <div style="opacity:0.3; margin-bottom:15px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M17.464 6.518a6 6 0 1 0 -3.023 7.965" />
              <path d="M17.482 17.464a6 6 0 1 0 -7.965 -3.023" />
              <path d="M6.54 17.482a6 6 0 1 0 3.024 -7.965" />
              <path d="M6.518 6.54a6 6 0 1 0 7.965 3.024" />
            </svg>
          </div>
          Sistem başlatılıyor veya kapalı...
        </div>
        <img id="live-img" alt="Canlı Yayın">
        <div class="video-pill" id="video-pill" style="display:none;">
          <span class="pill-label">İNSAN:</span> <span class="pill-val" id="pill-person">0</span>
          <div class="divider"></div>
          <span class="pill-label">SİLAHLI:</span> <span class="pill-val red" id="pill-weapon">0</span>
        </div>
      </div>

      <!-- RIGHT SIDEBAR -->
      <aside class="sidebar-right" id="main-sidebar-right">
        
        <!-- TAB: CANLI İZLEME -->
        <div id="tab-live" class="tab-content active">
          <div class="section-title">ANLIK GÜVENLİK İSTATİSTİKLERİ</div>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-label">[FPS]</div>
              <div class="stat-val"><span id="s-fps">–</span> <span class="small">FPS</span></div>
            </div>
            <div class="stat-card">
              <div class="stat-label">[ALARM]</div>
              <div class="stat-val red"><span id="s-alarm">0</span> <span class="small">ALARM</span></div>
            </div>
            <div class="stat-card">
              <div class="stat-label">[KİŞİ]</div>
              <div class="stat-val primary"><span id="s-person">0</span> <span class="small">KİŞİ</span></div>
            </div>
            <div class="stat-card">
              <div class="stat-label">[SİLAHLI]</div>
              <div class="stat-val red"><span id="s-weapon">0</span> <span class="small">SİLAHLI</span></div>
            </div>
          </div>

          <div class="section-title" style="margin-top: 32px;">MOTOR KONTROLÜ</div>
          <div class="motor-controls">
            <button id="btn-start" class="btn btn-green">BAŞLAT</button>
            <button id="btn-pause" class="btn btn-gray">DURAKLAT</button>
            <button id="btn-stop" class="btn btn-red">DURDUR</button>
          </div>

          <div class="sens-control" style="margin-top: 32px;">
            <div class="sens-header">
              <span>HASSASİYET (EŞİK)</span>
              <span id="conf-val" class="primary">%60</span>
            </div>
            <input type="range" id="conf-slider" min="10" max="90" value="60">
            <div class="sens-footer">
              <span>GENİŞ</span>
              <span>KESİN</span>
            </div>
          </div>
        </div>

        <!-- TAB: YAPAY ZEKA -->
        <div id="tab-ai" class="tab-content">
          <div class="radio-group">
            <label class="radio-label">
              <input type="radio" name="atype" value="person" checked>
              <span class="custom-radio"></span>
              GÜVENLİK (İNSAN/SİLAH)
            </label>
            <label class="radio-label">
              <input type="radio" name="atype" value="plane">
              <span class="custom-radio"></span>
              ÖN UÇUŞ (HASAR/BAKIM)
            </label>
          </div>

          <div class="upload-zone" id="upload-zone">
            <input type="file" id="file-input" accept="image/*" style="display:none">
            <div class="upload-icon" style="color:var(--primary); opacity:0.8;">
              <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M17.464 6.518a6 6 0 1 0 -3.023 7.965" />
                <path d="M17.482 17.464a6 6 0 1 0 -7.965 -3.023" />
                <path d="M6.54 17.482a6 6 0 1 0 3.024 -7.965" />
                <path d="M6.518 6.54a6 6 0 1 0 7.965 3.024" />
              </svg>
            </div>
            <div class="upload-text">FOTOĞRAF YÜKLE</div>
          </div>
          <div id="upload-status"></div>

          <div class="alerts-header" style="margin-top: 32px;">
            <div class="section-title" style="margin:0;">GÜVENLİK ALARMLARI</div>
            <button id="btn-clear" class="btn-clear">TEMİZLE</button>
          </div>
          
          <div class="alerts-list" id="alert-list">
            <div style="text-align:center; color:var(--text-muted); font-size:0.75rem; padding:20px;">Kayıt yok</div>
          </div>
        </div>

        <!-- TAB: VARLIK KAYDI -->
        <div id="tab-assets" class="tab-content">
          <div class="section-title">TOPLAM TESPİT ÖZETİ</div>
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-label">TOPLAM İNSAN</div>
              <div class="stat-val"><span id="a-total-person">0</span></div>
            </div>
            <div class="stat-card">
              <div class="stat-label">TOPLAM SİLAH</div>
              <div class="stat-val red"><span id="a-total-weapon">0</span></div>
            </div>
          </div>

          <!-- Dönen Söz Kartı -->
          <div class="quote-card" id="quote-card" title="Sonraki söze geçmek için tıklayın">
            <span class="quote-mark">&ldquo;</span>
            <div class="quote-text" id="quote-text"></div>
            <div class="quote-author">
              <div class="quote-author-line"></div>
              <div class="quote-author-name">Selçuk Bayraktar</div>
            </div>
            <div class="quote-dot-row" id="quote-dots"></div>
          </div>
        </div>

        <!-- TAB: YAPILANDIRMA -->
        <div id="tab-config" class="tab-content">
          <div class="section-title">SİSTEM YAPILANDIRMASI</div>
          <form id="config-form" style="display:flex; flex-direction:column; gap:20px;">
            <div class="stat-card" style="gap:8px;">
              <div class="stat-label">KAMERA İNDEKSİ</div>
              <input type="number" id="cfg-cam-index" value="0" style="background:var(--bg-dark); border:1px solid var(--border); color:#fff; padding:8px; border-radius:8px;">
            </div>
            <div class="stat-card" style="gap:8px;">
              <div class="stat-label">ÇÖZÜNÜRLÜK</div>
              <select id="cfg-resolution" style="background:var(--bg-dark); border:1px solid var(--border); color:#fff; padding:8px; border-radius:8px;">
                <option value="640x480">640x480 (Hızlı)</option>
                <option value="1280x720">1280x720 (HD)</option>
                <option value="1920x1080" selected>1920x1080 (Full HD)</option>
              </select>
            </div>
            <div class="stat-card" style="gap:8px;">
              <div class="stat-label">ALARM AYARLARI</div>
              <label class="radio-label"><input type="checkbox" id="cfg-alert-sound" checked> <span class="custom-radio" style="border-radius:4px;"></span> SESLİ UYARI</label>
              <label class="radio-label"><input type="checkbox" id="cfg-alert-screenshot" checked> <span class="custom-radio" style="border-radius:4px;"></span> EKRAN GÖRÜNTÜSÜ KAYDET</label>
            </div>
            <button type="button" id="btn-save-config" class="btn btn-green">AYARLARI KAYDET VE YENİDEN BAŞLAT</button>
          </form>
        </div>

      </aside>
    </div>
  </main>
</div>

<!-- Modal: About -->
<div class="modal-bg" id="about-modal">
  <div class="modal" style="max-width: 680px; text-align: center; border: 1px solid rgba(196, 0, 29, 0.3); background: linear-gradient(180deg, var(--bg-panel) 0%, #0a0b0e 100%);">
    <div class="modal-head" style="justify-content:center; position:relative; margin-bottom: 40px;">
      <h2 style="font-family: 'Thunderbolt', sans-serif; color: #fff; font-size: 2.2rem; letter-spacing: 4px; text-shadow: 0 0 20px rgba(196, 0, 29, 0.4); font-weight: normal;">HAKKIMIZDA</h2>
      <button class="btn-clear" style="position:absolute; right:0; top: 50%; transform: translateY(-50%); font-family: 'Inter', sans-serif; opacity: 0.6;" onclick="document.getElementById('about-modal').classList.remove('show')">KAPAT</button>
    </div>
    
    <div style="font-size:0.88rem; color:rgba(255,255,255,0.75); line-height:1.9; margin-bottom:45px; text-align:left; padding: 0 10px;">
       <div style="margin-bottom: 30px; background: rgba(255,255,255,0.02); padding: 25px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
         <strong style="font-family: 'Thunderbolt', sans-serif; color: var(--primary); display:block; margin-bottom:12px; font-size:1.1rem; letter-spacing: 2px; font-weight: normal;">VİZYONUMUZ</strong>
         Gazebo Tactical AI, modern güvenlik ve havacılık ekosistemini yapay zeka ile yeniden şekillendiren ileri teknoloji bir izleme ve analiz platformudur. Geleneksel yöntemlerin yetersiz kaldığı kritik operasyonlarda, otonom kararlar verebilen <span style="color: var(--primary-bright);">"akıllı gözler"</span> inşa ediyoruz.
       </div>
       
       <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
         <div style="background: rgba(196, 0, 29, 0.03); padding: 20px; border-radius: 14px; border: 1px solid rgba(196, 0, 29, 0.1);">
           <strong style="color:#fff; display:block; margin-bottom:8px; font-size:0.9rem; letter-spacing: 1px;">Taktik Güvenlik</strong>
           Sahadaki insan ve silah unsurlarını %99 hassasiyetle anlık olarak tespit ediyor, tehditleri saniyeler içinde analiz ediyoruz.
         </div>
         <div style="background: rgba(0, 240, 255, 0.02); padding: 20px; border-radius: 14px; border: 1px solid rgba(0, 240, 255, 0.1);">
           <strong style="color:#fff; display:block; margin-bottom:8px; font-size:0.9rem; letter-spacing: 1px;">Havacılık Kontrol</strong>
           Ön uçuş kontrollerinde uçak gövdesindeki mikroskobik hasarları yapay zeka ile tarayarak bakım süreçlerini dijitalleştiriyoruz.
         </div>
       </div>

       <p style="text-align: center; font-style: italic; opacity: 0.8; font-size: 0.85rem;">
         "Geleceği bugünden inşa edenlerin yanındayız."
       </p>
    </div>

    <div style="border-top:1px solid rgba(255,255,255,0.05); padding-top:35px; position:relative;">
       <div style="position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:#0a0b0e; padding:0 20px; font-size:0.65rem; letter-spacing:4px; color:var(--text-muted); font-weight:800; text-transform: uppercase;">Mühendislik Ekibi</div>
       <div style="display:flex; justify-content:center; gap:80px; margin-top:10px;">
          <div style="text-align:center;">
             <div style="font-family: 'Thunderbolt', sans-serif; color:#fff; font-size:1.3rem; letter-spacing:2px; font-weight: normal;">Emirhan Yazıcı</div>
             <div style="color:var(--primary); font-size:0.6rem; font-weight:700; letter-spacing:3px; margin-top:8px; text-transform: uppercase;">AI & Yazılım Mimarı</div>
          </div>
          <div style="text-align:center;">
             <div style="font-family: 'Thunderbolt', sans-serif; color:#fff; font-size:1.3rem; letter-spacing:2px; font-weight: normal;">Oğuzhan Yazıcı</div>
             <div style="color:var(--primary); font-size:0.6rem; font-weight:700; letter-spacing:3px; margin-top:8px; text-transform: uppercase;">Sistem Entegrasyonu</div>
          </div>
       </div>
    </div>
  </div>
</div>

<!-- Modal: Guide -->
<div class="modal-bg" id="guide-modal">
  <div class="modal" style="max-width: 850px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 60px rgba(0,0,0,0.5); backdrop-filter: blur(15px);">
    <div class="modal-head" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 20px;">
      <h2 style="color:#fff; font-size:1.3rem; letter-spacing:2px; display:flex; align-items:center; gap:12px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0" />
          <path d="M12 9h.01" />
          <path d="M11 12h1v4h1" />
        </svg> SİSTEM KULLANIM REHBERİ
      </h2>
      <button class="btn-clear" onclick="document.getElementById('guide-modal').classList.remove('show')">KAPAT</button>
    </div>
    <div class="modal-body" style="flex-direction:column; gap:20px;">
      <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
        <div class="stat-card" style="border-left: 4px solid var(--green);">
          <h3 style="color:var(--green); font-size:0.9rem; margin-bottom:10px;">1. CANLI İZLEME</h3>
          <p style="font-size:0.75rem; color:var(--text-muted); line-height:1.6;">
            Sağ taraftaki <b>BAŞLAT</b> butonuyla kamerayı aktif edebilirsiniz. Sistem gerçek zamanlı olarak insanları ve ellerindeki silahları tespit eder. 
            <b>Hassasiyet</b> ayarını değiştirerek sistemin ne kadar "şüpheci" olacağını belirleyebilirsiniz.
          </p>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--primary);">
          <h3 style="color:var(--primary); font-size:0.9rem; margin-bottom:10px;">2. YAPAY ZEKA (FOTO ANALİZ)</h3>
          <p style="font-size:0.75rem; color:var(--text-muted); line-height:1.6;">
            <b>GÜVENLİK</b> modunda fotoğraf yükleyerek insan/silah taraması yapabilir, <b>ÖN UÇUŞ</b> modunda ise uçağın dış yüzeyindeki hasarları (çizik, korozyon, perçin hatası) milimetrik olarak tespit edebilirsiniz.
          </p>
        </div>
        <div class="stat-card" style="border-left: 4px solid var(--yellow);">
          <h3 style="color:var(--yellow); font-size:0.9rem; margin-bottom:10px;">3. PALANTİR (SPIDERFOOT)</h3>
          <p style="font-size:0.75rem; color:var(--text-muted); line-height:1.6;">
            Açık kaynak istihbarat (OSINT) motorudur. Hedef odaklı dijital tarama yapar. <b>Palantir sekmesindeki "YARDIM (?)"</b> butonuna basarak çok daha kapsamlı teknik rehbere ulaşabilirsiniz.
          </p>
        </div>
        <div class="stat-card" style="border-left: 4px solid #888;">
          <h3 style="color:#aaa; font-size:0.9rem; margin-bottom:10px;">4. YAPILANDIRMA</h3>
          <p style="font-size:0.75rem; color:var(--text-muted); line-height:1.6;">
            Kamera seçimini (Kamera İndeksi: 0, 1, 2 vb.), görüntü çözünürlüğünü ve alarm seslerini bu bölümden özelleştirebilirsiniz. Değişiklik sonrası motoru yeniden başlatmanız gerekir.
          </p>
        </div>
      </div>
      <div class="threat-banner" style="animation:none; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.1); color:#aaa; text-align:center; justify-content:center;">
        💡 İPUCU: Herhangi bir alarm durumunda sistem otomatik olarak ekran görüntüsü alır ve "Alarm Geçmişi"ne kaydeder.
      </div>
    </div>
  </div>
</div>

<!-- Modal: Person -->
<div class="modal-bg" id="modal-bg">
  <div class="modal" style="max-width:740px;">
    <div class="modal-head">
      <h2>🔍 Güvenlik Analiz Sonucu</h2>
      <button onclick="document.getElementById('modal-bg').classList.remove('show')" style="background:none;border:none;color:var(--text-muted);font-size:1.5rem;cursor:pointer;">&times;</button>
    </div>
    <div class="modal-body" style="flex-direction:column;">
      <div id="m-threat-banner" class="threat-banner" style="display:none;">
        ⚠️  SİLAHLI KİŞİ TESPIT EDILDI
      </div>
      <img id="modal-img" src="" alt="Analiz" style="width:100%; border-radius:16px; border:1px solid var(--border); margin-top:12px;">
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr); margin-top:16px;">
        <div class="stat-card" style="padding:16px;">
          <div class="stat-label">TOPLAM KİŞİ</div>
          <div class="stat-val"><span id="m-total">–</span></div>
        </div>
        <div class="stat-card" style="padding:16px;">
          <div class="stat-label">SİLAHLI</div>
          <div class="stat-val red"><span id="m-armed">–</span></div>
        </div>
        <div class="stat-card" style="padding:16px;">
          <div class="stat-label">NORMAL</div>
          <div class="stat-val primary"><span id="m-safe">–</span></div>
        </div>
        <div class="stat-card" style="padding:16px;">
          <div class="stat-label">HAYVAN</div>
          <div class="stat-val"><span id="m-animal" style="color:#ffaa00">–</span></div>
        </div>
      </div>
      <div class="detection-list" id="m-det-list"></div>
    </div>
  </div>
</div>

<!-- Modal: Plane -->
<div class="modal-bg" id="plane-modal-bg">
  <div class="modal">
    <div class="modal-head">
      <h2>Ön Uçuş Kontrol Raporu</h2>
      <button onclick="document.getElementById('plane-modal-bg').classList.remove('show')" style="background:none;border:none;color:var(--text-muted);font-size:1.5rem;cursor:pointer;">&times;</button>
    </div>
    <div class="modal-body">
      <div class="modal-img-container">
        <img id="plane-img" src="" alt="Uçak">
      </div>
      <div class="modal-info">
        <div class="score-box" id="p-score-box">
          <div class="score-val">%<span id="p-score">0</span></div>
          <div class="score-label" id="p-verdict">BİLİNMİYOR</div>
          <div style="font-size:0.6rem; letter-spacing:1.5px; font-weight:700; margin-top:6px; opacity:0.7;" id="p-damage-label">HASAR ORANI</div>
        </div>
        <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.7rem; color:var(--text-muted); font-weight:700; padding:10px 0; border-bottom:1px solid var(--border); margin-bottom:10px;">
          <span>BULGU: <span id="p-count" style="color:var(--text-main)">0</span></span>
          <span>TAMİR: <span id="p-hours" style="color:var(--text-main)">0</span>s</span>
        </div>
        <div class="prio-row" id="p-prio-row" style="margin-bottom:12px;"></div>
        <div class="finding-list" id="p-findings"></div>
      </div>
    </div>
  </div>
</div>

<script>
// Tab Switching Logic
const menuItems = document.querySelectorAll('.menu-item[data-tab]');
menuItems.forEach(item => {
  item.addEventListener('click', (e) => {
    e.preventDefault();
    menuItems.forEach(i => i.classList.remove('active'));
    item.classList.add('active');
    
    const targetTab = item.dataset.tab;
    const osintContainer = document.getElementById('osint-container');
    const videoContainer = document.getElementById('main-video-container');
    const sidebarRight = document.getElementById('main-sidebar-right');
    
    if (targetTab === 'tab-osint') {
        if(osintContainer) osintContainer.style.display = 'flex';
        if(videoContainer) videoContainer.style.display = 'none';
        if(sidebarRight) sidebarRight.style.display = 'none';
    } else {
        if(osintContainer) osintContainer.style.display = 'none';
        if(videoContainer) videoContainer.style.display = 'flex';
        if(sidebarRight) sidebarRight.style.display = 'flex';
        
        document.querySelectorAll('.tab-content').forEach(tc => tc.classList.remove('active'));
        const tabContent = document.getElementById(targetTab);
        if(tabContent) tabContent.classList.add('active');
    }
  });
});

// ── Dönen Söz Sistemi ────────────────────────────────────────────────────────
const QUOTES = [
  "İstikbal göklerdedir; oraya ulaşmak için önce kalpte kanat açılmalıdır.",
  "Bir millet göklere hâkim olduğunda, yeryüzünde özgür yaşar.",
  "Gökyüzü bir son değil, yeni bir başlangıcın eşiğidir.",
  "Teknoloji ile rüyalar arasındaki mesafeyi sadece inanç kapatır.",
  "Her uçuş, imkânsızı mümküne dönüştürmek için atılmış bir adımdır.",
  "Uçmak bir özgürlük değil, bir sorumluluktur; vatanı koruma sorumluluğu.",
  "Göklere dokunan ellerin yüreği de zirveleri tanır.",
  "Savunma sanayii bir güç değil, bir onur meselesidir; ve onuru inşa eden kanatlar olur.",
  "Bulutların ötesini görmek için önce onlara doğru yürümek gerekir.",
  "Bir ülke kendi gökyüzünü kendisi koruyabiliyorsa, gerçekten bağımsızdır.",
];

let _quoteIdx = Math.floor(Math.random() * QUOTES.length);
let _quoteTimer = null;

function initQuoteDots() {
  const dotsEl = document.getElementById('quote-dots');
  if (!dotsEl) return;
  dotsEl.innerHTML = QUOTES.map((_, i) =>
    `<div class="quote-dot${i === _quoteIdx ? ' active' : ''}" data-qi="${i}"></div>`
  ).join('');
  dotsEl.querySelectorAll('.quote-dot').forEach(dot => {
    dot.addEventListener('click', e => {
      e.stopPropagation();
      showQuote(parseInt(dot.dataset.qi));
    });
  });
}

function showQuote(idx, instant = false) {
  const textEl   = document.getElementById('quote-text');
  const authorEl = textEl ? textEl.closest('.quote-card')?.querySelector('.quote-author') : null;
  if (!textEl) return;

  const doFade = !instant;

  if (doFade) {
    textEl.classList.add('fading');
    if (authorEl) authorEl.classList.add('fading');
  }

  setTimeout(() => {
    _quoteIdx = ((idx % QUOTES.length) + QUOTES.length) % QUOTES.length;
    textEl.textContent = QUOTES[_quoteIdx];
    if (doFade) {
      textEl.classList.remove('fading');
      if (authorEl) authorEl.classList.remove('fading');
    }
    // Dot güncelle
    document.querySelectorAll('.quote-dot').forEach((d, i) => {
      d.classList.toggle('active', i === _quoteIdx);
    });
  }, doFade ? 380 : 0);
}

function nextQuote() {
  showQuote(_quoteIdx + 1);
  resetQuoteTimer();
}

function resetQuoteTimer() {
  if (_quoteTimer) clearInterval(_quoteTimer);
  _quoteTimer = setInterval(nextQuote, 8000);
}

// Başlat
initQuoteDots();
showQuote(_quoteIdx, true);
resetQuoteTimer();

const quoteCard = document.getElementById('quote-card');
if (quoteCard) quoteCard.addEventListener('click', nextQuote);

let currentStatus = 'offline';
let systemRunning = false;
let paused = false;
const liveImg = document.getElementById('live-img');
const noSig = document.getElementById('no-signal');
const videoPill = document.getElementById('video-pill');
let frameFailCount = 0;

// ── Canlı Görüntü Polling ─────────────────────────────────────────
// Sabit 150ms döngüsü — currentStatus'a bağlı DEĞİL.
// Sistem kapalıyken de dener ama 404 gelirse no-signal gösterir.
let _pollActive = true;

const pollFrame = () => {
  if (!_pollActive) return;
  const url = 'api.php?action=frame&_t=' + Date.now();
  const t = new Image();

  t.onload = () => {
    frameFailCount = 0;
    liveImg.src = url;  // _t=timestamp zaten cache buster — src='' flicker yapar, kullanma
    liveImg.style.display = 'block';
    noSig.style.display = 'none';
    videoPill.style.display = 'flex';
    if (currentStatus === 'offline') currentStatus = 'live';
    delete noSig.dataset.waitMsg;
    setTimeout(pollFrame, 100);  // başarılı frame: ~10fps polling
  };

  t.onerror = () => {
    frameFailCount++;
    // Görüntü gelmediyse no-signal mesajını güncelle
    if (currentStatus === 'live' && frameFailCount > 15) {
      noSig.style.display = 'flex';
      noSig.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:15px; opacity:0.8;">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0" />
          <path d="M12 16h.01" />
        </svg>
        Görüntü bekleniyor...<br><small style="color:var(--text-muted); font-size:0.7rem; margin-top:5px;">Kamera bağlantısı kontrol ediliyor...</small>`;
    } else if ((currentStatus === 'starting') && frameFailCount > 3 && !noSig.dataset.waitMsg) {
      noSig.dataset.waitMsg = '1';
      noSig.style.display = 'flex';
      noSig.innerHTML = `
        <div class="spinner" style="border: 3px solid rgba(255,255,255,0.05); border-top: 3px solid var(--primary); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
        Kamera açılıyor...<br><small style="color:var(--text-muted); font-size:0.7rem; margin-top:5px;">YOLOv8 modeli yükleniyor...</small>`;
    }
    // Her durumda 150ms sonra tekrar dene — offline modda bile
    setTimeout(pollFrame, 150);
  };

  t.src = url;
};
pollFrame();

async function pollStatus(){
  try{
    const d = await(await fetch('api.php?action=status')).json();
    const prevStatus = currentStatus;
    currentStatus = d.status || 'offline';
    systemRunning = (d.status === 'starting' || (!d.paused && d.fps > 0));
    
    // Update Stats
    document.getElementById('s-fps').textContent = d.fps ?? '–';
    document.getElementById('s-person').textContent = d.total_persons ?? 0;
    document.getElementById('s-weapon').textContent = d.total_weapons ?? 0;
    document.getElementById('s-alarm').textContent = d.alert_count ?? 0;
    
    // Update Assets Tab
    document.getElementById('a-total-person').textContent = d.total_persons ?? 0;
    document.getElementById('a-total-weapon').textContent = d.total_weapons ?? 0;
    
    // Update Video Pill
    document.getElementById('pill-person').textContent = d.total_persons ?? 0;
    document.getElementById('pill-weapon').textContent = d.total_weapons ?? 0;

    // Update Badge
    const badge = document.getElementById('status-badge');
    const badgeText = document.getElementById('status-text');
    
    if(d.threat_active){
      badgeText.textContent = 'TEHDİT!';
      badge.className = 'live-badge threat';
    }
    else if(d.status === 'starting'){
      badgeText.textContent = 'BAŞLATILIYOR...';
      badge.className = 'live-badge offline';
      if(liveImg.style.display === 'none' || !liveImg.style.display) {
        noSig.style.display = 'flex';
        if(!noSig.dataset.waitMsg) {
          noSig.innerHTML = '<div class="spinner" style="border: 4px solid rgba(255,255,255,0.1); border-top: 4px solid var(--primary); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 12px;"></div>' + (d.msg || 'Başlatılıyor...');
        }
      }
      const btnStart = document.getElementById('btn-start');
      btnStart.disabled = true;
      btnStart.textContent = 'BAŞLATILIYOR...';
    }
    else if(d.status === 'error'){
      badgeText.textContent = 'HATA';
      badge.className = 'live-badge threat';
      noSig.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--red)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:15px;">
          <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
          <path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0" />
          <path d="M12 16h.01" />
        </svg>
        ${d.error || 'Hata oluştu'}`;
    }
    else if(d.paused){
      badgeText.textContent = 'DURAKLATILDI';
      badge.className = 'live-badge offline';
    }
    else if(d.status === 'live'){
      badgeText.textContent = 'CANLI';
      badge.className = 'live-badge';
      delete noSig.dataset.waitMsg;
      const btnStart = document.getElementById('btn-start');
      if(btnStart.disabled) {
          btnStart.disabled = false;
          btnStart.textContent = 'BAŞLAT';
      }
      // Sayfa açıldığında sistem zaten live ise — hemen frame göster
      if(prevStatus === 'offline' || prevStatus === 'starting') {
        frameFailCount = 0;
        // pollFrame zaten döngüdeyse tekrar başlatma, değilse başlat
        // Bir sonraki döngü otomatik devreye girer (currentStatus artık live)
      }
    }
  }catch(_){}
  setTimeout(pollStatus, 1000);
}
pollStatus();

async function pollAlerts(){
  try{
    const items = await(await fetch('api.php?action=alerts')).json();
    const list = document.getElementById('alert-list');
    if(!items.length){
      list.innerHTML = '<div style="text-align:center; color:var(--text-muted); font-size:0.75rem; padding:20px;">Kayıt yok</div>';
      return;
    }
    list.innerHTML = items.map(a => `
      <div class="alert-card">
        ${a.screenshot ? `<img class="alert-img" src="${a.screenshot}">` : '<div class="alert-img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem">🔫</div>'}
        <div class="alert-info">
          <div class="alert-title">SİLAH: %${Math.round((a.weapon_confidence||0)*100)}</div>
          <div class="alert-time">${a.timestamp||''}</div>
        </div>
        <div class="alert-dl" title="İndir ve Sistemden Sil" onclick="downloadAndDelete('${a.screenshot}', '${a.timestamp}')">
           <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        </div>
      </div>
    `).join('');
  }catch(_){}
  setTimeout(pollAlerts, 4000);
}
pollAlerts();

async function downloadAndDelete(imgUrl, timestamp) {
  if(!imgUrl) return;
  try {
    const response = await fetch(imgUrl);
    const blob = await response.blob();
    const blobUrl = URL.createObjectURL(blob);
    
    // Tarayıcı üzerinden indir
    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = "gazebo_alarm_" + timestamp.replace(/[: ]/g, '_') + ".jpg";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(blobUrl);

    // Sistemden sil (JSON'dan ve sunucudan)
    await fetch('api.php?action=delete_alert', {
      method: 'POST',
      body: JSON.stringify({ image: imgUrl, timestamp: timestamp })
    });
    
    // Listeyi yenile
    pollAlerts();
  } catch (e) {
    alert('İndirme sırasında bir hata oluştu.');
  }
}

document.getElementById('btn-start').onclick = async function() { 
  this.disabled = true;
  this.textContent = 'BAŞLATILIYOR...';
  // noSig state'ini sıfırla — yeni açılış için
  delete noSig.dataset.waitMsg;
  noSig.style.display = 'flex';
  noSig.innerHTML = `
    <div class="spinner" style="border: 3px solid rgba(255,255,255,0.05); border-top: 3px solid var(--primary); border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 20px;"></div>
    Kamera açılıyor...`;
  liveImg.style.display = 'none';
  videoPill.style.display = 'none';
  frameFailCount = 0;
  // Status'u 'starting' olarak işaretle — pollFrame hızlı poll etsin
  currentStatus = 'starting';
  const resp = await fetch('api.php?action=start_engine');
  const data = await resp.json().catch(() => ({}));
  if(data.error) {
    noSig.innerHTML = '<div style="font-size:2rem;">❌</div>' + data.error;
    this.disabled = false;
    this.textContent = 'BAŞLAT';
    return;
  }
  systemRunning = true;
  document.getElementById('btn-pause').textContent = 'DURAKLAT'; 
};
document.getElementById('btn-pause').onclick = async () => { 
  paused = !paused; 
  systemRunning = !paused;
  document.getElementById('btn-pause').textContent = paused ? 'DEVAM ET' : 'DURAKLAT'; 
  await fetch('api.php?action=settings',{method:'POST',body:JSON.stringify({paused})}); 
};
document.getElementById('btn-stop').onclick = async () => {
  const btnStop  = document.getElementById('btn-stop');
  const btnStart = document.getElementById('btn-start');
  btnStop.disabled  = true;
  btnStop.textContent = 'DURDURULUYOR...';

  // Hemen UI'yi kapat
  currentStatus = 'offline';
  systemRunning = false;
  paused = false;
  liveImg.src = '';
  liveImg.style.display = 'none';
  videoPill.style.display = 'none';
  delete noSig.dataset.waitMsg;
  noSig.style.display = 'flex';
  noSig.innerHTML = `
    <img src="assets/gazebo.png" style="width:160px; height:auto; opacity:0.3; margin-bottom:20px; filter:grayscale(1);">
    <div style="font-size:0.85rem; color:var(--text-muted); letter-spacing:1px;">Sistem başlatılıyor veya kapalı...</div>`;
  document.getElementById('status-badge').className = 'live-badge offline';
  document.getElementById('status-text').textContent = 'DURDURULUYOR...';
  document.getElementById('btn-pause').textContent = 'DURAKLAT';
  frameFailCount = 0;

  // PHP'den durdurma yanıtını bekle (kamera serbest bırakılana kadar)
  try {
    await fetch('api.php?action=stop_engine');
  } catch(_) {}

  document.getElementById('status-text').textContent = '';
  btnStop.disabled = false;
  btnStop.textContent = 'DURDUR';
  btnStart.disabled = false;
  btnStart.textContent = 'BAŞLAT';
};

// Configuration Save Logic
document.getElementById('btn-save-config').onclick = async () => {
  const settings = {
    camera_index: parseInt(document.getElementById('cfg-cam-index').value),
    resolution: document.getElementById('cfg-resolution').value,
    alert_sound: document.getElementById('cfg-alert-sound').checked,
    alert_screenshot: document.getElementById('cfg-alert-screenshot').checked
  };
  
  await fetch('api.php?action=settings', {method:'POST', body:JSON.stringify(settings)});
  alert('Ayarlar kaydedildi. Değişikliklerin uygulanması için motoru durdurup tekrar başlatın.');
};
document.getElementById('btn-clear').onclick = async () => { 
  await fetch('api.php?action=clear_alerts'); 
  pollAlerts(); 
};

document.getElementById('conf-slider').addEventListener('input', e => {
  document.getElementById('conf-val').textContent = '%' + e.target.value;
});
document.getElementById('conf-slider').addEventListener('change', async e => {
  await fetch('api.php?action=settings', { method:'POST', body:JSON.stringify({weapon_conf: parseInt(e.target.value)/100}) });
});

const zone = document.getElementById('upload-zone'), fileInput = document.getElementById('file-input'), statusEl = document.getElementById('upload-status');
zone.onclick = () => fileInput.click();
fileInput.onchange = e => handleFile(e.target.files[0]);

async function handleFile(file){
  if(!file) return;
  const type = document.querySelector('input[name="atype"]:checked').value;
  const action = type === 'plane' ? 'upload_plane' : 'upload';
  
  statusEl.textContent = '⏳ Analiz ediliyor...'; 
  statusEl.style.color = 'var(--primary)';
  
  const fd = new FormData(); 
  fd.append('file', file);
  
  try {
    const data = await(await fetch('api.php?action=' + action, {method:'POST', body:fd})).json();
    if(data.error){
      statusEl.textContent = '❌ ' + data.error; 
      statusEl.style.color = 'var(--red)'; 
      return;
    }
    if(type === 'plane'){
      statusEl.textContent = '✅ Rapor hazır'; 
      statusEl.style.color = 'var(--green)';
      showPlane(data);
    } else {
      statusEl.textContent = `✅ ${data.total_persons} kişi, ${data.armed} silahlı`; 
      statusEl.style.color = 'var(--green)';
      showPerson(data);
    }
  } catch(_) {
    statusEl.textContent = '❌ Hata oluştu'; 
    statusEl.style.color = 'var(--red)';
  }
  fileInput.value = '';
}

function showPerson(d){
  const armed = d.armed ?? 0;
  document.getElementById('modal-img').src = d.image || '';
  document.getElementById('m-total').textContent  = d.total_persons ?? '0';
  document.getElementById('m-armed').textContent  = armed;
  document.getElementById('m-safe').textContent   = d.unarmed ?? '0';
  document.getElementById('m-animal').textContent = d.animals ?? '0';

  // Tehdit banner
  const banner = document.getElementById('m-threat-banner');
  banner.style.display = armed > 0 ? 'flex' : 'none';

  // Deteksiyon listesi
  const detList = document.getElementById('m-det-list');
  if(d.detections && d.detections.length > 0){
    detList.innerHTML = d.detections.map(det => {
      const pct = Math.round((det.confidence||0)*100);
      const icon = det.has_weapon ? '🔴' : (det.class_name==='Insan'||det.class_name==='Person') ? '👤' : '🐾';
      const barColor = det.has_weapon ? '#ff2a44' : '#00e676';
      const label = det.has_weapon
        ? `${det.class_name} <span style="color:var(--red);font-size:0.65rem;">⚠️ SİLAHLI</span>`
        : det.class_name;
      return `<div class="det-item">
        <span class="det-icon">${icon}</span>
        <span class="det-name">${label}</span>
        <div class="det-conf-bar"><div class="det-conf-fill" style="width:${pct}%;background:${barColor};"></div></div>
        <span class="det-conf-val">${pct}%</span>
      </div>`;
    }).join('');
  } else {
    detList.innerHTML = '<div style="color:var(--text-muted);font-size:0.75rem;text-align:center;padding:12px;">Nesne tespit edilmedi.</div>';
  }
  document.getElementById('modal-bg').classList.add('show');
}

function showPlane(d){
  document.getElementById('plane-img').src  = d.image || '';
  const dmg = d.damage_pct ?? d.takeoff_score ?? 0;
  document.getElementById('p-score').textContent   = dmg;
  document.getElementById('p-verdict').textContent = d.verdict ?? 'BİLİNMİYOR';
  document.getElementById('p-count').textContent   = d.findings_count ?? '0';
  document.getElementById('p-hours').textContent   = d.total_repair_hours ?? '0';

  // Hasar % → renk sınıfı (5 seviye)
  const box = document.getElementById('p-score-box');
  box.className = 'score-box';
  if      (dmg >= 90) box.classList.add('score-nogo');        // KRİTİK HASAR — kırmızı
  else if (dmg >= 60) box.classList.add('score-nogo');        // AĞIR HASAR   — kırmızı
  else if (dmg >= 35) box.classList.add('score-conditional'); // ORTA HASAR   — sarı
  else if (dmg >= 15) box.classList.add('score-limited');     // HAFİF HASAR  — mavi
  else                box.classList.add('score-go');          // MİNİMAL HASAR— yeşil

  // Priority chip'leri
  const prioRow = document.getElementById('p-prio-row');
  const prioData = [
    { key:'critical_count', label:'KRİTİK',  cls:'kritik',  icon:'🔴' },
    { key:'high_count',     label:'YÜKSEK',  cls:'yüksek', icon:'🟠' },
    { key:'medium_count',   label:'ORTA',    cls:'orta',   icon:'🟡' },
    { key:'low_count',      label:'DÜŞÜK',   cls:'düşük',  icon:'🟢' },
  ];
  prioRow.innerHTML = prioData.map(p => {
    const cnt = d[p.key] ?? 0;
    const dots = { kritik: '●', yüksek: '●', orta: '●', düşük: '●' };
    return `<div class="prio-chip prio-chip-${p.cls}"><span style="font-size:0.55rem;">${dots[p.cls]||'●'}</span> ${cnt} ${p.label}</div>`;
  }).join('');

  // Bulgular
  const prioBarColors = { kritik:'#ff2a44', yüksek:'#ffaa00', orta:'#00f0ff', düşük:'#00e676' };
  const flist = document.getElementById('p-findings');
  if(d.findings && d.findings.length > 0){
    flist.innerHTML = d.findings.map((f, idx) => {
      const barPct   = Math.min(100, f.severity || 0);
      const barColor = prioBarColors[f.priority] || '#888';
      const badgeCls = `f-badge f-badge-${f.priority}`;
      const pLabel   = f.priority.toUpperCase();
      const zoneLabel = (f.zone || '').toUpperCase().replace(/_/g, ' ');
      const boxNum   = f.box_index ?? (idx + 1);
      const hours    = f.repair_time || 0;
      const hoursStr = hours >= 10 ? `${hours} SAAT` : `${hours}s`;
      return `<div class="finding-item finding-${f.priority}">
        <div class="f-header">
          <span class="f-title"><span style="color:var(--text-muted);font-weight:900;font-size:0.65rem;margin-right:5px;">#${boxNum}</span>${f.issue}</span>
          <span class="${badgeCls}">${pLabel}</span>
        </div>
        <div style="font-size:0.65rem;color:var(--text-muted);font-weight:600;margin-bottom:5px;letter-spacing:0.5px;">${f.category}</div>
        <div class="f-action">${f.action}</div>
        <div class="f-meta">
          <span class="f-zone">${zoneLabel}</span>
          <div class="f-bar-wrap"><div class="f-bar" style="width:${barPct}%;background:${barColor};"></div></div>
          <span style="white-space:nowrap;">${hoursStr}</span>
        </div>
      </div>`;
    }).join('');
  } else {
    flist.innerHTML = '<div style="color:var(--green);text-align:center;padding:20px;font-weight:700;">Kusursuz. Yapısal sorun yok.</div>';
  }
  document.getElementById('plane-modal-bg').classList.add('show');
}

// ── Particle canvas ─────────────────────────────────────────────────────────
(function(){
  const cvs = document.getElementById('particle-canvas');
  if(!cvs) return;
  const ctx = cvs.getContext('2d');
  const resize = () => { cvs.width = window.innerWidth; cvs.height = window.innerHeight; };
  resize();
  window.addEventListener('resize', resize);
  const pts = Array.from({length:80}, () => ({
    x: Math.random()*window.innerWidth,
    y: Math.random()*window.innerHeight,
    r: Math.random()*1.2+0.3,
    vx:(Math.random()-0.5)*0.3,
    vy:(Math.random()-0.5)*0.3,
    a: Math.random()
  }));
  function drawParts(){
    ctx.clearRect(0,0,cvs.width,cvs.height);
    pts.forEach(p=>{
      p.x+=p.vx; p.y+=p.vy; p.a+=0.005;
      if(p.x<0||p.x>cvs.width)  p.vx*=-1;
      if(p.y<0||p.y>cvs.height) p.vy*=-1;
      ctx.beginPath();
      ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
      ctx.fillStyle=`rgba(196,0,29,${(Math.sin(p.a)*0.3+0.4).toFixed(2)})`;
      ctx.fill();
    });
    requestAnimationFrame(drawParts);
  }
  drawParts();
})();

// ── 3D STARTUP ───────────────────────────────────────────────────────────────
const startupOverlay = document.getElementById('startup-overlay');
const loadingBar     = document.getElementById('loading-bar');
const tapHint        = document.getElementById('tap-hint');
let   overlayReady   = false;

// Herhangi bir yere tıklayınca blur ile kapat
function closeOverlay() {
  if(!overlayReady) return;
  startupOverlay.classList.add('closing');
  setTimeout(() => { startupOverlay.classList.add('hidden'); }, 950);
}
startupOverlay.addEventListener('click', closeOverlay);

const scene    = new THREE.Scene();
const threeW   = window.innerWidth;
const threeH   = window.innerHeight;
const camera   = new THREE.PerspectiveCamera(40, threeW / threeH, 0.1, 2000);
const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
renderer.setSize(threeW, threeH);
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

// ✅ PBR FIX: renderer encoding + tone mapping (beyaz/pembe sorunu çözümü)
if(THREE.sRGBEncoding !== undefined) renderer.outputEncoding = THREE.sRGBEncoding;
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 1.6;
if(renderer.physicallyCorrectLights !== undefined) renderer.physicallyCorrectLights = true;

document.getElementById('three-container').appendChild(renderer.domElement);

// ── Işıklandırma — dramatik sinematik ────────────────────────────────────
const ambientLight = new THREE.AmbientLight(0xffffff, 0.3);
scene.add(ambientLight);

const hemiLight = new THREE.HemisphereLight(0x334466, 0x111111, 0.5);
scene.add(hemiLight);

// Ana ışık — sol üst önden (soldan sağa kalkışta kaynağı aydınlatır)
const keyLight = new THREE.DirectionalLight(0xffffff, 2.5);
keyLight.position.set(-15, 20, 10);
scene.add(keyLight);

// Dolgu ışığı — sağdan yumusat
const fillLight = new THREE.DirectionalLight(0x6699ff, 0.8);
fillLight.position.set(12, 5, 8);
scene.add(fillLight);

// Rim ışığı — arkaplan ayrımı
const rimLight = new THREE.DirectionalLight(0xffffff, 1.4);
rimLight.position.set(0, 8, -18);
scene.add(rimLight);

// Kırmızı aksent — alt söl köşe
const redAccent = new THREE.PointLight(0xc4001d, 2.5, 25);
redAccent.position.set(-6, -2, 5);
scene.add(redAccent);

// İkinci kırmızı — egzoz hissi
const engineGlow = new THREE.PointLight(0xff4400, 1.8, 15);
engineGlow.position.set(5, 0, -3);
scene.add(engineGlow);

// Grid yok — kaldırıldı

// Kamera başlangıç
camera.position.set(0, 1.5, 9);
camera.lookAt(0, 0, 0);

// OrbitControls: autoRotate açık — kendi etrafında döner
const controls = new THREE.OrbitControls(camera, renderer.domElement);
controls.enableDamping   = true;
controls.dampingFactor   = 0.05;
controls.enableZoom      = false;
controls.enableRotate    = false;
controls.enablePan       = false;
controls.autoRotate      = true;
controls.autoRotateSpeed = 0.8;

// ── F-16 Model Yükleme ──────────────────────────────────────────────────────
let jet;
const loader = new THREE.GLTFLoader();
loader.load(
  'assets/models/f16.glb',
  (gltf) => {
    jet = gltf.scene;

    jet.traverse((child) => {
      if(child.isMesh){
        if(!(child.material && child.material.map)){
          child.material = new THREE.MeshStandardMaterial({
            color: 0x3a3f4a, metalness: 0.80, roughness: 0.25, envMapIntensity: 1.2,
          });
        }
        child.material.needsUpdate = true;
        child.castShadow = true;
      }
    });

    // ── Uçağı tüm ekranı dolduracak şekilde büyüt ─────────────────────────
    const box  = new THREE.Box3().setFromObject(jet);
    const size = new THREE.Vector3();
    box.getSize(size);
    const maxDim = Math.max(size.x, size.y, size.z);
    const scale  = 7.0 / maxDim;  // Ekranda büyük, ama tam görünür
    jet.scale.setScalar(scale);

    const center = new THREE.Vector3();
    box.getCenter(center).multiplyScalar(scale);
    jet.position.set(-center.x, -center.y, -center.z);  // Sahne ortası
    jet.userData.originalPos = { x: jet.position.x, y: jet.position.y, z: jet.position.z };
    scene.add(jet);

    // Kamerayı modele göre ayarla
    camera.position.set(0, 1.5, 9);
    camera.lookAt(0, 0, 0);

    // Model hazır — yükleme çubuğunu doldur ve "tıkla" ipucunu göster
    loadingBar.style.width = '100%';
    setTimeout(() => {
      tapHint.style.display = 'block';
      overlayReady = true;
    }, 500);
  },
  (xhr) => {
    if(xhr.total > 0) loadingBar.style.width = ((xhr.loaded / xhr.total) * 100) + '%';
  },
  (err) => {
    console.warn('Model yüklenemedi:', err);
    // Hata durumunda da tıklanabilir yap
    loadingBar.style.background = 'var(--red)';
    tapHint.style.display = 'block';
    overlayReady = true;
  }
);

// Güvenlik timeout — 8s içinde model gelmezse yine tıklanabilir
setTimeout(() => {
  if(!overlayReady){
    tapHint.style.display = 'block';
    overlayReady = true;
  }
}, 8000);

// ── ANIMATE — sadece hover float, kalkış animasyonu yok ──────────────────
function animate(){
  requestAnimationFrame(animate);
  if(controls) controls.update();

  const t = Date.now() * 0.001;

  if(jet){
    // Yavaş yüzer nefes animasyonu
    jet.position.y = jet.userData.originalPos.y + Math.sin(t * 0.7) * 0.06;
    // autoRotate dönüşü hallediyor, manuel yaw yok

    // Işık nefesi
    engineGlow.intensity = 1.6 + Math.sin(t * 2.5) * 0.5;
    redAccent.intensity  = 2.2 + Math.sin(t * 1.8) * 0.6;
  }

  renderer.render(scene, camera);
}
animate();

// Renderer tam ekran resize
window.addEventListener('resize', () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
});
</script>
</body>
</html>

