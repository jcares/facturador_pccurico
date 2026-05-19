<?php
require_once __DIR__ . '/../bootstrap/app.php';

if (!\Core\Auth::check()) {
    header('Location: login.php');
    exit;
}

$title = 'Portal de Cliente';
$contentFile = 'placeholders/client_portal.php';

include __DIR__ . '/../app/views/settings_layout.php';