<?php
// ================================================================
// login.php — Halaman Login
// Kredensial: TA-mikroalga / udinus2022
// ================================================================
session_start();

// Kalau sudah login, redirect ke dashboard
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Hardcoded single admin account
    if ($username === 'TA-mikroalga' && $password === 'udinus2022') {
        $_SESSION['login']    = true;
        $_SESSION['username'] = 'Slamet';
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Monitoring Mikroalga Spirulina</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
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
    --red        : #d94141;
}
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-mid) 50%, #1a7a5c 100%);
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
}
.login-wrap {
    width: 100%; max-width: 400px;
    display: flex; flex-direction: column; align-items: center; gap: 24px;
}
.login-logo {
    text-align: center; color: #fff;
}
.login-logo .icon {
    font-size: 3rem; margin-bottom: 8px; display: block;
}
.login-logo h1 {
    font-size: 1.4rem; font-weight: 800; margin-bottom: 4px;
}
.login-logo p {
    font-size: 0.75rem; opacity: 0.75; font-weight: 500; letter-spacing: 0.5px;
}
.login-card {
    background: var(--white);
    border-radius: 20px;
    padding: 32px 28px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.login-card h2 {
    font-size: 1.1rem; font-weight: 800; color: var(--text);
    margin-bottom: 6px;
}
.login-card p {
    font-size: 0.75rem; color: var(--text-soft); font-weight: 500;
    margin-bottom: 24px;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block; font-size: 0.72rem; font-weight: 700;
    color: var(--text-soft); text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 6px;
}
.input-wrap {
    position: relative;
}
.input-wrap i {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: var(--text-soft); font-size: 0.85rem;
}
.input-wrap input {
    width: 100%; padding: 11px 12px 11px 36px;
    border: 1.5px solid var(--border); border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.85rem; font-weight: 500; color: var(--text);
    outline: none; transition: border-color 0.15s;
    background: var(--bg);
}
.input-wrap input:focus {
    border-color: var(--green-light);
    background: var(--white);
}
.toggle-pw {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: var(--text-soft);
    cursor: pointer; font-size: 0.85rem; padding: 0;
}
.error-msg {
    background: #fde8e8; border: 1px solid #f5c6c6;
    color: var(--red); border-radius: 8px;
    padding: 10px 14px; font-size: 0.75rem; font-weight: 600;
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 16px;
}
.btn-login {
    width: 100%; padding: 13px;
    background: var(--green-dark); color: #fff;
    border: none; border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 0.88rem; font-weight: 800; cursor: pointer;
    transition: background 0.15s; margin-top: 8px;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.btn-login:hover { background: var(--green-mid); }
.login-footer {
    color: rgba(255,255,255,0.6); font-size: 0.68rem;
    font-weight: 500; text-align: center;
}
</style>
</head>
<body>
<div class="login-wrap">
    <div class="login-logo">
        <span class="icon">🌿</span>
        <h1>MikroAlga Monitor</h1>
        <p>SISTEM MONITORING KOLAM SPIRULINA SP.</p>
    </div>

    <div class="login-card">
        <h2>Selamat Datang</h2>
        <p>Masuk untuk mengakses dashboard monitoring</p>

        <?php if ($error): ?>
        <div class="error-msg">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="off">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           placeholder="Masukkan username" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" id="pwInput"
                           placeholder="Masukkan password" required>
                    <button type="button" class="toggle-pw" onclick="togglePw()">
                        <i class="fas fa-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>
    </div>

    <div class="login-footer">
        Universitas Dian Nuswantoro &nbsp;•&nbsp; Tugas Akhir 2026
    </div>
</div>
<script>
function togglePw() {
    const inp  = document.getElementById('pwInput');
    const icon = document.getElementById('pwIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
</body>
</html>
