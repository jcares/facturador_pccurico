<?php
require_once '../bootstrap/app.php';

use Core\Auth;
use Core\Mailer;
use Core\Security;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$user = Auth::user();
$settings = \Core\Config::getAll();
$testMessage = null;
$testError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test_email') {
    Security::validatePost();
    $testTo = filter_var($_POST['test_email_to'] ?? '', FILTER_VALIDATE_EMAIL);
    
    if ($testTo) {
        $subject = "Prueba de configuración de correo - FACTURADOR-PCCURICO";
        $body = "<h3>Prueba Exitosa</h3><p>Hola,</p><p>Este es un mensaje de prueba generado desde el sistema para verificar que la configuración de correo SMTP está funcionando correctamente.</p>";
        
        $publicBaseUrl = rtrim($settings['public_base_url'] ?? 'https://pccurico.cl', '/');
        $demoUrl = $publicBaseUrl . "/view.php?token=demo";
        
        if (($settings['email_include_webpay_button'] ?? '1') === '1') {
            $buttonText = htmlspecialchars($settings['webpay_button_text'] ?? 'Pagar con Webpay Plus');
            $buttonImage = !empty($settings['webpay_button_image'])
                ? $publicBaseUrl . '/uploads/' . htmlspecialchars($settings['webpay_button_image'])
                : $publicBaseUrl . '/assets/img/transbank-webpay.svg';

            $body .= '
                <div style="margin: 24px 0; padding: 20px; border: 1px solid #f3c2d2; background: #fff7fb; border-radius: 10px; text-align: center; max-width: 500px;">
                    <img src="' . $buttonImage . '" alt="Transbank Webpay Plus" style="max-height: 50px; background: #fff; border-radius: 8px; padding: 4px 10px; margin-bottom: 12px;">
                    <br>
                    <a href="' . $demoUrl . '" style="display: inline-block; background: #e0245e; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 12px 24px; font-weight: 800; font-family: sans-serif;">
                        ' . $buttonText . '
                    </a>
                </div>
            ';
        }

        $body .= "<p>Si puedes ver este mensaje y el botón superior (si está habilitado), tu configuración es correcta.</p><p>Saludos,<br>FACTURADOR-PCCURICO</p>";
        
        try {
            $result = Mailer::sendReminder($testTo, $subject, $body);
            if ($result) {
                $testMessage = "Correo de prueba enviado exitosamente a $testTo";
            } else {
                $testError = "No se pudo enviar el correo de prueba. Verifica la configuración.";
            }
        } catch (\Exception $e) {
            $testError = "Error al enviar el correo: " . $e->getMessage();
        }
    } else {
        $testError = "Dirección de correo de prueba inválida.";
    }
}

$title = 'Configuración del Correo Electrónico';
$contentFile = 'settings/email.php';

include '../app/views/layout.php';