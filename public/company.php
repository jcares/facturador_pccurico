<?php
require_once '../bootstrap/app.php';

$user = \Core\Auth::user();
$settings = \Core\Config::getAll();

$title = 'Detalles de la Empresa';
$contentFile = 'settings/company.php';

include '../app/views/layout.php';
?>