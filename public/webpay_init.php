<?php
/**
 * FACTURADOR-PCCURico
 * Init Webpay Transaction
 */
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Database;
use Core\TransbankService;

$token = $_POST['token'] ?? null;

if (!$token) {
    die("Petición inválida.");
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM invoices WHERE token = ?");
$stmt->execute([$token]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die("Factura no encontrada.");
}

// Check Balance
$stmtPay = $db->prepare("SELECT SUM(amount) as paid FROM payments WHERE invoice_id = ?");
$stmtPay->execute([$invoice['id']]);
$paid = $stmtPay->fetch()['paid'] ?? 0;
$balance = $invoice['total'] - $paid;

if ($balance <= 0) {
    header("Location: view.php?token={$token}");
    exit;
}

$tbk = new TransbankService();
$buyOrder = "INV" . $invoice['id'] . time(); // Unique buy order
$sessionId = "SESS" . uniqid();
$returnUrl = "https://" . $_SERVER['HTTP_HOST'] . "/webpay_return.php?token=" . $token;

$response = $tbk->createTransaction($buyOrder, $sessionId, $balance, $returnUrl);

if ($response['status'] === 200 && isset($response['body']['url']) && isset($response['body']['token'])) {
    $url = $response['body']['url'];
    $tbkToken = $response['body']['token'];
    
    // Auto-submit form to redirect to Webpay
    echo "
        <html><head><title>Redirigiendo a Webpay...</title></head>
        <body onload='document.forms[0].submit()'>
            <div style='text-align: center; margin-top: 50px; font-family: sans-serif;'>
                <h2>Redirigiendo al portal de pagos seguros de Transbank...</h2>
                <p>Por favor, espere.</p>
            </div>
            <form action='{$url}' method='POST'>
                <input type='hidden' name='token_ws' value='{$tbkToken}'>
            </form>
        </body></html>
    ";
} else {
    // Manejo de errores
    echo "<h2>Error al conectar con Webpay</h2>";
    echo "<pre>"; print_r($response); echo "</pre>";
    echo "<br><a href='view.php?token={$token}'>Volver a la Factura</a>";
}
