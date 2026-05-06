<div style="display: flex; gap: 30px; max-width: 1000px; margin: 0 auto;">
    
    <!-- Resumen Factura y Pagos Previos -->
    <div style="flex: 1; display: flex; flex-direction: column; gap: 20px;">
        <div class="glass-card">
            <h3 style="margin-bottom: 15px; font-weight: 700;">Detalle de Factura</h3>
            <p><strong>N° Documento:</strong> <?= htmlspecialchars($invoice['number']) ?></p>
            <p><strong>Cliente:</strong> <?= htmlspecialchars($invoice['client_name']) ?></p>
            <p><strong>Total Facturado:</strong> $<?= number_format($invoice['total'], 0, ',', '.') ?></p>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--glass-border);">
                <p style="font-size: 1.2rem; font-weight: 800; color: <?= $balance > 0 ? '#ef4444' : '#10b981' ?>;">
                    Saldo Pendiente: $<?= number_format($balance, 0, ',', '.') ?>
                </p>
            </div>
        </div>

        <?php if(!empty($payments)): ?>
        <div class="glass-card">
            <h3 style="margin-bottom: 15px; font-weight: 700;">Historial de Pagos</h3>
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 8px 0; color: var(--text-muted);">Fecha</th>
                        <th style="padding: 8px 0; color: var(--text-muted);">Método</th>
                        <th style="padding: 8px 0; color: var(--text-muted); text-align: right;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payments as $p): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 8px 0;"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                            <td style="padding: 8px 0;"><?= htmlspecialchars($p['method']) ?></td>
                            <td style="padding: 8px 0; text-align: right; font-weight: 600;">$<?= number_format($p['amount'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulario de Pago -->
    <div class="glass-card" style="flex: 1;">
        <h3 style="margin-bottom: 20px; font-weight: 700;">Registrar Nuevo Pago</h3>
        
        <?php if($balance <= 0): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--primary); padding: 20px; border-radius: 12px; text-align: center;">
                <i data-lucide="check-circle" style="color: var(--primary); width: 48px; height: 48px; margin-bottom: 10px;"></i>
                <p style="color: var(--primary); font-weight: 600;">Esta factura ya ha sido pagada en su totalidad.</p>
                <a href="invoices.php" class="btn-primary" style="margin-top: 15px; display: inline-block; width: auto;">Volver a Facturas</a>
            </div>
        <?php else: ?>
            <form action="payments.php?action=store" method="POST">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="invoice_id" value="<?= $invoice['id'] ?>">
                
                <div class="form-group">
                    <label>Monto a Pagar ($)</label>
                    <input type="number" step="0.01" name="amount" value="<?= $balance ?>" max="<?= $balance ?>" required class="form-control" style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px; font-size: 1.2rem; font-weight: bold; text-align: right;">
                </div>
                
                <div class="form-group">
                    <label>Método de Pago</label>
                    <select name="method" required class="form-control" style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                        <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Tarjeta de Crédito/Débito">Tarjeta de Crédito/Débito</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 20px;">Registrar Pago</button>
            </form>
        <?php endif; ?>
    </div>
</div>
