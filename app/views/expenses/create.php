<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Registrar Nuevo Gasto</h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;">Agrega un gasto para controlar tus finanzas.</p>
        </div>
        <a href="expenses.php" class="btn-secondary">Volver</a>
    </div>

    <form action="expenses.php?action=create" method="POST" enctype="multipart/form-data" class="glass-card" style="padding: 32px;">
        <?= \Core\Security::csrfField() ?>

        <div class="form-group">
            <label for="title">Título del Gasto *</label>
            <input type="text" id="title" name="title" required placeholder="Ej: Compra de insumos de oficina">
            <small class="form-help">Describe brevemente el gasto realizado.</small>
        </div>

        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" rows="3" placeholder="Detalles adicionales sobre el gasto..."></textarea>
            <small class="form-help">Proporciona más contexto sobre el gasto.</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="category">Categoría</label>
                <select id="category" name="category">
                    <option value="">Seleccionar categoría</option>
                    <option value="Oficina">Oficina</option>
                    <option value="Transporte">Transporte</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Tecnología">Tecnología</option>
                    <option value="Servicios">Servicios</option>
                    <option value="Suministros">Suministros</option>
                    <option value="Otros">Otros</option>
                </select>
                <small class="form-help">Clasifica el tipo de gasto.</small>
            </div>

            <div class="form-group">
                <label for="amount">Monto *</label>
                <input type="number" id="amount" name="amount" step="0.01" required placeholder="0.00">
                <small class="form-help">Monto del gasto en la moneda seleccionada.</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="currency">Moneda</label>
                <select id="currency" name="currency">
                    <option value="CLP">CLP</option>
                    <option value="USD">USD</option>
                    <option value="UF">UF</option>
                </select>
            </div>

            <div class="form-group">
                <label for="exchange_rate">Tasa de Cambio</label>
                <input type="number" id="exchange_rate" name="exchange_rate" step="0.0001" value="1" placeholder="1.0000">
            </div>

            <div class="form-group">
                <label for="date">Fecha *</label>
                <input type="date" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="supplier">Proveedor</label>
                <input type="text" id="supplier" name="supplier" placeholder="Nombre del proveedor">
            </div>

            <div class="form-group">
                <label for="payment_method">Método de Pago</label>
                <select id="payment_method" name="payment_method">
                    <option value="">Seleccionar método</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia</option>
                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                    <option value="Cheque">Cheque</option>
                    <option value="Webpay">Webpay</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="receipt">Comprobante (opcional)</label>
            <input type="file" id="receipt" name="receipt" accept="image/*,.pdf">
            <small class="form-help">Sube una imagen o PDF del comprobante de pago.</small>
        </div>

        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" id="tax_deductible" name="tax_deductible" value="1">
                <span>Gasto deducible de impuestos</span>
            </label>
            <small class="form-help">Marca si este gasto puede ser deducido en tus declaraciones de impuestos.</small>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; margin-top: 24px;">Registrar Gasto</button>
    </form>
</div>