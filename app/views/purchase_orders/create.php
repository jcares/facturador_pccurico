<div style="max-width: 1000px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Crear Orden de Compra</h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;">Registra una nueva orden de compra a proveedor.</p>
        </div>
        <a href="purchase_orders.php" class="btn-secondary">Volver</a>
    </div>

    <form action="purchase_orders.php?action=create" method="POST" class="glass-card" style="padding: 32px;">
        <?= \Core\Security::csrfField() ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div class="form-group">
                <label for="supplier_name">Nombre del Proveedor *</label>
                <input type="text" id="supplier_name" name="supplier_name" required placeholder="Ej: Distribuidora XYZ">
            </div>

            <div class="form-group">
                <label for="supplier_rut">RUT del Proveedor</label>
                <input type="text" id="supplier_rut" name="supplier_rut" placeholder="12.345.678-9">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div class="form-group">
                <label for="supplier_email">Email del Proveedor</label>
                <input type="email" id="supplier_email" name="supplier_email" placeholder="proveedor@email.com">
            </div>

            <div class="form-group">
                <label for="supplier_phone">Teléfono del Proveedor</label>
                <input type="tel" id="supplier_phone" name="supplier_phone" placeholder="+56 9 1234 5678">
            </div>

            <div class="form-group">
                <label for="number">Número de Orden *</label>
                <input type="text" id="number" name="number" required placeholder="OC-001">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label for="supplier_address">Dirección del Proveedor</label>
            <textarea id="supplier_address" name="supplier_address" rows="2" placeholder="Dirección completa del proveedor..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div class="form-group">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <option value="draft">Borrador</option>
                    <option value="sent">Enviada</option>
                    <option value="received">Recibida</option>
                    <option value="canceled">Cancelada</option>
                </select>
            </div>

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
                <label for="due_date">Fecha de Vencimiento</label>
                <input type="date" id="due_date" name="due_date">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label for="notes">Notas</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Notas adicionales sobre la orden..."></textarea>
        </div>

        <!-- Items Section -->
        <div style="border-top: 1px solid var(--glass-border); padding-top: 24px; margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; font-weight: 600;">Artículos</h3>
                <button type="button" id="add-item-btn" class="btn-secondary" style="padding: 8px 16px;">Agregar Artículo</button>
            </div>

            <div id="items-container">
                <!-- Items will be added here dynamically -->
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--glass-border);">
                <div>
                    <label style="font-weight: 600; color: var(--text-main);">Subtotal</label>
                    <div id="subtotal-display" style="font-size: 1.1rem; color: var(--text-muted);">$0</div>
                </div>
                <div>
                    <label style="font-weight: 600; color: var(--text-main);">IVA</label>
                    <div id="tax-display" style="font-size: 1.1rem; color: var(--text-muted);">$0</div>
                </div>
                <div>
                    <label style="font-weight: 600; color: var(--text-main);">Total</label>
                    <div id="total-display" style="font-size: 1.2rem; font-weight: 700; color: var(--primary);">$0</div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; margin-top: 24px;">Crear Orden de Compra</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 0;
    const container = document.getElementById('items-container');
    const addBtn = document.getElementById('add-item-btn');

    function createItemRow() {
        itemCount++;
        const row = document.createElement('div');
        row.className = 'item-row';
        row.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr; gap: 12px; align-items: end; margin-bottom: 16px; padding: 16px; background: rgba(255,255,255,0.02); border-radius: 8px;';
        
        row.innerHTML = `
            <div class="form-group" style="margin: 0;">
                <label>Producto *</label>
                <input type="text" name="items[${itemCount}][product_name]" required placeholder="Nombre del producto">
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Cantidad</label>
                <input type="number" name="items[${itemCount}][qty]" step="0.01" value="1" onchange="calculateTotals()">
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Precio Unit.</label>
                <input type="number" name="items[${itemCount}][price]" step="0.01" onchange="calculateTotals()">
            </div>
            <div class="form-group" style="margin: 0;">
                <label>Total</label>
                <input type="number" name="items[${itemCount}][total]" step="0.01" readonly style="background: var(--glass-bg);">
            </div>
            <button type="button" class="remove-item-btn" style="padding: 8px; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">×</button>
            <input type="hidden" name="items[${itemCount}][tax_rate]" value="0.19">
        `;
        
        container.appendChild(row);
        
        // Add remove functionality
        row.querySelector('.remove-item-btn').addEventListener('click', function() {
            row.remove();
            calculateTotals();
        });
        
        return row;
    }

    addBtn.addEventListener('click', createItemRow);

    // Add first item by default
    createItemRow();
});

function calculateTotals() {
    const rows = document.querySelectorAll('.item-row');
    let subtotal = 0;
    let tax = 0;
    
    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('input[name*="[qty]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
        const total = qty * price;
        const taxRate = 0.19; // IVA Chile
        
        row.querySelector('input[name*="[total]"]').value = total.toFixed(2);
        
        subtotal += total;
        tax += total * taxRate;
    });
    
    const total = subtotal + tax;
    
    document.getElementById('subtotal-display').textContent = '$' + subtotal.toLocaleString('es-CL');
    document.getElementById('tax-display').textContent = '$' + tax.toLocaleString('es-CL');
    document.getElementById('total-display').textContent = '$' + total.toLocaleString('es-CL');
    
    // Update hidden fields for form submission
    const subtotalInput = document.createElement('input');
    subtotalInput.type = 'hidden';
    subtotalInput.name = 'subtotal';
    subtotalInput.value = subtotal.toFixed(2);
    
    const taxInput = document.createElement('input');
    taxInput.type = 'hidden';
    taxInput.name = 'tax';
    taxInput.value = tax.toFixed(2);
    
    const totalInput = document.createElement('input');
    totalInput.type = 'hidden';
    totalInput.name = 'total';
    totalInput.value = total.toFixed(2);
    
    // Remove existing hidden fields
    document.querySelectorAll('input[name="subtotal"], input[name="tax"], input[name="total"]').forEach(el => el.remove());
    
    // Add new hidden fields
    document.querySelector('form').appendChild(subtotalInput);
    document.querySelector('form').appendChild(taxInput);
    document.querySelector('form').appendChild(totalInput);
}
</script>