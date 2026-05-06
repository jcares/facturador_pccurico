<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;

if (Auth::check()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    \Core\Security::validatePost();
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password'] ?? '');

    if ($email && $password && Auth::login($email, $password)) {
        header('Location: index.php');
        exit;
    }

    $error = "Credenciales inválidas.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | FACTURADOR-PCCURICO</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="wizard-container">
        <div class="glass-card">
            <h1>Bienvenido</h1>
            <p class="subtitle">Ingresa a tu facturador</p>

            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; padding: 15px; border-radius: 12px; margin-bottom: 20px; color: #fca5a5; font-size: 0.9rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <?php echo \Core\Security::csrfField(); ?>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="admin@pccurico.cl">
                </div>
                <div class="form-group">
                    <label>Contraseña</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn-primary">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>
