<?php

$recurringInvoices = $recurringInvoices ?? [];

$recurringTotal = 0;
$nextRun = null;

foreach ($recurringInvoices as $r) {

    $recurringTotal += (float)($r['total'] ?? 0);

    if (!empty($r['next_run_date'])) {

        $date = strtotime($r['next_run_date']);

        if ($date && (!$nextRun || $date < $nextRun)) {
            $nextRun = $date;
        }
    }
}
?>

<div class="glass-card">

    <div class="flex-between" style="margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">

        <div>
            <h3 class="section-heading" style="margin: 0;">
                Facturas recurrentes
            </h3>

            <p style="margin: 6px 0 0; color: var(--text-muted);">
                Gestiona ciclos, exportaciones e importaciones desde un solo lugar.
            </p>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">

            <a href="invoices.php?action=create" class="btn-primary">
                <i data-lucide="plus-circle"></i>
                Crear recurrente
            </a>

            <a href="tools.php?action=export" class="btn-secondary">
                <i data-lucide="download"></i>
                Exportar
            </a>

            <a href="tools.php" class="btn-secondary">
                <i data-lucide="upload"></i>
                Importar
            </a>

        </div>

    </div>

    <div class="summary-grid">

        <div class="summary-card">

            <div class="summary-title">
                Recurrencias totales
            </div>

            <div class="summary-value">
                <?= number_format(count($recurringInvoices), 0, ',', '.') ?>
            </div>

        </div>

        <div class="summary-card">

            <div class="summary-title">
                Valores programados
            </div>

            <div class="summary-value">
                $<?= number_format($recurringTotal, 0, ',', '.') ?>
            </div>

        </div>

        <div class="summary-card">

            <div class="summary-title">
                Próxima emisión
            </div>

            <div class="summary-value">
                <?= $nextRun ? date('d/m/Y', $nextRun) : 'Sin datos' ?>
            </div>

        </div>

    </div>

    <?php if(empty($recurringInvoices)): ?>

        <div class="text-center" style="padding:40px; color:var(--text-muted);">

            <i data-lucide="repeat"
               style="width:48px; height:48px; margin-bottom:16px; opacity:0.5;">
            </i>

            <p>No hay facturas recurrentes configuradas.</p>

        </div>

    <?php else: ?>

        <div class="table-container">

            <table class="table-clean">

                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Frecuencia</th>
                        <th>Próxima emisión</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Ciclos</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach($recurringInvoices as $r): ?>

                    <?php $currency = $r['currency'] ?? 'CLP'; ?>

                    <tr>

                        <td class="highlight">
                            <?= htmlspecialchars($r['client_name'] ?? 'Cliente') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($r['frequency']) ?>
                        </td>

                        <td style="color: var(--text-muted);">
                            <?= !empty($r['next_run_date'])
                                ? date('d/m/Y', strtotime($r['next_run_date']))
                                : '-' ?>
                        </td>

                        <td style="font-weight:600;">

                            <?= $currency === 'CLP'
                                ? '$'
                                : htmlspecialchars($currency) . ' ' ?>

                            <?= number_format(
                                (float)$r['total'],
                                $currency === 'CLP' ? 0 : 2,
                                ',',
                                '.'
                            ) ?>

                        </td>

                        <td>

                            <span class="status-badge status-sent">

                                <?= htmlspecialchars($r['status'] ?? 'Pendiente') ?>

                            </span>

                        </td>

                        <td>

                            <?= (int)$r['cycles_generated'] ?>

                            <?= $r['remaining_cycles'] !== null
                                ? ' / ' . (int)$r['remaining_cycles']
                                : ' / sin límite' ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <!-- MOBILE -->

        <div class="mobile-cards" style="display:none;">

            <?php foreach($recurringInvoices as $r): ?>

                <?php $currency = $r['currency'] ?? 'CLP'; ?>

                <div class="mobile-card">

                    <div class="mobile-card-header">

                        <div class="mobile-card-title">

                            <?= htmlspecialchars($r['client_name'] ?? 'Cliente') ?>

                        </div>

                        <span class="status-badge status-sent">

                            <?= htmlspecialchars($r['status'] ?? 'Pendiente') ?>

                        </span>

                    </div>

                    <div class="mobile-card-meta">

                        <strong>Frecuencia:</strong>

                        <?= htmlspecialchars($r['frequency']) ?>

                        <br>

                        <strong>Próxima:</strong>

                        <?= !empty($r['next_run_date'])
                            ? date('d/m/Y', strtotime($r['next_run_date']))
                            : '-' ?>

                        <br>

                        <strong>Total:</strong>

                        <?= $currency === 'CLP'
                            ? '$'
                            : htmlspecialchars($currency) . ' ' ?>

                        <?= number_format(
                            (float)$r['total'],
                            $currency === 'CLP' ? 0 : 2,
                            ',',
                            '.'
                        ) ?>

                        <br>

                        <strong>Ciclos:</strong>

                        <?= (int)$r['cycles_generated'] ?>

                        <?= $r['remaining_cycles'] !== null
                            ? ' / ' . (int)$r['remaining_cycles']
                            : ' / sin límite' ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>