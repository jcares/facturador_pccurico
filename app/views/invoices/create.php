<?php
$productData = array_map(static function ($product) {
    return [
        'id' => (int)$product['id'],
        'name' => (string)$product['name'],
        'sku' => (string)($product['sku'] ?? ''),
        'price' => (float)$product['price'],
        'currency' => (string)($product['currency'] ?? 'CLP'),
        'price_unit' => (string)($product['price_unit'] ?? 'unit'),
    ];
}, $products ?? []);

$renderProductOptions = static function () use ($productData): void {
    echo '<option value="">Seleccione producto...</option>';
    foreach ($productData as $product) {
        $unitLabel = $product['price_unit'] === 'meter' ? 'metro' : 'unidad';
        $label = trim($product['name'] . ' (' . $product['sku'] . ') - ' . $product['currency'] . ' / ' . $unitLabel);
        echo '<option value="' . (int)$product['id'] . '" data-price="' . htmlspecialchars((string)$product['price'], ENT_QUOTES, 'UTF-8') . '" data-currency="' . htmlspecialchars($product['currency'], ENT_QUOTES, 'UTF-8') . '" data-price-unit="' . htmlspecialchars($product['price_unit'], ENT_QUOTES, 'UTF-8') . '">';
        echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        echo '</option>';
    }
};
?>

<div class="glass-card pos-card" style="max-width: 1080px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 30px; border-bottom: 1px solid var(--glass-border); padding-bottom: 20px;">
        <h2 style="font-weight: 800; margin: 0;">Nueva Venta</h2>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end;">
            <a href="invoices.php" class="btn-secondary" style="text-decoration: none;">Cancelar</a>
            <button type="submit" form="invoice-form" name="save_action" value="save" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 20px;">Solo Guardar</button>
            <button type="submit" form="invoice-form" name="save_action" value="send" class="btn-primary" style="width: auto; margin-top: 0; padding: 10px 20px; background: var(--success, #10b981);">Guardar y Enviar</button>
        </div>
    </div>

    <?php if (empty($clients)): ?>
        <div class="alert alert-warning">No hay clientes disponibles. Crea un cliente antes de vender.</div>
    <?php endif; ?>

    <?php if (empty($productData)): ?>
        <div class="alert alert-warning">No hay productos disponibles. Crea productos antes de vender.</div>
    <?php endif; ?>

    <form action="invoices.php?action=store" method="POST" id="invoice-form" novalidate>
        <?= \Core\Security::csrfField() ?>

        <div class="main-grid" style="display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 32px; margin-bottom: 32px;">
            <section>
                <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px;">Datos del Cliente</h3>
                <div class="form-group">
                    <label for="client-id">Seleccionar Cliente</label>
                    <select name="client_id" id="client-id" class="form-control" required>
                        <option value="">-- Seleccione un cliente --</option>
                        <?php foreach (($clients ?? []) as $client): ?>
                            <option value="<?= (int)$client['id'] ?>">
                                <?= htmlspecialchars($client['rut'] . ' - ' . ($client['business_name'] ?? $client['name'] ?? 'Cliente'), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </section>

            <section>
                <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px;">Detalles del Documento</h3>
                <div class="flex-responsive" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label for="invoice-currency">Moneda Base</label>
                        <select name="currency" id="invoice-currency" class="form-control">
                            <option value="CLP">CLP</option>
                            <option value="USD">USD</option>
                            <option value="UF">UF</option>
                        </select>
                        <small id="rate-hint" style="display: block; color: var(--text-muted); margin-top: 6px;"></small>
                    </div>
                    <div class="form-group">
                        <label for="due-date">Vencimiento</label>
                        <input type="date" name="due_date" id="due-date" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="issue-date">Emision</label>
                        <input type="date" name="issue_date" id="issue-date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </section>
        </div>

        <section style="border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03); border-radius: 12px; padding: 18px; margin-bottom: 28px;">
            <label style="display: flex; align-items: center; gap: 12px; margin: 0; color: var(--text-main); font-weight: 700;">
                <input type="checkbox" name="make_recurring" id="make-recurring" value="1" style="width: 18px; height: 18px; accent-color: var(--primary);">
                Crear como factura recurrente
            </label>
            <div id="recurring-panel" style="display: none; margin-top: 18px;">
                <div class="flex-responsive" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="recurring-frequency">Frecuencia</label>
                        <select name="recurring_frequency" id="recurring-frequency">
                            <option value="monthly">Mensual</option>
                            <option value="weekly">Semanal</option>
                            <option value="quarterly">Trimestral</option>
                            <option value="yearly">Anual</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="recurring-start-date">Primera recurrencia</label>
                        <input type="date" name="recurring_start_date" id="recurring-start-date" value="<?= date('Y-m-d', strtotime('+1 month')) ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="recurring-due-days">Dias para vencer</label>
                        <input type="number" name="recurring_due_days" id="recurring-due-days" value="30" min="0" max="365">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="recurring-remaining-cycles">Ciclos restantes</label>
                        <input type="number" name="recurring_remaining_cycles" id="recurring-remaining-cycles" min="1" placeholder="Sin limite">
                    </div>
                </div>
            </div>
        </section>

        <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px;">Lineas de Detalle</h3>
        <div class="table-container invoice-items-container">
            <table id="items-table" style="width: 100%; border-collapse: collapse; margin-bottom: 18px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 12px 0; text-align: left; color: var(--text-muted); min-width: 260px;">Producto</th>
                        <th style="padding: 12px; text-align: right; color: var(--text-muted); width: 110px;">Cantidad</th>
                        <th style="padding: 12px; text-align: right; color: var(--text-muted); width: 160px;">Precio Neto</th>
                        <th style="padding: 12px; text-align: right; color: var(--text-muted); width: 160px;">Total</th>
                        <th style="width: 52px;"></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    <tr class="invoice-item-row" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 12px 0;">
                            <select name="product_id[]" class="form-control prod-select" aria-label="Producto de la linea">
                                <?php $renderProductOptions(); ?>
                            </select>
                            <div class="row-rate-display" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;"></div>
                        </td>
                        <td style="padding: 12px;">
                            <input type="number" name="qty[]" value="1" min="0.01" step="0.01" class="form-control qty-input" aria-label="Cantidad de la linea" style="text-align: right;">
                        </td>
                        <td style="padding: 12px;">
                            <input type="number" name="price[]" value="0" step="0.01" min="0" class="form-control price-input" aria-label="Precio neto de la linea" style="text-align: right;">
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <div class="row-total-display" style="font-weight: 700; color: var(--text-main);">$0</div>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <button type="button" class="remove-line-btn" aria-label="Eliminar linea" title="Eliminar linea" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 6px;">
                                <i data-lucide="trash-2" style="width:16px;"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="button" id="add-line-btn" class="btn-secondary" style="width: 100%; margin-bottom: 32px; border-style: dashed; color: var(--primary);">
            + Anadir Linea
        </button>

        <div style="display: flex; justify-content: flex-end; border-top: 1px solid var(--glass-border); padding-top: 20px;">
            <div style="width: min(100%, 320px);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--text-muted);">
                    <span>Subtotal Neto</span>
                    <span id="subtotal-display">$0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; color: var(--text-muted);">
                    <span>IVA (19%)</span>
                    <span id="tax-display">$0</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); font-size: 1.2rem; font-weight: 800; color: var(--primary);">
                    <span>Total</span>
                    <span id="total-display">$0</span>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(() => {
    const products = <?= json_encode($productData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]' ?>;
    const rates = Object.assign({ CLP: 1, USD: 1, UF: 1 }, <?= json_encode($rates ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '{}' ?>);
    const form = document.getElementById('invoice-form');
    const body = document.getElementById('items-body');
    const currencyField = document.getElementById('invoice-currency');
    const addButton = document.getElementById('add-line-btn');
    const recurringToggle = document.getElementById('make-recurring');
    const recurringPanel = document.getElementById('recurring-panel');
    const rateHint = document.getElementById('rate-hint');

    function decimals(currency) {
        return currency === 'CLP' ? 0 : 2;
    }

    function money(amount, currency) {
        const value = Number(amount) || 0;
        if (currency === 'UF') {
            return 'UF ' + value.toLocaleString('es-CL', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return new Intl.NumberFormat('es-CL', {
            style: 'currency',
            currency: currency === 'USD' ? 'USD' : 'CLP',
            minimumFractionDigits: decimals(currency),
            maximumFractionDigits: decimals(currency)
        }).format(value);
    }

    function convert(amount, fromCurrency, toCurrency) {
        const fromRate = Number(rates[fromCurrency] || 1);
        const toRate = Number(rates[toCurrency] || 1);
        return toRate > 0 ? ((Number(amount) || 0) * fromRate) / toRate : (Number(amount) || 0);
    }

    function productById(id) {
        return products.find((product) => String(product.id) === String(id)) || null;
    }

    function optionHtml() {
        return '<option value="">Seleccione producto...</option>' + products.map((product) => {
            const unitLabel = product.price_unit === 'meter' ? 'metro' : 'unidad';
            const label = `${product.name} (${product.sku}) - ${product.currency} / ${unitLabel}`;
            return `<option value="${product.id}" data-price="${product.price}" data-currency="${product.currency}" data-price-unit="${product.price_unit}">${escapeHtml(label)}</option>`;
        }).join('');
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function createRow() {
        const row = document.createElement('tr');
        row.className = 'invoice-item-row';
        row.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
        row.innerHTML = `
            <td style="padding: 12px 0;">
                <select name="product_id[]" class="form-control prod-select" aria-label="Producto de la linea">${optionHtml()}</select>
                <div class="row-rate-display" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 6px;"></div>
            </td>
            <td style="padding: 12px;">
                <input type="number" name="qty[]" value="1" min="0.01" step="0.01" class="form-control qty-input" aria-label="Cantidad de la linea" style="text-align: right;">
            </td>
            <td style="padding: 12px;">
                <input type="number" name="price[]" value="0" step="0.01" min="0" class="form-control price-input" aria-label="Precio neto de la linea" style="text-align: right;">
            </td>
            <td style="padding: 12px; text-align: right;">
                <div class="row-total-display" style="font-weight: 700; color: var(--text-main);">$0</div>
            </td>
            <td style="padding: 12px; text-align: right;">
                <button type="button" class="remove-line-btn" aria-label="Eliminar linea" title="Eliminar linea" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 6px;">
                    <i data-lucide="trash-2" style="width:16px;"></i>
                </button>
            </td>
        `;
        return row;
    }

    function updateRow(row, useCatalogPrice = false) {
        const currency = currencyField.value || 'CLP';
        const select = row.querySelector('.prod-select');
        const qtyField = row.querySelector('.qty-input');
        const priceField = row.querySelector('.price-input');
        const totalDisplay = row.querySelector('.row-total-display');
        const rateDisplay = row.querySelector('.row-rate-display');
        const product = productById(select.value);

        if (product && (useCatalogPrice || Number(priceField.value) <= 0)) {
            const converted = convert(product.price, product.currency, currency);
            priceField.value = converted.toFixed(decimals(currency));
            rateDisplay.textContent = product.currency === currency
                ? `Producto en ${currency} por ${product.price_unit === 'meter' ? 'metro' : 'unidad'}`
                : `${money(product.price, product.currency)} por ${product.price_unit === 'meter' ? 'metro' : 'unidad'} convertido a ${currency}`;
        } else if (!product) {
            rateDisplay.textContent = '';
        }

        const qty = Number(qtyField.value) || 0;
        const price = Number(priceField.value) || 0;
        totalDisplay.textContent = money(qty * price, currency);
    }

    function updateTotals() {
        const currency = currencyField.value || 'CLP';
        let subtotal = 0;
        body.querySelectorAll('.invoice-item-row, #items-body tr').forEach((row) => {
            updateRow(row);
            const qty = Number(row.querySelector('.qty-input').value) || 0;
            const price = Number(row.querySelector('.price-input').value) || 0;
            subtotal += qty * price;
        });

        const tax = subtotal * 0.19;
        const total = subtotal + tax;
        document.getElementById('subtotal-display').textContent = money(subtotal, currency);
        document.getElementById('tax-display').textContent = money(tax, currency);
        document.getElementById('total-display').textContent = money(total, currency);

        const rate = Number(rates[currency] || 1);
        rateHint.textContent = currency === 'CLP' ? 'Cobro en pesos chilenos.' : `Valor usado: 1 ${currency} = ${money(rate, 'CLP')}`;
    }

    // Compatibilidad con HTML antiguo que pueda quedar en cache/opcache con handlers inline.
    window.updatePrice = function (selectElement) {
        const row = selectElement ? selectElement.closest('.invoice-item-row, tr') : null;
        if (!row) return;
        updateRow(row, true);
        updateTotals();
    };

    window.calculateRow = function (fieldElement) {
        const row = fieldElement ? fieldElement.closest('.invoice-item-row, tr') : null;
        if (!row) return;
        updateRow(row);
        updateTotals();
    };

    window.refreshAllPrices = function () {
        body.querySelectorAll('.invoice-item-row, #items-body tr').forEach((row) => updateRow(row, true));
        updateTotals();
    };

    window.addRow = function () {
        body.appendChild(createRow());
        if (typeof lucide !== 'undefined') lucide.createIcons();
        updateTotals();
    };

    function validRows() {
        return Array.from(body.querySelectorAll('.invoice-item-row, #items-body tr')).filter((row) => {
            const productId = row.querySelector('.prod-select').value;
            const qty = Number(row.querySelector('.qty-input').value) || 0;
            return productId !== '' && qty > 0;
        });
    }

    addButton.addEventListener('click', () => {
        window.addRow();
    });

    body.addEventListener('change', (event) => {
        const row = event.target.closest('.invoice-item-row');
        if (!row) return;
        updateRow(row, event.target.classList.contains('prod-select'));
        updateTotals();
    });

    body.addEventListener('input', (event) => {
        if (!event.target.matches('.qty-input, .price-input')) return;
        const row = event.target.closest('.invoice-item-row');
        updateRow(row);
        updateTotals();
    });

    body.addEventListener('click', (event) => {
        const removeButton = event.target.closest('.remove-line-btn');
        if (!removeButton) return;
        const rows = body.querySelectorAll('.invoice-item-row, #items-body tr');
        if (rows.length <= 1) {
            rows[0].querySelector('.prod-select').value = '';
            rows[0].querySelector('.qty-input').value = '1';
            rows[0].querySelector('.price-input').value = '0';
            rows[0].querySelector('.row-rate-display').textContent = '';
        } else {
            removeButton.closest('.invoice-item-row').remove();
        }
        updateTotals();
    });

    currencyField.addEventListener('change', () => {
        body.querySelectorAll('.invoice-item-row, #items-body tr').forEach((row) => updateRow(row, true));
        updateTotals();
    });

    recurringToggle.addEventListener('change', () => {
        recurringPanel.style.display = recurringToggle.checked ? 'block' : 'none';
    });

    form.addEventListener('submit', (event) => {
        if (!document.getElementById('client-id').value) {
            event.preventDefault();
            Toast.show('Selecciona un cliente.', 'error');
            return;
        }

        if (validRows().length === 0) {
            event.preventDefault();
            Toast.show('Agrega al menos un producto con cantidad.', 'error');
            return;
        }
    });

    updateTotals();
    if (typeof lucide !== 'undefined') lucide.createIcons();
})();
</script>
