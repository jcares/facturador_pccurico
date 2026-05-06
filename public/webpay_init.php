<?php
/**
 * FACTURADOR-PCCURico
 * Init Webpay Transaction
 */
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Database;
use Core\TransbankService;
use Core\Logger;

$token = $_POST['token'] ?? null;

if (!$token || !is_string($token) || strlen($token) !== 32) {
    http_response_code(400);
    echo 'Petición inválida.';
    exit;
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM invoices WHERE token = ?");
$stmt->execute([$token]);
$invoice = $stmt->fetch();

if (!$invoice) {
    http_response_code(404);
    echo 'Factura no encontrada.';
    exit;
}

$stmtPay = $db->prepare("SELECT SUM(amount) as paid FROM payments WHERE invoice_id = ?");
$stmtPay->execute([$invoice['id']]);
$paid = $stmtPay->fetch()['paid'] ?? 0;
$balance = $invoice['total'] - $paid;

if ($balance <= 0) {
    header("Location: view.php?token=" . urlencode($token));
    exit;
}

try {
    $tbk = new TransbankService();
    $buyOrder = "INV" . $invoice['id'] . time();
    $sessionId = "SESS" . uniqid();
    $returnUrl = "https://" . $_SERVER['HTTP_HOST'] . "/webpay_return.php?token=" . urlencode($token);
    $currency = strtoupper($invoice['currency'] ?? 'CLP');
    $rate = (float)($invoice['exchange_rate'] ?? 1);
    $webpayAmount = $currency === 'CLP' ? ceil($balance) : ceil($balance * max($rate, 1));

    $response = $tbk->createTransaction($buyOrder, $sessionId, $webpayAmount, $returnUrl);

    if ($response['status'] === 200 && isset($response['body']['url']) && isset($response['body']['token'])) {
        $url = htmlspecialchars($response['body']['url']);
        $tbkToken = htmlspecialchars($response['body']['token']);
        
        echo "<!DOCTYPE html>
        <html><head>
            <meta charset='UTF-8'>
            <title>Redirigiendo a Webpay...</title>
        </head>
        <body onload='document.forms[0].submit()'>
            <div style='text-align: center; margin-top: 50px; font-family: sans-serif;'>
                <h2>Redirigiendo al portal de pagos seguros de Transbank...</h2>
                <p>Por favor, espere.</p>
            </div>
            <form action='" . $url . "' method='POST'>
                <input type='hidden' name='token_ws' value='" . $tbkToken . "'>
            </form>
        </body></html>";
    } else {
        Logger::error("Webpay Transaction Failed: " . json_encode($response));
        http_response_code(500);
        echo "<h2>Error al conectar con Webpay</h2>";
        echo "<p>Por favor, intente más tarde.</p>";
        echo "<br><a href='view.php?token=" . urlencode($token) . "'>Volver a la Factura</a>";
    }
} catch (\Exception $e) {
    Logger::error("Webpay Init Exception: " . $e->getMessage());
    http_response_code(500);
    echo "<h2>Error del Sistema</h2>";
    echo "<p>Intente más tarde.</p>";
}
