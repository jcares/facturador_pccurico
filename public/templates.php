<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

$controller = new \Modules\Templates\TemplateController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $controller->index();
        break;
    case 'visual_edit':
        $controller->visualEdit();
        break;
    case 'save_visual':
        $controller->saveVisual();
        break;
    case 'preview_visual':
        $controller->previewVisual();
        break;
    default:
        $controller->index();
        break;
}
