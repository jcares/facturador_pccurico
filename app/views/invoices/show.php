<?php
$currency = $invoice['currency'] ?? 'CLP';
$decimals = $currency === 'CLP' ? 0 : 2;
$money = function ($amount) use ($currency, $decimals) {
    $prefix = $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ';
    return $prefix . number_format((float)$amount, $decimals, ',', '.');
};
?>

<div class="glass-card">
    <div class="flex-between" style="margin-bottom: 30px;">
        <div>
            <h3 class="section-heading" style="margin: 0;">Documento <?= htmlspecialchars($invoice['number']) ?></h3>
            <p style="color: var(--text-muted); margin: 8px 0 0;">
                <?= htmlspecialchars($invoice['client_name'] ?? 'Cliente Generico') ?> - <?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?>
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
            <a href="invoices.php" class="btn-secondary link-button">Volver</a>
            <a href="invoices.php?action=print&id=<?= (int)$invoice['id'] ?>&format=a4" target="_blank" class="btn-primary link-button">
                <i data-lucide="printer"></i> Imprimir A4
            </a>
            <a href="invoices.php?action=download&id=<?= (int)$invoice['id'] ?>" class="btn-secondary link-button">
                <i data-lucide="download"></i> Descargar
            </a>
            <a href="invoices.php?action=send&id=<?= (int)$invoice['id'] ?>" class="btn-primary link-button" style="background: var(--success, #10b981);">
                <i data-lucide="mail"></i> Enviar por Email
            </a>
            <a href="payments.php?invoice_id=<?= (int)$invoice['id'] ?>" class="btn-primary link-button">
                <i data-lucide="dollar-sign"></i> Registrar Pago
            </a>
        </div>
    </div>

    <div class="main-grid" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 28px;">
        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; padding: 16px;">
            <div style="color: var(--text-muted); font-size: 0.82rem;">Estado</div>
            <div style="font-weight: 800; margin-top: 6px;"><?= htmlspecialchars($invoice['status']) ?></div>
        </div>
        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; padding: 16px;">
            <div style="color: var(--text-muted); font-size: 0.82rem;">Vencimiento</div>
            <div style="font-weight: 800; margin-top: 6px;"><?= !empty($invoice['due_date']) ? date('d/m/Y', strtotime($invoice['due_date'])) : '-' ?></div>
        </div>
        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; padding: 16px;">
            <div style="color: var(--text-muted); font-size: 0.82rem;">Total</div>
            <div style="font-weight: 800; margin-top: 6px; color: var(--primary);"><?= $money($invoice['total']) ?></div>
        </div>
    </div>

    <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px;">Productos del documento</h3>
    <?php if (empty($items)): ?>
        <div style="padding: 24px; color: var(--text-muted); border: 1px solid var(--glass-border); border-radius: 8px;">
            No hay lineas registradas para este documento.
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th style="text-align: right;">Cantidad</th>
                        <th style="text-align: right;">Precio</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="highlight"><?= htmlspecialchars($item['product_name'] ?? 'Producto eliminado') ?></td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($item['product_sku'] ?? '-') ?></td>
                            <td style="text-align: right;"><?= number_format((float)$item['qty'], floor((float)$item['qty']) == (float)$item['qty'] ? 0 : 2, ',', '.') ?></td>
                            <td style="text-align: right;"><?= $money($item['price']) ?></td>
                            <td style="text-align: right; font-weight: 700;"><?= $money($item['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: flex-end; margin-top: 24px;">
        <div style="width: 320px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--text-muted);">
                <span>Subtotal Neto</span>
                <span><?= $money($invoice['subtotal']) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--text-muted);">
                <span>IVA (19%)</span>
                <span><?= $money($invoice['tax']) ?></span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 1.2rem; font-weight: 800; color: var(--primary);">
                <span>Total</span>
                <span><?= $money($invoice['total']) ?></span>
            </div>
        </div>
    </div>
</div>
