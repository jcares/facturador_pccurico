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
    <title>Ticket <?= htmlspecialchars($invoice['number']) ?></title>
    <style>
        body { font-family: "Courier New", Courier, monospace; color: #000; margin: 0; padding: 0; background: #fff; font-size: 12px; width: 80mm; }
        .ticket-box { padding: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .header { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p, .info p { margin: 2px 0; }
        .info { margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 2px 0; vertical-align: top; }
        .item-row td { border-bottom: 1px dotted #ccc; padding-bottom: 4px; margin-bottom: 4px; }
        .totals { border-top: 1px dashed #000; padding-top: 5px; }
        .totals p { display: flex; justify-content: space-between; margin: 2px 0; }
        .total-row { font-size: 16px; font-weight: bold; margin-top: 5px; padding-top: 5px; border-top: 1px solid #000; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px; background: #000; color: white; border: none; cursor: pointer;">Imprimir Ticket</button>
        <br><br>
        <a href="invoices.php" style="color: blue; text-decoration: underline;">Volver</a>
    </div>

    <div class="ticket-box">
        <div class="header text-center">
            <?php if(!empty($settings['biz_logo'])): ?>
                <img src="uploads/<?= htmlspecialchars($settings['biz_logo']) ?>" alt="Logo" style="max-width: 100%; margin-bottom: 5px; filter: grayscale(100%);">
            <?php endif; ?>
            <h2><?= htmlspecialchars($settings['biz_name'] ?? 'PC Curico SpA') ?></h2>
            <p>RUT: <?= htmlspecialchars($settings['biz_rut'] ?? '76.123.456-7') ?></p>
            <p><?= htmlspecialchars($settings['biz_address'] ?? 'Curico, Chile') ?></p>
        </div>

        <div class="info">
            <p><span class="bold">TICKET NO:</span> <?= htmlspecialchars($invoice['number']) ?></p>
            <p><span class="bold">FECHA:</span> <?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?></p>
            <p><span class="bold">CLIENTE:</span> <?= htmlspecialchars($invoice['client_name'] ?? 'Generico') ?></p>
            <p><span class="bold">RUT:</span> <?= htmlspecialchars($invoice['client_rut'] ?? '-') ?></p>
            <p><span class="bold">MONEDA:</span> <?= htmlspecialchars($currency) ?></p>
            <?php if($currency !== 'CLP' && !empty($invoice['exchange_rate'])): ?>
                <p><span class="bold">TC:</span> $<?= number_format((float)$invoice['exchange_rate'], 0, ',', '.') ?></p>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-left" style="width: 50%;">Cant/Desc</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($invoice['items'] as $item): ?>
                    <tr class="item-row">
                        <td class="text-left">
                            <?= number_format((float)$item['qty'], floor((float)$item['qty']) == (float)$item['qty'] ? 0 : 2, ',', '.') ?>x <?= htmlspecialchars($item['product_name'] ?? 'Item') ?><br>
                            <small><?= $money($item['price']) ?> c/u</small>
                        </td>
                        <td class="text-right" style="vertical-align: bottom;"><?= $money($item['total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <p><span>Subtotal:</span> <span><?= $money($invoice['subtotal']) ?></span></p>
            <p><span>IVA (19%):</span> <span><?= $money($invoice['tax']) ?></span></p>
            <div class="totals total-row">
                <p><span>TOTAL:</span> <span><?= $money($invoice['total']) ?></span></p>
            </div>
        </div>

        <div class="text-center" style="margin-top: 20px; font-size: 10px;">
            <p>Gracias por su compra</p>
            <p>Facturador PCCurico</p>
        </div>
    </div>
</body>
</html>
