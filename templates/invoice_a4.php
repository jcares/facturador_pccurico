<?php
$currency = $invoice['currency'] ?? 'CLP';
$money = function ($amount) use ($currency) {
    $decimals = $currency === 'CLP' ? 0 : 2;
    $prefix = $currency === 'CLP' ? '$' : $currency . ' ';
    return $prefix . number_format((float)$amount, $decimals, ',', '.');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= htmlspecialchars($invoice['number']) ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 40px;
            background: #fff;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            padding: 30px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #10b981;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .company-details h2 { margin: 0; color: #10b981; }
        .company-details p { margin: 5px 0; color: #555; }
        .invoice-details { text-align: right; }
        .invoice-details h1 { margin: 0; font-size: 24px; color: #333; }
        .invoice-details p { margin: 5px 0; font-size: 14px; color: #777; }
        
        .client-info {
            margin-bottom: 30px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
        }
        .client-info h3 { margin-top: 0; margin-bottom: 10px; color: #333; font-size: 16px; }
        .client-info p { margin: 5px 0; font-size: 14px; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #10b981;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals {
            width: 50%;
            float: right;
            margin-top: 20px;
        }
        .totals table th { background: transparent; color: #333; text-align: right; border-bottom: none; }
        .totals table td { text-align: right; font-weight: bold; border-bottom: none;}
        .totals table tr.total td { font-size: 18px; color: #10b981; border-top: 2px solid #10b981; }

        @media print {
            body { padding: 0; background: #fff; }
            .invoice-box { border: none; box-shadow: none; max-width: 100%; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 5px; cursor: pointer;">Imprimir Factura</button>
        <a href="invoices.php" style="margin-left: 10px; color: #555; text-decoration: none;">Volver al Historial</a>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="company-details" style="display: flex; align-items: center; gap: 20px;">
                <?php if(!empty($settings['biz_logo'])): ?>
                    <img src="uploads/<?= htmlspecialchars($settings['biz_logo']) ?>" alt="Logo" style="max-height: 80px; max-width: 150px;">
                <?php endif; ?>
                <div>
                    <h2><?= htmlspecialchars($settings['biz_name'] ?? 'PC Curicó SpA') ?></h2>
                    <p>RUT: <?= htmlspecialchars($settings['biz_rut'] ?? '76.123.456-7') ?></p>
                    <p>Giro: <?= htmlspecialchars($settings['biz_giro'] ?? 'Servicios Informáticos') ?></p>
                    <p>Dirección: <?= htmlspecialchars($settings['biz_address'] ?? 'Curicó, Chile') ?></p>
                </div>
            </div>
            <div class="invoice-details">
                <h1>FACTURA NO. <?= htmlspecialchars($invoice['number']) ?></h1>
                <p>Fecha de Emisión: <?= date('d/m/Y', strtotime($invoice['created_at'])) ?></p>
                <?php if(!empty($invoice['due_date'])): ?>
                    <p>Fecha de Vencimiento: <?= date('d/m/Y', strtotime($invoice['due_date'])) ?></p>
                <?php endif; ?>
                <p>Moneda: <?= htmlspecialchars($currency) ?></p>
                <?php if($currency !== 'CLP' && !empty($invoice['exchange_rate'])): ?>
                    <p>Tipo de cambio: $<?= number_format((float)$invoice['exchange_rate'], 0, ',', '.') ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="client-info">
            <h3>Facturar a:</h3>
            <p><strong>Señor(es):</strong> <?= htmlspecialchars($invoice['client_name'] ?? 'Cliente Genérico') ?></p>
            <p><strong>RUT:</strong> <?= htmlspecialchars($invoice['client_rut'] ?? '-') ?></p>
            <?php if(!empty($invoice['client_address'])): ?>
                <p><strong>Dirección:</strong> <?= htmlspecialchars($invoice['client_address']) ?></p>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-right">Precio Neto</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($invoice['items'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_sku'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($item['product_name'] ?? 'Producto Eliminado') ?></td>
                        <td class="text-center"><?= $item['qty'] ?></td>
                        <td class="text-right"><?= $money($item['price']) ?></td>
                        <td class="text-right"><?= $money($item['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <th>Subtotal (Neto):</th>
                    <td><?= $money($invoice['subtotal']) ?></td>
                </tr>
                <tr>
                    <th>IVA (19%):</th>
                    <td><?= $money($invoice['tax']) ?></td>
                </tr>
                <tr class="total">
                    <th>TOTAL A PAGAR:</th>
                    <td><?= $money($invoice['total']) ?></td>
                </tr>
            </table>
        </div>
        
        <div style="clear: both;"></div>
        
        <div style="margin-top: 50px; text-align: center; color: #777; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px;">
            Gracias por su preferencia. Sistema generado por Facturador-PCCurico.
        </div>
    </div>
</body>
</html>
