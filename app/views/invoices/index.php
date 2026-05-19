<div class="glass-card">
    <div class="flex-between mb-24 gap-16 flex-wrap">
        <div>
            <h3 class="section-heading m-0">Historial de Documentos</h3>
            <p class="section-desc">Gestiona facturas, boletas, registros e historial completo de operaciones.</p>
        </div>
        <div class="action-buttons">
            <a href="invoices.php?action=create" class="btn-primary whitespace-nowrap">
                <i data-lucide="plus"></i> Nueva Venta
            </a>
            <a href="tools.php?action=export" class="btn-secondary whitespace-nowrap">
                <i data-lucide="download"></i> Exportar
            </a>
            <a href="tools.php" class="btn-secondary whitespace-nowrap">
                <i data-lucide="upload"></i> Importar
            </a>
        </div>
    </div>

    <?php if(empty($invoices)): ?>
        <div class="text-center text-muted p-40">
            <i data-lucide="file-x" class="icon-lg mb-16 opacity-50"></i>
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
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($invoices as $i): ?>
                        <tr>
                            <td class="highlight text-primary">
                                <a href="invoices.php?id=<?= (int)$i['id'] ?>" class="text-inherit no-underline">
                                    <?= htmlspecialchars($i['number']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($i['client_name'] ?? 'Cliente Generico') ?></td>
                            <td class="text-muted"><?= date('d/m/Y H:i', strtotime($i['created_at'])) ?></td>
                            <?php $currency = $i['currency'] ?? 'CLP'; ?>
                            <td class="font-600">
                                <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$i['total'], $currency === 'CLP' ? 0 : 2, ',', '.') ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= htmlspecialchars($i['status'] ?? 'sent') ?>">
                                    <?= htmlspecialchars($i['status']) ?>
                                </span>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <?php if($i['status'] !== 'canceled'): ?>
                                    <a href="payments.php?invoice_id=<?= (int)$i['id'] ?>" class="text-primary no-underline mr-15" title="Registrar Pago">
                                        <i data-lucide="dollar-sign"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="invoices.php?action=print&id=<?= (int)$i['id'] ?>&format=a4" target="_blank" class="text-main no-underline mr-10" title="Imprimir A4">
                                    <i data-lucide="printer"></i>
                                </a>
                                <a href="invoices.php?action=print&id=<?= (int)$i['id'] ?>&format=ticket_80mm" target="_blank" class="text-muted no-underline mr-10" title="Ticket 80mm">
                                    <i data-lucide="receipt"></i>
                                </a>
                                <?php if($i['status'] !== 'paid' && $i['status'] !== 'canceled'): ?>
                                    <form action="invoices.php?action=cancel" method="POST" class="d-inline" onsubmit="return confirm('Anular este documento y registrar nota de credito generica?');">
                                        <?= \Core\Security::csrfField() ?>
                                        <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                                        <input type="hidden" name="reason" value="Anulacion manual desde panel">
                                        <button type="submit" class="btn-icon text-danger" title="Anular">
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
        <div class="mobile-cards">
            <?php foreach($invoices as $i): ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title">
                            <a href="invoices.php?id=<?= (int)$i['id'] ?>" class="text-inherit no-underline">
                                <?= htmlspecialchars($i['number']) ?>
                            </a>
                        </div>
                        <div class="mobile-card-subtitle">
                            <?= date('d/m/Y H:i', strtotime($i['created_at'])) ?>
                        </div>
                    </div>
                    <div class="mobile-card-meta">
                        <strong>Cliente:</strong> <?= htmlspecialchars($i['client_name'] ?? 'Cliente Generico') ?><br>
                        <?php $currency = $i['currency'] ?? 'CLP'; ?>
                        <strong>Total:</strong> <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$i['total'], $currency === 'CLP' ? 0 : 2, ',', '.') ?><br>
                        <strong>Estado:</strong>
                        <span class="status-badge status-sm status-<?= htmlspecialchars($i['status'] ?? 'sent') ?>">
                            <?= htmlspecialchars($i['status']) ?>
                        </span>
                    </div>
                    <div class="mobile-card-actions">
                        <?php if($i['status'] !== 'canceled'): ?>
                            <a href="payments.php?invoice_id=<?= (int)$i['id'] ?>" class="btn-primary btn-sm flex-1">
                                <i data-lucide="dollar-sign" class="icon-sm"></i> Pagar
                            </a>
                        <?php endif; ?>
                        <div class="d-flex gap-4 flex-1">
                            <a href="invoices.php?action=print&id=<?= (int)$i['id'] ?>&format=a4" target="_blank" class="btn-secondary btn-sm flex-1">
                                <i data-lucide="printer" class="icon-xs"></i> A4
                            </a>
                            <a href="invoices.php?action=print&id=<?= (int)$i['id'] ?>&format=ticket_80mm" target="_blank" class="btn-secondary btn-sm flex-1">
                                <i data-lucide="receipt" class="icon-xs"></i> Ticket
                            </a>
                        </div>
                        <?php if($i['status'] !== 'paid' && $i['status'] !== 'canceled'): ?>
                            <form action="invoices.php?action=cancel" method="POST" class="flex-1" onsubmit="return confirm('Anular este documento y registrar nota de credito generica?');">
                                <?= \Core\Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                                <input type="hidden" name="reason" value="Anulacion manual desde panel">
                                <button type="submit" class="btn-secondary btn-danger w-full btn-sm">
                                    <i data-lucide="ban" class="icon-sm"></i> Anular
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
    <div class="glass-card mt-24">
        <div class="flex-between mb-20">
            <h3 class="section-heading m-0">Facturas recurrentes</h3>
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
                            <td class="text-muted">
                                <?= !empty($r['next_run_date']) ? date('d/m/Y', strtotime($r['next_run_date'])) : '-' ?>
                            </td>
                            <td class="font-600">
                                <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$r['total'], $currency === 'CLP' ? 0 : 2, ',', '.') ?>
                            </td>
                            <td><span class="status-badge status-<?= htmlspecialchars($r['status'] ?? 'sent') ?>"><?= htmlspecialchars($r['status']) ?></span></td>
                            <td>
                                <?= (int)$r['cycles_generated'] ?><?= $r['remaining_cycles'] !== null ? ' / ' . (int)$r['remaining_cycles'] : ' / sin limite' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mobile-cards">
            <?php foreach($recurringInvoices as $r): ?>
                <?php $currency = $r['currency'] ?? 'CLP'; ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title"><?= htmlspecialchars($r['client_name'] ?? 'Cliente') ?></div>
                        <span class="status-badge status-sm status-<?= htmlspecialchars($r['status'] ?? 'sent') ?>"><?= htmlspecialchars($r['status']) ?></span>
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
