<div class="glass-card">
    <div class="flex-between" style="margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
        <div>
            <h3 class="section-heading" style="margin: 0;">Historial de Documentos</h3>
            <p style="margin: 6px 0 0; color: var(--text-muted);">Gestiona facturas, boletas, registros e historial completo de operaciones.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="invoices.php?action=create" class="btn-primary" style="white-space: nowrap;">
                <i data-lucide="plus"></i> Nueva Venta
            </a>
            <a href="tools.php?action=export" class="btn-secondary" style="white-space: nowrap;">
                <i data-lucide="download"></i> Exportar
            </a>
            <a href="tools.php" class="btn-secondary" style="white-space: nowrap;">
                <i data-lucide="upload"></i> Importar
            </a>
        </div>
    </div>

    <?php if(empty($invoices)): ?>
        <div class="text-center" style="padding: 40px; color: var(--text-muted);">
            <i data-lucide="file-x" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>No hay facturas ni boletas registradas.</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>Numero</th>
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
                            <td class="highlight" style="color: var(--primary);">
                                <a href="invoices.php?id=<?= (int)$i['id'] ?>" style="color: inherit; text-decoration: none;">
                                    <?= htmlspecialchars($i['number']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($i['client_name'] ?? 'Cliente Generico') ?></td>
                            <td style="color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($i['created_at'])) ?></td>
                            <?php $currency = $i['currency'] ?? 'CLP'; ?>
                            <td style="font-weight: 600;">
                                <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$i['total'], $currency === 'CLP' ? 0 : 2, ',', '.') ?>
                            </td>
                            <td>
                                <span class="status-badge status-sent">
                                    <?= htmlspecialchars($i['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <?php if($i['status'] !== 'canceled'): ?>
                                    <a href="payments.php?invoice_id=<?= (int)$i['id'] ?>" style="color: var(--primary); margin-right: 15px; text-decoration: none;" title="Registrar Pago">
                                        <i data-lucide="dollar-sign"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="invoices.php?action=print&id=<?= (int)$i['id'] ?>&format=a4" target="_blank" style="color: var(--text-main); margin-right: 10px; text-decoration: none;" title="Imprimir A4">
                                    <i data-lucide="printer"></i>
                                </a>
                                <a href="invoices.php?action=print&id=<?= (int)$i['id'] ?>&format=ticket_80mm" target="_blank" style="color: var(--text-muted); margin-right: 10px; text-decoration: none;" title="Ticket 80mm">
                                    <i data-lucide="receipt"></i>
                                </a>
                                <?php if($i['status'] !== 'paid' && $i['status'] !== 'canceled'): ?>
                                    <form action="invoices.php?action=cancel" method="POST" style="display: inline;" onsubmit="return confirm('Anular este documento y registrar nota de credito generica?');">
                                        <?= \Core\Security::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                                        <input type="hidden" name="reason" value="Anulacion manual desde panel">
                                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;" title="Anular">
                                            <i data-lucide="ban"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards View -->
        <div class="mobile-cards" style="display: none;">
            <?php foreach($invoices as $i): ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title">
                            <a href="invoices.php?id=<?= (int)$i['id'] ?>" style="color: inherit; text-decoration: none;">
                                <?= htmlspecialchars($i['number']) ?>
                            </a>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            <?= date('d/m/Y H:i', strtotime($i['created_at'])) ?>
                        </div>
                    </div>
                    <div class="mobile-card-meta">
                        <strong>Cliente:</strong> <?= htmlspecialchars($i['client_name'] ?? 'Cliente Generico') ?><br>
                        <?php $currency = $i['currency'] ?? 'CLP'; ?>
                        <strong>Total:</strong> <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$i['total'], $currency === 'CLP' ? 0 : 2, ',', '.') ?><br>
                        <strong>Estado:</strong>
                        <span class="status-badge status-sent" style="font-size: 0.7rem; padding: 2px 6px;">
                            <?= htmlspecialchars($i['status']) ?>
                        </span>
                    </div>
                    <div class="mobile-card-actions">
                        <?php if($i['status'] !== 'canceled'): ?>
                            <a href="payments.php?invoice_id=<?= (int)$i['id'] ?>" class="btn-primary" style="flex: 1; text-align: center; padding: 8px; font-size: 0.8rem;">
                                <i data-lucide="dollar-sign" style="width: 14px;"></i> Pagar
                            </a>
                        <?php endif; ?>
                        <div style="display: flex; gap: 4px; flex: 1;">
                            <a href="invoices.php?action=print&id=<?= (int)$i['id'] ?>&format=a4" target="_blank" class="btn-secondary" style="flex: 1; text-align: center; padding: 8px; font-size: 0.7rem;">
                                <i data-lucide="printer" style="width: 12px;"></i> A4
                            </a>
                            <a href="invoices.php?action=print&id=<?= (int)$i['id'] ?>&format=ticket_80mm" target="_blank" class="btn-secondary" style="flex: 1; text-align: center; padding: 8px; font-size: 0.7rem;">
                                <i data-lucide="receipt" style="width: 12px;"></i> Ticket
                            </a>
                        </div>
                        <?php if($i['status'] !== 'paid' && $i['status'] !== 'canceled'): ?>
                            <form action="invoices.php?action=cancel" method="POST" style="flex: 1;" onsubmit="return confirm('Anular este documento y registrar nota de credito generica?');">
                                <?= \Core\Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                                <input type="hidden" name="reason" value="Anulacion manual desde panel">
                                <button type="submit" class="btn-secondary" style="width: 100%; background: rgba(239,68,68,0.1); color: #ef4444; border-color: rgba(239,68,68,0.3); padding: 8px; font-size: 0.8rem;">
                                    <i data-lucide="ban" style="width: 14px;"></i> Anular
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if(!empty($recurringInvoices)): ?>
    <div class="glass-card" style="margin-top: 24px;">
        <div class="flex-between" style="margin-bottom: 20px;">
            <h3 class="section-heading" style="margin: 0;">Facturas recurrentes</h3>
            <a href="invoices.php?action=create" class="btn-secondary link-button">
                <i data-lucide="repeat"></i> Nueva recurrente
            </a>
        </div>
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

        <div class="mobile-cards" style="display: none;">
            <?php foreach($recurringInvoices as $r): ?>
                <?php $currency = $r['currency'] ?? 'CLP'; ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title"><?= htmlspecialchars($r['client_name'] ?? 'Cliente') ?></div>
                        <span class="status-badge status-sent" style="font-size: 0.7rem; padding: 2px 6px;"><?= htmlspecialchars($r['status']) ?></span>
                    </div>
                    <div class="mobile-card-meta">
                        <strong>Frecuencia:</strong> <?= htmlspecialchars($r['frequency']) ?><br>
                        <strong>Proxima:</strong> <?= !empty($r['next_run_date']) ? date('d/m/Y', strtotime($r['next_run_date'])) : '-' ?><br>
                        <strong>Total:</strong> <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$r['total'], $currency === 'CLP' ? 0 : 2, ',', '.') ?><br>
                        <strong>Ciclos:</strong> <?= (int)$r['cycles_generated'] ?><?= $r['remaining_cycles'] !== null ? ' / ' . (int)$r['remaining_cycles'] : ' / sin limite' ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
