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

    public static function validateCsrfToken($token)
    {
        return self::checkCsrfToken($token);
    }

    public static function csrfField()
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
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

    public static function cleanString($value, $maxLength = null)
    {
        $value = trim((string)$value);
        $value = strip_tags($value);

        if ($maxLength !== null) {
            $value = mb_substr($value, 0, (int)$maxLength);
        }

        return $value;
    }

    public static function cleanEmail($value)
    {
        $value = trim((string)$value);
        return filter_var($value, FILTER_VALIDATE_EMAIL) ?: null;
    }

    public static function cleanDecimal($value, $default = 0)
    {
        return is_numeric($value) ? (float)$value : (float)$default;
    }

    public static function cleanInt($value, $default = 0)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : (int)$default;
    }
}
