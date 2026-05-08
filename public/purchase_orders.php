<?php
require_once '../bootstrap/app.php';

use Core\Auth;
use Core\Router;
use Modules\PurchaseOrders\PurchaseOrderController;

// Verificar autenticación
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

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