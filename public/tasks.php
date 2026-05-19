<?php
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Core\Security;
use Modules\Tasks\TaskController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

Security::validatePost();

$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

$controller = new TaskController();

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'edit':
        $controller->edit($id);
        break;
    case 'delete':
        $controller->delete();
        break;
    default:
        $controller->index();
        break;
}
