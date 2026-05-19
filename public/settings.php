<?php

require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Core\Security;
use Modules\Settings\SettingsController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$controller = new SettingsController();

$action = $_GET['action'] ?? 'index';

switch ($action) {

    case 'update':
        Security::validatePost();
        $controller->update();
        break;

    case 'test_transbank':
        $controller->test_transbank();
        break;

    case 'test_email':
        $controller->test_email();
        break;

    default:
        $controller->index();
        break;
}