<?php
/**
 * FACTURADOR-PCCURICO
 * Client Public Invoice View
 */

require_once __DIR__ . '/../bootstrap/app.php';

use Core\Database;

$token = $_GET['token'] ?? null;

if (!$token) {
    die("Enlace inválido.");
}

$db = Database::getInstance();

// Fetch Invoice by token
$stmt = $db->prepare("
    SELECT i.*, c.name as client_name, c.rut as client_rut, c.address as client_address, c.email as client_email
    FROM invoices i 
    LEFT JOIN clients c ON i.client_id = c.id 
    WHERE i.token = ?
");
$stmt->execute([$token]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die("Documento no encontrado o expirado.");
}

// Fetch Items
$stmtItems = $db->prepare("
    SELECT it.*, p.name as product_name, p.sku as product_sku 
    FROM invoice_items it 
    LEFT JOIN products p ON it.product_id = p.id 
    WHERE it.invoice_id = ?
");
$stmtItems->execute([$invoice['id']]);
$invoice['items'] = $stmtItems->fetchAll();

// Fetch Payments
$stmtPay = $db->prepare("SELECT SUM(amount) as paid FROM payments WHERE invoice_id = ?");
$stmtPay->execute([$invoice['id']]);
$paid = $stmtPay->fetch()['paid'] ?? 0;
$balance = $invoice['total'] - $paid;

// Get Business Settings
$stmtSettings = $db->query("SELECT * FROM settings");
$settingsRaw = $stmtSettings->fetchAll();
$settings = [];
foreach($settingsRaw as $s) {
    $settings[$s['key']] = $s['value'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= htmlspecialchars($invoice['number']) ?></title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; color: #334155; padding: 40px 20px; }
        .public-container { max-width: 800px; margin: 0 auto; }
        .invoice-paper { background: white; border-radius: 12px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .btn-webpay { background: #e0245e; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-weight: 700; font-size: 1.1rem; cursor: pointer; display: inline-block; text-decoration: none; text-align: center; width: 100%; transition: opacity 0.2s; }
        .btn-webpay:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="public-container">
        
        <?php if (isset($_GET['payment_status'])): ?>
            <?php if ($_GET['payment_status'] === 'success'): ?>
                <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 12px; padding: 20px; margin-bottom: 20px; text-align: center; color: #047857; font-weight: 600;">
                    ✓ Su pago mediante Transbank Webpay Plus se procesó exitosamente.
                </div>
            <?php elseif ($_GET['payment_status'] === 'rejected'): ?>
                <div style="background: #fef2f2; border: 1px solid #ef4444; border-radius: 12px; padding: 20px; margin-bottom: 20px; text-align: center; color: #b91c1c; font-weight: 600;">
                    ✗ Su pago ha sido rechazado por la entidad bancaria. Por favor, intente nuevamente.
                </div>
            <?php elseif ($_GET['payment_status'] === 'aborted'): ?>
                <div style="background: #fffbeb; border: 1px solid #f59e0b; border-radius: 12px; padding: 20px; margin-bottom: 20px; text-align: center; color: #b45309; font-weight: 600;">
                    ⚠ El proceso de pago fue cancelado o abortado.
                </div>
            <?php else: ?>
                <div style="background: #fef2f2; border: 1px solid #ef4444; border-radius: 12px; padding: 20px; margin-bottom: 20px; text-align: center; color: #b91c1c; font-weight: 600;">
                    ⚠ Ocurrió un error de comunicación con Transbank. Intente más tarde.
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($balance > 0): ?>
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; border-left: 5px solid #e0245e;">
                <h3 style="margin-bottom: 15px; color: #334155;">Pago Pendiente: $<?= number_format($balance, 0, ',', '.') ?></h3>
                <!-- Aquí enviaremos el formulario a webpay_init.php -->
                <form action="webpay_init.php" method="POST">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <button type="submit" class="btn-webpay">Pagar con Transbank Webpay Plus</button>
                </form>
            </div>
        <?php else: ?>
            <div style="background: #ecfdf5; border: 1px solid #10b981; border-radius: 12px; padding: 20px; margin-bottom: 20px; text-align: center; color: #047857; font-weight: 600;">
                ✓ Este documento ya ha sido pagado en su totalidad.
            </div>
        <?php endif; ?>

        <div class="invoice-paper">
            <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 20px;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <?php if(!empty($settings['biz_logo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($settings['biz_logo']) ?>" alt="Logo" style="max-height: 70px; max-width: 150px;">
                    <?php endif; ?>
                    <div>
                        <h2 style="color: #0f172a; font-weight: 800; margin-bottom: 5px;"><?= htmlspecialchars($settings['biz_name'] ?? 'Empresa') ?></h2>
                        <div style="color: #64748b; font-size: 0.9rem;">
                            <p><?= htmlspecialchars($settings['biz_rut'] ?? '-') ?></p>
                            <p><?= htmlspecialchars($settings['biz_address'] ?? '') ?></p>
                        </div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <h1 style="color: #0f172a; margin-bottom: 5px; font-size: 1.5rem;">FACTURA #<?= htmlspecialchars($invoice['number']) ?></h1>
                    <div style="color: #64748b; font-size: 0.9rem;">
                        <p>Fecha: <?= date('d/m/Y', strtotime($invoice['created_at'])) ?></p>
                        <?php if(!empty($invoice['due_date'])): ?>
                            <p>Vence: <?= date('d/m/Y', strtotime($invoice['due_date'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 30px; background: #f8fafc; padding: 20px; border-radius: 8px;">
                <h3 style="color: #0f172a; font-size: 1rem; margin-bottom: 10px;">Facturar a:</h3>
                <p style="font-weight: 600;"><?= htmlspecialchars($invoice['client_name']) ?></p>
                <p style="color: #64748b; font-size: 0.9rem;">RUT: <?= htmlspecialchars($invoice['client_rut'] ?? '-') ?></p>
                <p style="color: #64748b; font-size: 0.9rem;"><?= htmlspecialchars($invoice['client_address'] ?? '') ?></p>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 0.9rem;">
                        <th style="padding: 10px 0; text-align: left;">Descripción</th>
                        <th style="padding: 10px 0; text-align: center;">Cant.</th>
                        <th style="padding: 10px 0; text-align: right;">Precio</th>
                        <th style="padding: 10px 0; text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($invoice['items'] as $item): ?>
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 15px 0;">
                                <div style="font-weight: 500;"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div style="font-size: 0.8rem; color: #94a3b8;"><?= htmlspecialchars($item['product_sku'] ?? '') ?></div>
                            </td>
                            <td style="padding: 15px 0; text-align: center; color: #64748b;"><?= $item['qty'] ?></td>
                            <td style="padding: 15px 0; text-align: right; color: #64748b;">$<?= number_format($item['price'], 0, ',', '.') ?></td>
                            <td style="padding: 15px 0; text-align: right; font-weight: 500;">$<?= number_format($item['total'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="display: flex; justify-content: flex-end;">
                <div style="width: 300px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #64748b;">
                        <span>Subtotal Neto</span>
                        <span>$<?= number_format($invoice['subtotal'], 0, ',', '.') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: #64748b;">
                        <span>IVA (19%)</span>
                        <span>$<?= number_format($invoice['tax'], 0, ',', '.') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 2px solid #e2e8f0; font-size: 1.2rem; font-weight: 800; color: #0f172a;">
                        <span>TOTAL</span>
                        <span>$<?= number_format($invoice['total'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
</body>
</html>
