<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Modules\Settings\SettingsController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

\Core\Security::validatePost();
$_POST = \Core\Security::sanitizeInput($_POST);

$controller = new SettingsController();
$action = $_GET['action'] ?? 'index';

if ($action === 'update') {
    $controller->update();
} else {
    $controller->index();
}
