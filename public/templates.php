<?php
require_once __DIR__ . '/../bootstrap/app.php';

if (!\Core\Auth::check()) {
    header('Location: login.php');
    exit;
}

\Core\Security::validatePost();

$controller = new \Modules\Templates\TemplateController();

$action = $_GET['action'] ?? 'index';

// Only index action uses the settings layout; visual_edit and others use raw layout
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