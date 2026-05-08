<div style="max-width: 1000px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Orden de Compra #<?= htmlspecialchars($order['number']) ?></h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;">Detalles de la orden de compra.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="purchase_orders.php?action=edit&id=<?= $order['id'] ?>" class="btn-secondary">Editar</a>
            <a href="purchase_orders.php" class="btn-secondary">Volver</a>
        </div>
    </div>

    <div class="glass-card" style="padding: 32px;">
        <!-- Order Header -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-bottom: 32px;">
            <div>
                <h3 style="margin: 0 0 16px; font-weight: 600;">Información del Proveedor</h3>
                <div style="display: grid; gap: 8px;">
                    <div><strong>Nombre:</strong> <?= htmlspecialchars($order['supplier_name']) ?></div>
                    <?php if(!empty($order['supplier_rut'])): ?>
                        <div><strong>RUT:</strong> <?= htmlspecialchars($order['supplier_rut']) ?></div>
                    <?php endif; ?>
                    <?php if(!empty($order['supplier_email'])): ?>
                        <div><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($order['supplier_email']) ?>"><?= htmlspecialchars($order['supplier_email']) ?></a></div>
                    <?php endif; ?>
                    <?php if(!empty($order['supplier_phone'])): ?>
                        <div><strong>Teléfono:</strong> <a href="tel:<?= htmlspecialchars($order['supplier_phone']) ?>"><?= htmlspecialchars($order['supplier_phone']) ?></a></div>
                    <?php endif; ?>
                    <?php if(!empty($order['supplier_address'])): ?>
                        <div><strong>Dirección:</strong> <?= htmlspecialchars($order['supplier_address']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h3 style="margin: 0 0 16px; font-weight: 600;">Detalles de la Orden</h3>
                <div style="display: grid; gap: 8px;">
                    <div><strong>Número:</strong> <?= htmlspecialchars($order['number']) ?></div>
                    <div><strong>Estado:</strong> 
                        <span class="badge" style="background: 
                            <?php if($order['status'] === 'draft'): ?>#6b7280<?php elseif($order['status'] === 'sent'): ?>#3b82f6<?php elseif($order['status'] === 'received'): ?>#10b981<?php else: ?>#ef4444<?php endif; ?>; 
                            color: white;">
                            <?php 
                            $statusLabels = [
                                'draft' => 'Borrador',
                                'sent' => 'Enviada',
                                'received' => 'Recibida',
                                'canceled' => 'Cancelada'
                            ];
                            echo $statusLabels[$order['status']] ?? $order['status'];
                            ?>
                        </span>
                    </div>
                    <div><strong>Moneda:</strong> <?= $order['currency'] ?></div>
                    <?php if($order['exchange_rate'] != 1): ?>
                        <div><strong>Tasa de Cambio:</strong> <?= number_format($order['exchange_rate'], 4, ',', '.') ?></div>
                    <?php endif; ?>
                    <?php if(!empty($order['due_date'])): ?>
                        <div><strong>Vence:</strong> <?= date('d/m/Y', strtotime($order['due_date'])) ?></div>
                    <?php endif; ?>
                    <div><strong>Creada:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div style="margin-bottom: 32px;">
            <h3 style="margin: 0 0 16px; font-weight: 600;">Artículos</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: rgba(255,255,255,0.05);">
                            <th style="padding: 12px; text-align: left; border-bottom: 1px solid var(--glass-border);">Producto</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 1px solid var(--glass-border);">Cantidad</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 1px solid var(--glass-border);">Precio Unit.</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 1px solid var(--glass-border);">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid var(--glass-border);">
                                    <div style="font-weight: 500;"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <?php if(!empty($item['description'])): ?>
                                        <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 4px;"><?= htmlspecialchars($item['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: center; border-bottom: 1px solid var(--glass-border);">
                                    <?= number_format($item['qty'], 2, ',', '.') ?>
                                </td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid var(--glass-border);">
                                    $<?= number_format($item['price'], 0, ',', '.') ?>
                                </td>
                                <td style="padding: 12px; text-align: right; border-bottom: 1px solid var(--glass-border); font-weight: 500;">
                                    $<?= number_format($item['total'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background: rgba(255,255,255,0.05);">
                            <td colspan="3" style="padding: 12px; text-align: right; font-weight: 600;">Subtotal:</td>
                            <td style="padding: 12px; text-align: right; font-weight: 600;">$<?= number_format($order['subtotal'], 0, ',', '.') ?></td>
                        </tr>
                        <tr style="background: rgba(255,255,255,0.05);">
                            <td colspan="3" style="padding: 12px; text-align: right; font-weight: 600;">IVA (19%):</td>
                            <td style="padding: 12px; text-align: right; font-weight: 600;">$<?= number_format($order['tax'], 0, ',', '.') ?></td>
                        </tr>
                        <tr style="background: rgba(255,255,255,0.05); border-top: 2px solid var(--primary);">
                            <td colspan="3" style="padding: 12px; text-align: right; font-weight: 700; font-size: 1.1rem;">Total:</td>
                            <td style="padding: 12px; text-align: right; font-weight: 700; font-size: 1.1rem; color: var(--primary);">$<?= number_format($order['total'], 0, ',', '.') ?> <?= $order['currency'] ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <?php if(!empty($order['notes'])): ?>
            <div>
                <h3 style="margin: 0 0 16px; font-weight: 600;">Notas</h3>
                <div style="background: rgba(255,255,255,0.02); padding: 16px; border-radius: 8px; border: 1px solid var(--glass-border);">
                    <?= nl2br(htmlspecialchars($order['notes'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>