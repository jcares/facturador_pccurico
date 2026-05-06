<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Modules\Invoices\InvoiceController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

\Core\Security::validatePost();
// Skip sanitize for invoice items array to avoid messing up structural data easily, but sanitize simple values.
$_POST['client_id'] = \Core\Security::sanitizeInput($_POST['client_id'] ?? null);
$_POST['issue_date'] = \Core\Security::sanitizeInput($_POST['issue_date'] ?? null);
$_POST['due_date'] = \Core\Security::sanitizeInput($_POST['due_date'] ?? null);

$controller = new InvoiceController();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'store':
        $controller->store();
        break;
    case 'print':
        $controller->print();
        break;
    default:
        $controller->index();
        break;
}
