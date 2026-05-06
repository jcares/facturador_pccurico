<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Modules\Reports\ReportController;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$controller = new ReportController();
$controller->index();
