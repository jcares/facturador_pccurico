<?php
namespace Core;

class ErrorHandler
{
    public static function register()
    {
        error_reporting(E_ALL);
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    public static function handleError($level, $message, $file = '', $line = 0)
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    public static function handleException(\Throwable $exception)
    {
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }
        
        http_response_code($code);

        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();

        Logger::error("Uncaught Exception: {$message}", ['file' => $file, 'line' => $line]);

        // Mostrar error generico en produccion, pero detallado en desarrollo
        // Para cPanel, preferimos no mostrar la ruta exacta
        if (php_sapi_name() === 'cli') {
            echo "Error: {$message}\n";
        } else {
            echo "<div style='padding: 20px; background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; border-radius: 8px; font-family: sans-serif;'>";
            echo "<h3 style='margin-top:0;'>Ha ocurrido un error inesperado</h3>";
            echo "<p>El problema ha sido registrado. Por favor, contacte al administrador si el error persiste.</p>";
            if (Config::get('app.debug', true)) {
                echo "<p><strong>Detalle:</strong> {$message}</p>";
            }
            echo "</div>";
        }
    }

    public static function handleFatalError()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::handleException(new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }
}
