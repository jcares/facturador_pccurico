<?php
$currency = $invoice['currency'] ?? 'CLP';
$decimals = $currency === 'CLP' ? 0 : 2;
$money = function ($amount) use ($currency, $decimals) {
    $prefix = $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ';
    return $prefix . number_format((float)$amount, $decimals, ',', '.');
};
?>

<div class="glass-card">
    <div class="flex-between mb-20">
        <div>
            <h3 class="section-heading m-0">Documento <?= htmlspecialchars($invoice['number']) ?></h3>
            <p class="form-help">
                <?= htmlspecialchars($invoice['client_name'] ?? 'Cliente Genérico') ?> — <?= date('d/m/Y H:i', strtotime($invoice['created_at'])) ?>
            </p>
        </div>
        <div class="d-flex gap-10 flex-wrap justify-end">
            <a href="invoices.php" class="btn-secondary">Volver</a>
            <a href="invoices.php?action=print&id=<?= (int)$invoice['id'] ?>&format=a4" target="_blank" class="btn-primary">
                <i data-lucide="printer"></i> Imprimir A4
            </a>
            <a href="invoices.php?action=download&id=<?= (int)$invoice['id'] ?>" class="btn-secondary">
                <i data-lucide="download"></i> Descargar
            </a>
            <a href="invoices.php?action=send&id=<?= (int)$invoice['id'] ?>" class="btn-success">
                <i data-lucide="mail"></i> Enviar por Email
            </a>
            <a href="payments.php?invoice_id=<?= (int)$invoice['id'] ?>" class="btn-primary">
                <i data-lucide="dollar-sign"></i> Registrar Pago
            </a>
        </div>
    </div>

    <div class="form-row mb-20">
        <div class="summary-card">
            <div class="summary-title">Estado</div>
            <div class="status-badge status-<?= htmlspecialchars($invoice['status'] ?? 'sent') ?>"><?= htmlspecialchars($invoice['status']) ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-title">Vencimiento</div>
            <div class="summary-value font-12rem"><?= !empty($invoice['due_date']) ? date('d/m/Y', strtotime($invoice['due_date'])) : '-' ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-title">Total Documento</div>
            <div class="summary-value font-12rem text-primary"><?= $money($invoice['total']) ?></div>
        </div>
    </div>

    <h3 class="section-subtitle">Productos del documento</h3>
    <?php if (empty($items)): ?>
        <div class="alert alert-error">
            No hay líneas registradas para este documento.
        </div>
    <?php else: ?>
        <div class="table-container mb-20">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Precio</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="highlight"><?= htmlspecialchars($item['product_name'] ?? 'Producto eliminado') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($item['product_sku'] ?? '-') ?></td>
                            <td class="text-right"><?= number_format((float)$item['qty'], floor((float)$item['qty']) == (float)$item['qty'] ? 0 : 2, ',', '.') ?></td>
                            <td class="text-right"><?= $money($item['price']) ?></td>
                            <td class="text-right highlight"><?= $money($item['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="totals-section">
        <div class="totals-wrapper">
            <div class="flex-between mb-10 text-muted">
                <span>Subtotal Neto</span>
                <span><?= $money($invoice['subtotal']) ?></span>
            </div>
            <div class="flex-between mb-10 text-muted">
                <span>IVA (19%)</span>
                <span><?= $money($invoice['tax']) ?></span>
            </div>
            <div class="flex-between total-row">
                <span>Total</span>
                <span><?= $money($invoice['total']) ?></span>
            </div>
        </div>
    </div>
</div>
