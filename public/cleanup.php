<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

if (!\Core\Auth::check()) {
    header('Location: login.php');
    exit;
}

header('Location: tools.php?action=cleanup');
exit;
