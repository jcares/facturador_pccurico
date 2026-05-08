<?php
require_once '../bootstrap/app.php';

$user = \Core\Auth::user();
$settings = \Core\Config::getAll();

$title = 'Configuración de Impuestos';
$contentFile = 'settings/taxes.php';

include '../app/views/layout.php';
?>