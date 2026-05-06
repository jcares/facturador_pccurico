<?php
/**
 * FACTURADOR-PCCURico
 * Return from Webpay Transaction
 */
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Database;
use Core\TransbankService;
use Core\Logger;
use Modules\Payments\Payment;

$token = $_GET['token'] ?? null;
$tokenWs = $_POST['token_ws'] ?? $_GET['token_ws'] ?? null;
$tbkToken = $_POST['TBK_TOKEN'] ?? $_GET['TBK_TOKEN'] ?? null;

if (!$token || !is_string($token) || strlen($token) !== 32) {
    http_response_code(400);
    Logger::error("Webpay Return: Invalid token.");
    echo 'Petición inválida.';
    exit;
}

$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM invoices WHERE token = ?");
$stmt->execute([$token]);
$invoice = $stmt->fetch();

if (!$invoice) {
    http_response_code(404);
    Logger::error("Webpay Return: Invoice not found for token.");
    echo 'Factura no encontrada.';
    exit;
}

if ($tbkToken && !$tokenWs) {
    header("Location: view.php?token=" . urlencode($token) . "&payment_status=aborted");
    exit;
}

if (!$tokenWs) {
    http_response_code(400);
    Logger::error("Webpay Return: No token_ws received.");
    echo 'Token WS no recibido.';
    exit;
}

try {
    $tbk = new TransbankService();
    $response = $tbk->commitTransaction($tokenWs);

    if ($response['status'] === 200) {
        $data = $response['body'];
        
        if (isset($data['response_code']) && $data['response_code'] === 0) {
            try {
                $cardLast4 = isset($data['card_detail']['card_number']) ? 
                    substr($data['card_detail']['card_number'], -4) : 'XXXX';
                $currency = strtoupper($invoice['currency'] ?? 'CLP');
                $rate = (float)($invoice['exchange_rate'] ?? 1);
                $paidAmount = (float)($data['amount'] ?? 0);

                if ($currency !== 'CLP' && $rate > 0) {
                    $paidAmount = round($paidAmount / $rate, 2);
                }
                
                Payment::create([
                    'invoice_id' => $invoice['id'],
                    'amount' => $paidAmount,
                    'method' => 'Webpay Plus (TC: ' . $cardLast4 . ')'
                ]);
                
                header("Location: view.php?token=" . urlencode($token) . "&payment_status=success");
                exit;
            } catch (\Exception $e) {
                Logger::error("Webpay Payment Creation Error: " . $e->getMessage());
                http_response_code(500);
                echo 'Error al registrar el pago. Comuníquese con soporte.';
                exit;
            }
        } else {
            Logger::info("Webpay Payment Rejected: " . json_encode($data));
            header("Location: view.php?token=" . urlencode($token) . "&payment_status=rejected");
            exit;
        }
    } else {
        Logger::error("Webpay Commit HTTP Error: " . json_encode($response));
        header("Location: view.php?token=" . urlencode($token) . "&payment_status=error");
        exit;
    }
} catch (\Exception $e) {
    Logger::error("Webpay Return Exception: " . $e->getMessage());
    http_response_code(500);
    header("Location: view.php?token=" . urlencode($token) . "&payment_status=error");
    exit;
}
