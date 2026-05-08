<?php
/**
 * Bootstrap the application
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si el sistema no está instalado, redirigir al wizard (excepto si ya estamos en install.php)
$currentScript = basename($_SERVER['SCRIPT_FILENAME'] ?? '');
if ($currentScript !== 'install.php' && !file_exists(ROOT_PATH . '/storage/installed.lock')) {
    header('Location: install.php');
    exit;
}

require_once __DIR__ . '/../Core/Config.php';
require_once __DIR__ . '/../Core/Logger.php';
require_once __DIR__ . '/../Core/ErrorHandler.php';
require_once __DIR__ . '/../Core/Security.php';

// Inicializar Manejo de Errores
\Core\Logger::init();
\Core\ErrorHandler::register();

// Autoload PSR-4 simple
spl_autoload_register(function ($class) {
    $file = ROOT_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Cargar configuración
$appConfig = [];

if (file_exists(__DIR__ . '/../.env')) {
    $envContents = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envContents as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2) + [null, null]);
        if ($key !== null && $value !== null) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

if (file_exists(__DIR__ . '/../config/app.php')) {
    $appConfig = require __DIR__ . '/../config/app.php';
}
if (file_exists(__DIR__ . '/../config/database.php')) {
    $dbConfig = require __DIR__ . '/../config/database.php';
    $appConfig['database'] = $dbConfig;
}
\Core\Config::load($appConfig);

// Entorno y depuración
if (!defined('APP_ENV')) {
    define('APP_ENV', \Core\Config::get('env', 'production'));
}
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', \Core\Config::get('debug', false));
}

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
}

$timezone = \Core\Config::get('timezone', 'America/Santiago');
if ($timezone) {
    date_default_timezone_set($timezone);
}

// Seguridad de sesión
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    if (!empty($_SERVER['HTTPS']) || ($_SERVER['SERVER_PORT'] ?? '') === '443') {
        ini_set('session.cookie_secure', '1');
    }
}

// Inicializar utilidades globales aquí
