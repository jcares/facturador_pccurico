<div style="max-width: 1000px; margin: 0 auto;">
    <div class="stat-grid">
        <div class="stat-card" style="border-left: 4px solid var(--primary);">
            <div class="stat-label">Total Facturado (Histórico)</div>
            <div class="stat-value">$<?= number_format($totalBilled, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #3b82f6;">
            <div class="stat-label">Ingresos Percibidos</div>
            <div class="stat-value">$<?= number_format($totalPaid, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card" style="border-left: 4px solid #ef4444;">
            <div class="stat-label">Cuentas por Cobrar</div>
            <div class="stat-value">$<?= number_format($totalPending, 0, ',', '.') ?></div>
        </div>
    </div>

    <div class="glass-card" style="margin-top: 40px;">
        <h3 style="margin-bottom: 20px; font-weight: 700; color: #ef4444; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="alert-circle"></i> Cuentas por Cobrar (Facturas Pendientes)
        </h3>
        
        <?php if(empty($receivables)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 20px;">No hay deudas pendientes registradas.</p>
        <?php else: ?>
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 12px; color: var(--text-muted);">Documento</th>
                        <th style="padding: 12px; color: var(--text-muted);">Cliente</th>
                        <th style="padding: 12px; color: var(--text-muted);">Vencimiento</th>
                        <th style="padding: 12px; color: var(--text-muted); text-align: right;">Total Factura</th>
                        <th style="padding: 12px; color: var(--text-muted); text-align: right;">Saldo Pendiente</th>
                        <th style="padding: 12px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($receivables as $r): ?>
                        <?php 
                            $isOverdue = !empty($r['due_date']) && strtotime($r['due_date']) < time(); 
                            $rowColor = $isOverdue ? 'rgba(239, 68, 68, 0.1)' : 'transparent';
                        ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); background: <?= $rowColor ?>;">
                            <td style="padding: 12px; font-weight: 700;"><?= htmlspecialchars($r['number']) ?></td>
                            <td style="padding: 12px;"><?= htmlspecialchars($r['client_name']) ?></td>
                            <td style="padding: 12px; color: <?= $isOverdue ? '#ef4444' : 'var(--text-muted)' ?>;">
                                <?= !empty($r['due_date']) ? date('d/m/Y', strtotime($r['due_date'])) : 'Sin fecha' ?>
                                <?= $isOverdue ? ' (Vencida)' : '' ?>
                            </td>
                            <td style="padding: 12px; text-align: right;">$<?= number_format($r['total'], 0, ',', '.') ?></td>
                            <td style="padding: 12px; text-align: right; font-weight: 800; color: #ef4444;">$<?= number_format($r['balance'], 0, ',', '.') ?></td>
                            <td style="padding: 12px; text-align: right;">
                                <a href="payments.php?invoice_id=<?= $r['id'] ?>" class="btn-primary" style="padding: 6px 12px; font-size: 0.8rem; width: auto;">Cobrar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
