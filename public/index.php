<?php
/**
 * FACTURADOR-PCCURICO
 * Entry Point
 */

session_start();

define('APP_START', microtime(true));
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Bootstrap
require_once ROOT_PATH . '/bootstrap/app.php';

use Core\Auth;
use Core\Database;
use Core\Router;
use Core\View;

$router = new Router();
require ROOT_PATH . '/routes/web.php';

$page = $_GET['page'] ?? 'dashboard';

try {
    $router->dispatch($page, $_SERVER['REQUEST_METHOD']);
} catch (\Throwable $e) {
    if ($e->getCode() === 404) {
        http_response_code(404);
        echo '<h1>404 - Página no encontrada</h1>';
        echo '<p>La ruta solicitada no está disponible.</p>';
    } else {
        throw $e;
    }
}
