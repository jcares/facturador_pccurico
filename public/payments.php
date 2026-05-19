<?php
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Modules\Payments\PaymentController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

\Core\Security::validatePost();
$_POST = \Core\Security::sanitizeInput($_POST);

$controller = new PaymentController();
$action = $_GET['action'] ?? 'index';

if ($action === 'index' && isset($_GET['invoice_id'])) {
    $action = 'create';
}

if ($action === 'store') {
    $controller->store();
} elseif ($action === 'create') {
    $controller->create();
} else {
    $controller->index();
}
