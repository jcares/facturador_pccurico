<?php
require_once '../bootstrap/app.php';

$user = \Core\Auth::user();
$settings = \Core\Config::getAll();

$title = 'Localización';
$contentFile = 'settings/localization.php';

include '../app/views/layout.php';
?>