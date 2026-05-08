<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Editar Gasto</h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;">Modifica los detalles del gasto.</p>
        </div>
        <a href="expenses.php" class="btn-secondary">Volver</a>
    </div>

    <form action="expenses.php?action=edit&id=<?= $expense['id'] ?>" method="POST" enctype="multipart/form-data" class="glass-card" style="padding: 32px;">
        <?= \Core\Security::csrfField() ?>

        <div class="form-group">
            <label for="title">Título del Gasto *</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($expense['title']) ?>" required>
            <small class="form-help">Describe brevemente el gasto realizado.</small>
        </div>

        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($expense['description'] ?? '') ?></textarea>
            <small class="form-help">Proporciona más contexto sobre el gasto.</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="category">Categoría</label>
                <select id="category" name="category">
                    <option value="">Seleccionar categoría</option>
                    <option value="Oficina" <?= $expense['category'] === 'Oficina' ? 'selected' : '' ?>>Oficina</option>
                    <option value="Transporte" <?= $expense['category'] === 'Transporte' ? 'selected' : '' ?>>Transporte</option>
                    <option value="Marketing" <?= $expense['category'] === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                    <option value="Tecnología" <?= $expense['category'] === 'Tecnología' ? 'selected' : '' ?>>Tecnología</option>
                    <option value="Servicios" <?= $expense['category'] === 'Servicios' ? 'selected' : '' ?>>Servicios</option>
                    <option value="Suministros" <?= $expense['category'] === 'Suministros' ? 'selected' : '' ?>>Suministros</option>
                    <option value="Otros" <?= $expense['category'] === 'Otros' ? 'selected' : '' ?>>Otros</option>
                </select>
                <small class="form-help">Clasifica el tipo de gasto.</small>
            </div>

            <div class="form-group">
                <label for="amount">Monto *</label>
                <input type="number" id="amount" name="amount" step="0.01" value="<?= $expense['amount'] ?>" required>
                <small class="form-help">Monto del gasto en la moneda seleccionada.</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="currency">Moneda</label>
                <select id="currency" name="currency">
                    <option value="CLP" <?= $expense['currency'] === 'CLP' ? 'selected' : '' ?>>CLP</option>
                    <option value="USD" <?= $expense['currency'] === 'USD' ? 'selected' : '' ?>>USD</option>
                    <option value="UF" <?= $expense['currency'] === 'UF' ? 'selected' : '' ?>>UF</option>
                </select>
            </div>

            <div class="form-group">
                <label for="exchange_rate">Tasa de Cambio</label>
                <input type="number" id="exchange_rate" name="exchange_rate" step="0.0001" value="<?= $expense['exchange_rate'] ?? 1 ?>">
            </div>

            <div class="form-group">
                <label for="date">Fecha *</label>
                <input type="date" id="date" name="date" value="<?= $expense['date'] ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="supplier">Proveedor</label>
                <input type="text" id="supplier" name="supplier" value="<?= htmlspecialchars($expense['supplier'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="payment_method">Método de Pago</label>
                <select id="payment_method" name="payment_method">
                    <option value="">Seleccionar método</option>
                    <option value="Efectivo" <?= $expense['payment_method'] === 'Efectivo' ? 'selected' : '' ?>>Efectivo</option>
                    <option value="Transferencia" <?= $expense['payment_method'] === 'Transferencia' ? 'selected' : '' ?>>Transferencia</option>
                    <option value="Tarjeta de Crédito" <?= $expense['payment_method'] === 'Tarjeta de Crédito' ? 'selected' : '' ?>>Tarjeta de Crédito</option>
                    <option value="Tarjeta de Débito" <?= $expense['payment_method'] === 'Tarjeta de Débito' ? 'selected' : '' ?>>Tarjeta de Débito</option>
                    <option value="Cheque" <?= $expense['payment_method'] === 'Cheque' ? 'selected' : '' ?>>Cheque</option>
                    <option value="Webpay" <?= $expense['payment_method'] === 'Webpay' ? 'selected' : '' ?>>Webpay</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="receipt">Comprobante (opcional)</label>
            <input type="file" id="receipt" name="receipt" accept="image/*,.pdf">
            <?php if(!empty($expense['receipt_file'])): ?>
                <small class="form-help">Archivo actual: <?= htmlspecialchars($expense['receipt_file']) ?></small>
            <?php else: ?>
                <small class="form-help">Sube una imagen o PDF del comprobante de pago.</small>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="tax_deductible" name="tax_deductible" value="1" <?= $expense['tax_deductible'] ? 'checked' : '' ?>>
                <span>Gasto deducible de impuestos</span>
            </label>
            <small class="form-help">Marca si este gasto puede ser deducido en tus declaraciones de impuestos.</small>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; margin-top: 24px;">Actualizar Gasto</button>
    </form>
</div>