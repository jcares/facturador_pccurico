<div class="glass-card">
    <div class="flex-between" style="margin-bottom: 30px;">
        <h3 class="section-heading" style="margin: 0;">Historial de Pagos</h3>
        <a href="invoices.php" class="btn-primary link-button">
            <i data-lucide="arrow-left"></i> Volver a Facturas
        </a>
    </div>

    <?php if(empty($payments)): ?>
        <div class="text-center" style="padding: 40px; color: var(--text-muted);">
            <i data-lucide="credit-card" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>No hay pagos registrados.</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Factura</th>
                        <th>Cliente</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $p): ?>
                        <?php
                            $currency = $p['invoice_currency'] ?? 'CLP';
                            $decimals = $currency === 'CLP' ? 0 : 2;
                            $prefix = $currency === 'CLP' ? '$' : $currency . ' ';
                            $amount = $prefix . number_format((float)$p['amount'], $decimals, ',', '.');
                        ?>
                        <tr>
                            <td class="highlight" style="color: var(--primary);">#<?= (int)$p['id'] ?></td>
                            <td><?= htmlspecialchars($p['invoice_number'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($p['client_name'] ?? 'Cliente Genérico') ?></td>
                            <td style="font-weight: 600;"><?= htmlspecialchars($amount) ?></td>
                            <td>
                                <span class="status-badge">
                                    <?= htmlspecialchars($p['method']) ?>
                                </span>
                            </td>
                            <td style="color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards View -->
        <div class="mobile-cards" style="display: none;">
            <?php foreach($payments as $p): ?>
                <?php
                    $currency = $p['invoice_currency'] ?? 'CLP';
                    $decimals = $currency === 'CLP' ? 0 : 2;
                    $prefix = $currency === 'CLP' ? '$' : $currency . ' ';
                    $amount = $prefix . number_format((float)$p['amount'], $decimals, ',', '.');
                ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title">#<?= (int)$p['id'] ?> - <?= htmlspecialchars($amount) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                        </div>
                    </div>
                    <div class="mobile-card-meta">
                        <strong>Factura:</strong> <?= htmlspecialchars($p['invoice_number'] ?? 'N/A') ?><br>
                        <strong>Cliente:</strong> <?= htmlspecialchars($p['client_name'] ?? 'Cliente Genérico') ?><br>
                        <strong>Método:</strong>
                        <span class="status-badge" style="font-size: 0.7rem; padding: 2px 6px;">
                            <?= htmlspecialchars($p['method']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
