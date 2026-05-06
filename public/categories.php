<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Modules\Categories\CategoryController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

\Core\Security::validatePost();
$_POST = \Core\Security::sanitizeInput($_POST);

$controller = new CategoryController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'store':
        $controller->store();
        break;
    case 'update':
        $controller->update();
        break;
    case 'delete':
        $controller->delete();
        break;
    default:
        $controller->index();
        break;
}