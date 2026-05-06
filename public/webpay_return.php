<?php
/**
 * FACTURADOR-PCCURico
 * Return from Webpay Transaction
 */
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Database;
use Core\TransbankService;
use Modules\Payments\Payment;

$token = $_GET['token'] ?? null;
$tokenWs = $_POST['token_ws'] ?? $_GET['token_ws'] ?? null;
$tbkToken = $_POST['TBK_TOKEN'] ?? $_GET['TBK_TOKEN'] ?? null;

if (!$token) {
    die("Petición inválida. Falla de seguridad.");
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM invoices WHERE token = ?");
$stmt->execute([$token]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die("Factura no encontrada.");
}

// Check if user aborted
if ($tbkToken && !$tokenWs) {
    // User aborted
    header("Location: view.php?token={$token}&payment_status=aborted");
    exit;
}

if (!$tokenWs) {
    die("Token WS no recibido.");
}

$tbk = new TransbankService();
$response = $tbk->commitTransaction($tokenWs);

if ($response['status'] === 200) {
    $data = $response['body'];
    
    if (isset($data['response_code']) && $data['response_code'] === 0) {
        // Payment authorized
        try {
            Payment::create([
                'invoice_id' => $invoice['id'],
                'amount' => $data['amount'],
                'method' => 'Webpay Plus (TC: XXXX-'.$data['card_detail']['card_number'].')'
            ]);
            
            header("Location: view.php?token={$token}&payment_status=success");
            exit;
        } catch (\Exception $e) {
            die("Error crítico al registrar el pago aprobado: " . $e->getMessage());
        }
    } else {
        // Payment rejected
        header("Location: view.php?token={$token}&payment_status=rejected");
        exit;
    }
} else {
    // HTTP Error on commit
    header("Location: view.php?token={$token}&payment_status=error");
    exit;
}
