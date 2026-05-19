<?php
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Core\Security;
use Modules\PurchaseOrders\PurchaseOrderController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

Security::validatePost();

$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

$controller = new PurchaseOrderController();

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'edit':
        $controller->edit($id);
        break;
    case 'show':
        $controller->show($id);
        break;
    case 'delete':
        $controller->delete();
        break;
    default:
        $controller->index();
        break;
}
