<?php
// ================================================================
// auth_check.php — Guard session
// Include di awal setiap halaman yang perlu proteksi login.
// Usage: require_once 'auth_check.php';
// ================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: login.php');
    exit;
}
?>
