<div class="glass-card" style="max-width: 1000px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--glass-border); padding-bottom: 20px;">
        <h2 style="font-weight: 800; margin: 0;">Nueva Factura</h2>
        <div>
            <a href="invoices.php" class="btn-primary" style="background: rgba(255,255,255,0.1); width: auto; padding: 10px 20px; color: var(--text-main); text-decoration: none;">Cancelar</a>
            <button type="submit" form="invoice-form" class="btn-primary" style="width: auto; padding: 10px 20px; margin-left: 10px;">Guardar Documento</button>
        </div>
    </div>

    <form action="invoices.php?action=store" method="POST" id="invoice-form">
        <?= \Core\Security::csrfField() ?>
        <!-- Encabezado de Factura -->
        <div class="main-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
            <div>
                <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px;">Datos del Cliente</h3>
                <div class="form-group">
                    <label>Seleccionar Cliente</label>
                    <select name="client_id" class="form-control" required style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                        <option value="">-- Seleccione un cliente --</option>
                        <?php foreach($clients as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['rut'] . ' - ' . $c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div>
                <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px;">Detalles del Documento</h3>
                <div class="flex-responsive" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Moneda Base</label>
                        <select name="currency" id="invoice-currency" class="form-control" onchange="calculateTotals()" style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                            <option value="CLP">Pesos Chilenos (CLP)</option>
                            <option value="USD">Dólar (USD)</option>
                            <option value="UF">Unidad de Fomento (UF)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Emisión</label>
                        <input type="date" name="issue_date" value="<?= date('Y-m-d') ?>" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Ítems -->
        <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px;">Líneas de Detalle</h3>
        <div class="table-container">
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;" id="items-table">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 12px 0; text-align: left; color: var(--text-muted);">Producto / Descripción</th>
                        <th style="padding: 12px; text-align: right; color: var(--text-muted); width: 100px;">Cantidad</th>
                        <th style="padding: 12px; text-align: right; color: var(--text-muted); width: 150px;">Precio Neto</th>
                        <th style="padding: 12px; text-align: right; color: var(--text-muted); width: 150px;">Total</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    <!-- Se inyecta la primera fila via JS -->
                </tbody>
            </table>
        </div>

        <button type="button" onclick="addRow()" style="background: rgba(16,185,129,0.1); color: var(--primary); border: 1px dashed var(--primary); padding: 10px 20px; border-radius: 8px; cursor: pointer; width: 100%; margin-bottom: 40px; font-weight: 600;">
            + Añadir Línea
        </button>

        <!-- Totales -->
        <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--glass-border); padding-top: 20px;">
            <div style="width: 300px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--text-muted);">
                    <span>Subtotal Neto</span>
                    <span id="subtotal-display">$0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--text-muted);">
                    <span>IVA (19%)</span>
                    <span id="tax-display">$0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 1.2rem; font-weight: 800; color: var(--primary);">
                    <span>Total a Pagar</span>
                    <span id="total-display">$0</span>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Opciones de productos para JS -->
<select id="product-template" style="display:none;">
    <option value="">Seleccione o escriba...</option>
    <?php foreach($products as $p): ?>
        <option value="<?= $p['id'] ?>" data-price="<?= $p['price'] ?>" data-currency="<?= $p['currency'] ?? 'CLP' ?>">
            <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['sku']) ?>) - <?= $p['currency'] ?? 'CLP' ?>
        </option>
    <?php endforeach; ?>
</select>

<script>
    const productOptions = document.getElementById('product-template').innerHTML;
    const exchangeRates = <?= json_encode($rates) ?>;

    function addRow() {
        const tbody = document.getElementById('items-body');
        const tr = document.createElement('tr');
        tr.style.borderBottom = "1px solid rgba(255,255,255,0.05)";
        
        tr.innerHTML = `
            <td style="padding: 12px 0;">
                <select name="product_id[]" class="form-control prod-select" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px;" onchange="updatePrice(this)">
                    ${productOptions}
                </select>
            </td>
            <td style="padding: 12px;">
                <input type="number" name="qty[]" value="1" min="1" class="form-control qty-input" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px; text-align: right;" oninput="calculateRow(this)">
            </td>
            <td style="padding: 12px;">
                <input type="number" name="price[]" value="0" step="0.01" class="form-control price-input" required style="width: 100%; padding: 10px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px; text-align: right;" oninput="calculateRow(this)">
            </td>
            <td style="padding: 12px; text-align: right; font-weight: 600; color: var(--text-main);" class="row-total-display">
                $0
            </td>
            <td style="padding: 12px; text-align: right;">
                <button type="button" onclick="removeRow(this)" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 5px;"><i data-lucide="trash-2" style="width:16px;"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calculateTotals();
    }

    function updatePrice(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const price = selectedOption.getAttribute('data-price');
        const currency = selectedOption.getAttribute('data-currency') || 'CLP';
        const tr = selectElement.closest('tr');
        const priceInput = tr.querySelector('.price-input');
        const invCurrency = document.getElementById('invoice-currency').value;
        
        if (price) {
            let finalPrice = parseFloat(price);
            // If product currency is different from invoice currency, convert
            if (currency !== invCurrency) {
                const rateToClp = exchangeRates[currency] || 1;
                const rateFromClp = exchangeRates[invCurrency] || 1;
                finalPrice = (finalPrice * rateToClp) / rateFromClp;
            }
            priceInput.value = finalPrice.toFixed(2);
            calculateRow(priceInput);
        }
    }

    function calculateRow(element) {
        const tr = element.closest('tr');
        const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
        const price = parseFloat(tr.querySelector('.price-input').value) || 0;
        const total = qty * price;
        const invCurrency = document.getElementById('invoice-currency').value;
        
        const formatter = new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: invCurrency === 'CLP' ? 'CLP' : 'USD', // JS doesn't have UF, we use USD as proxy for formatting
            minimumFractionDigits: invCurrency === 'CLP' ? 0 : 2
        });

        tr.querySelector('.row-total-display').innerText = formatter.format(total).replace('USD', 'US$');
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        const invCurrency = document.getElementById('invoice-currency').value;
        
        document.querySelectorAll('#items-body tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.qty-input').value) || 0;
            const price = parseFloat(tr.querySelector('.price-input').value) || 0;
            subtotal += (qty * price);
        });

        // Convert to CLP for tax calculation if needed, but here we just show totals in base currency
        const tax = subtotal * 0.19;
        const total = subtotal + tax;

        const formatter = new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: invCurrency === 'CLP' ? 'CLP' : 'USD',
            minimumFractionDigits: invCurrency === 'CLP' ? 0 : 2
        });

        document.getElementById('subtotal-display').innerText = formatter.format(subtotal).replace('USD', 'US$');
        document.getElementById('tax-display').innerText = formatter.format(tax).replace('USD', 'US$');
        document.getElementById('total-display').innerText = formatter.format(total).replace('USD', 'US$');
    }

    // Inicializar con una fila
    addRow();
</script>
