<?php
namespace Core;

class Security
{
    public static function generateCsrfToken()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function checkCsrfToken($token)
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField()
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function validatePost()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::checkCsrfToken($_POST['csrf_token'] ?? '')) {
                Logger::error("Fallo de validación CSRF detectado.");
                http_response_code(403);
                echo "Petición inválida. Error de seguridad CSRF.";
                exit;
            }
        }
    }

    public static function sanitizeInput($data)
    {
        if ($data === null) {
            return null;
        }

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitizeInput($value);
            }
        } else {
            $data = htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
}
