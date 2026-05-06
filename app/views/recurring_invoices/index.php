<div class="glass-card">
    <div class="flex-between" style="margin-bottom: 30px;">
        <h3 class="section-heading" style="margin: 0;">Facturas recurrentes</h3>
        <a href="invoices.php?action=create" class="btn-primary link-button">
            <i data-lucide="repeat"></i> Nueva recurrente
        </a>
    </div>

    <?php if(empty($recurringInvoices)): ?>
        <div class="text-center" style="padding: 40px; color: var(--text-muted);">
            <i data-lucide="repeat" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>No hay facturas recurrentes configuradas.</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Frecuencia</th>
                        <th>Proxima emision</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Ciclos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recurringInvoices as $r): ?>
                        <?php $currency = $r['currency'] ?? 'CLP'; ?>
                        <tr>
                            <td class="highlight"><?= htmlspecialchars($r['client_name'] ?? 'Cliente') ?></td>
                            <td><?= htmlspecialchars($r['frequency']) ?></td>
                            <td style="color: var(--text-muted);">
                                <?= !empty($r['next_run_date']) ? date('d/m/Y', strtotime($r['next_run_date'])) : '-' ?>
                            </td>
                            <td style="font-weight: 600;">
                                <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$r['total'], $currency === 'CLP' ? 0 : 2, ',', '.') ?>
                            </td>
                            <td><span class="status-badge status-sent"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td>
                                <?= (int)$r['cycles_generated'] ?><?= $r['remaining_cycles'] !== null ? ' / ' . (int)$r['remaining_cycles'] : ' / sin limite' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
