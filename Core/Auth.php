<?php

namespace Core;

class Auth
{
    public static function login($email, $password)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            return true;
        }

        return false;
    }

    public static function requireLogin()
    {
        if (!self::check()) {
            header('Location: login.php');
            exit;
        }
    }

    public static function check()
    {
        return isset($_SESSION['user_id']);
    }

    public static function logout()
    {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    public static function user()
    {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null
        ];
    }
}
