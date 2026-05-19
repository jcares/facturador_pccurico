<?php
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Modules\Products\ProductController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

\Core\Security::validatePost();
$_POST = \Core\Security::sanitizeInput($_POST);

$controller = new ProductController();
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
