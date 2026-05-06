<div class="glass-card">
    <div class="flex-between" style="margin-bottom: 30px;">
        <h3 class="section-heading" style="margin: 0;">Historial de Documentos</h3>
        <a href="invoices.php?action=create" class="btn-primary link-button">
            <i data-lucide="plus"></i> Nueva Venta
        </a>
    </div>

    <?php if(empty($invoices)): ?>
        <div class="text-center" style="padding: 40px; color: var(--text-muted);">
            <i data-lucide="file-x" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>No hay facturas ni boletas registradas.</p>
        </div>
    <?php else: ?>
        <table class="table-clean">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($invoices as $i): ?>
                    <tr>
                        <td class="highlight" style="color: var(--primary);"><?= htmlspecialchars($i['number']) ?></td>
                        <td><?= htmlspecialchars($i['client_name'] ?? 'Cliente Genérico') ?></td>
                        <td style="color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($i['created_at'])) ?></td>
                        <td style="font-weight: 600;">$<?= number_format($i['total'], 0, ',', '.') ?></td>
                        <td>
                            <span class="status-badge status-sent">
                                <?= htmlspecialchars($i['status']) ?>
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <a href="payments.php?invoice_id=<?= $i['id'] ?>" style="color: var(--primary); margin-right: 15px; text-decoration: none;" title="Registrar Pago">
                                <i data-lucide="dollar-sign"></i>
                            </a>
                            <a href="invoices.php?action=print&id=<?= $i['id'] ?>&format=a4" target="_blank" style="color: var(--text-main); margin-right: 10px; text-decoration: none;" title="Imprimir A4">
                                <i data-lucide="printer"></i>
                            </a>
                            <a href="invoices.php?action=print&id=<?= $i['id'] ?>&format=ticket_80mm" target="_blank" style="color: var(--text-muted); text-decoration: none;" title="Ticket 80mm">
                                <i data-lucide="receipt"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
