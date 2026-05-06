<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

if (!\Core\Auth::check()) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['test'])) {
    $_SESSION['test'] = time();
    echo "Session initialized. Reload to verify.";
} else {
    echo "Session persisted: " . htmlspecialchars((string)$_SESSION['test'], ENT_QUOTES, 'UTF-8');
}
