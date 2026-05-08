<div style="max-width: 1200px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Órdenes de Compra</h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;">Gestiona las órdenes de compra a proveedores.</p>
        </div>
        <a href="purchase_orders.php?action=create" class="btn-primary">Nueva Orden</a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php if($_GET['success'] === 'created'): ?>Orden de compra creada exitosamente.<?php endif; ?>
            <?php if($_GET['success'] === 'updated'): ?>Orden de compra actualizada exitosamente.<?php endif; ?>
            <?php if($_GET['success'] === 'deleted'): ?>Orden de compra eliminada exitosamente.<?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <?php if($_GET['error'] === 'invalid_id'): ?>ID de orden inválido.<?php endif; ?>
            <?php if($_GET['error'] === 'not_found'): ?>Orden de compra no encontrada.<?php endif; ?>
            <?php if($_GET['error'] === 'empty_fields'): ?>Los campos obligatorios no pueden estar vacíos.<?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 16px;">
            <?php foreach ($orders as $order): ?>
                <div class="order-card" style="border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; background: rgba(255,255,255,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <div>
                            <h4 style="margin: 0; font-weight: 600; color: var(--text-main);">#<?= htmlspecialchars($order['number']) ?></h4>
                            <p style="margin: 4px 0; color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($order['supplier_name']) ?></p>
                        </div>
                        <div style="display: flex; gap: 8px;">
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
                    </div>
                    
                    <div style="margin: 12px 0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="color: var(--text-muted); font-size: 0.9rem;">Total:</span>
                            <strong style="color: var(--text-main);">$<?= number_format($order['total'], 0, ',', '.') ?> <?= $order['currency'] ?></strong>
                        </div>
                        <?php if(!empty($order['due_date'])): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: var(--text-muted); font-size: 0.9rem;">Vence:</span>
                                <span style="color: var(--text-main); font-size: 0.9rem;"><?= date('d/m/Y', strtotime($order['due_date'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
                        <small style="color: var(--text-muted);">Creada: <?= date('d/m/Y', strtotime($order['created_at'])) ?></small>
                        <div style="display: flex; gap: 8px;">
                            <a href="purchase_orders.php?action=show&id=<?= $order['id'] ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Ver</a>
                            <a href="purchase_orders.php?action=edit&id=<?= $order['id'] ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Editar</a>
                            <form method="POST" action="purchase_orders.php?action=delete" style="display: inline;" onsubmit="return confirm('¿Eliminar esta orden de compra?')">
                                <?= \Core\Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; background: #ef4444; border-color: #ef4444;">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($orders)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <i data-lucide="shopping-bag" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <h3 style="margin: 0 0 8px; font-weight: 600;">No hay órdenes de compra</h3>
                    <p style="margin: 0;">Crea tu primera orden de compra para comenzar a gestionar tus adquisiciones.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>