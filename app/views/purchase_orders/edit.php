<div style="max-width: 1000px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Editar Orden de Compra</h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;">Modifica los detalles de la orden de compra.</p>
        </div>
        <a href="purchase_orders.php" class="btn-secondary">Volver</a>
    </div>

    <form action="purchase_orders.php?action=edit&id=<?= $order['id'] ?>" method="POST" class="glass-card" style="padding: 32px;">
        <?= \Core\Security::csrfField() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div class="form-group">
                <label for="supplier_name">Nombre del Proveedor *</label>
                <input type="text" id="supplier_name" name="supplier_name" value="<?= htmlspecialchars($order['supplier_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="supplier_rut">RUT del Proveedor</label>
                <input type="text" id="supplier_rut" name="supplier_rut" value="<?= htmlspecialchars($order['supplier_rut'] ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div class="form-group">
                <label for="supplier_email">Email del Proveedor</label>
                <input type="email" id="supplier_email" name="supplier_email" value="<?= htmlspecialchars($order['supplier_email'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="supplier_phone">Teléfono del Proveedor</label>
                <input type="tel" id="supplier_phone" name="supplier_phone" value="<?= htmlspecialchars($order['supplier_phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="number">Número de Orden *</label>
                <input type="text" id="number" name="number" value="<?= htmlspecialchars($order['number']) ?>" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label for="supplier_address">Dirección del Proveedor</label>
            <textarea id="supplier_address" name="supplier_address" rows="2"><?= htmlspecialchars($order['supplier_address'] ?? '') ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div class="form-group">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <option value="draft" <?= $order['status'] === 'draft' ? 'selected' : '' ?>>Borrador</option>
                    <option value="sent" <?= $order['status'] === 'sent' ? 'selected' : '' ?>>Enviada</option>
                    <option value="received" <?= $order['status'] === 'received' ? 'selected' : '' ?>>Recibida</option>
                    <option value="canceled" <?= $order['status'] === 'canceled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>

            <div class="form-group">
                <label for="currency">Moneda</label>
                <select id="currency" name="currency">
                    <option value="CLP" <?= $order['currency'] === 'CLP' ? 'selected' : '' ?>>CLP</option>
                    <option value="USD" <?= $order['currency'] === 'USD' ? 'selected' : '' ?>>USD</option>
                    <option value="UF" <?= $order['currency'] === 'UF' ? 'selected' : '' ?>>UF</option>
                </select>
            </div>

            <div class="form-group">
                <label for="exchange_rate">Tasa de Cambio</label>
                <input type="number" id="exchange_rate" name="exchange_rate" step="0.0001" value="<?= $order['exchange_rate'] ?? 1 ?>">
            </div>

            <div class="form-group">
                <label for="due_date">Fecha de Vencimiento</label>
                <input type="date" id="due_date" name="due_date" value="<?= $order['due_date'] ?? '' ?>">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label for="notes">Notas</label>
            <textarea id="notes" name="notes" rows="3"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; margin-top: 24px;">Actualizar Orden de Compra</button>
    </form>
</div>