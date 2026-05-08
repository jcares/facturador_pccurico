<?php
require_once '../bootstrap/app.php';

$user = \Core\Auth::user();
$settings = \Core\Config::getAll();

$title = 'Configuración del Producto';
$contentFile = 'settings/product.php';

include '../app/views/layout.php';
?>