<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Modules\Clients\ClientController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

// Global Security for POST requests
\Core\Security::validatePost();
$_POST = \Core\Security::sanitizeInput($_POST);

$controller = new ClientController();
$action = $_GET['action'] ?? 'index';

if ($action === 'store') {
    $controller->store();
} else {
    $controller->index();
}
