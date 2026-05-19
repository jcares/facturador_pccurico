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

<div class="glass-card pos-card max-w-1080 m-auto">
    <div class="flex-between mb-20 border-b pb-20">
        <h2 class="font-800 m-0">Nueva Venta</h2>
        <div class="d-flex gap-10 flex-wrap justify-end">
            <a href="invoices.php" class="btn-secondary">Cancelar</a>
            <button type="submit" form="invoice-form" name="save_action" value="save" class="btn-primary">Solo Guardar</button>
            <button type="submit" form="invoice-form" name="save_action" value="send" class="btn-success">Guardar y Enviar</button>
        </div>
    </div>

    <?php if (empty($clients)): ?>
        <div class="alert alert-error">No hay clientes disponibles. Crea un cliente antes de vender.</div>
    <?php endif; ?>

    <?php if (empty($productData)): ?>
        <div class="alert alert-error">No hay productos disponibles. Crea productos antes de vender.</div>
    <?php endif; ?>

    <form action="invoices.php?action=store" method="POST" id="invoice-form" novalidate>
        <?= \Core\Security::csrfField() ?>

        <div class="form-row mb-20">
            <section>
                <h3 class="section-subtitle">Datos del Cliente</h3>
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
                <h3 class="section-subtitle">Detalles del Documento</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="invoice-currency">Moneda Base</label>
                        <select name="currency" id="invoice-currency" class="form-control">
                            <option value="CLP">CLP</option>
                            <option value="USD">USD</option>
                            <option value="UF">UF</option>
                        </select>
                        <small id="rate-hint" class="form-help"></small>
                    </div>
                    <div class="form-group">
                        <label for="due-date">Vencimiento</label>
                        <input type="date" name="due_date" id="due-date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="issue-date">Emisión</label>
                        <input type="date" name="issue_date" id="issue-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
            </section>
        </div>

        <section class="config-panel">
            <label class="config-toggle-lbl">
                <input type="checkbox" name="make_recurring" id="make-recurring" value="1" class="check-lg">
                Crear como factura recurrente
            </label>
            <div id="recurring-panel" class="mt-20" style="display: none;">
                <div class="form-row">
                    <div class="form-group">
                        <label for="recurring-frequency">Frecuencia</label>
                        <select name="recurring_frequency" id="recurring-frequency" class="form-control">
                            <option value="monthly">Mensual</option>
                            <option value="weekly">Semanal</option>
                            <option value="quarterly">Trimestral</option>
                            <option value="yearly">Anual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="recurring-start-date">Primera recurrencia</label>
                        <input type="date" name="recurring_start_date" id="recurring-start-date" class="form-control" value="<?= date('Y-m-d', strtotime('+1 month')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="recurring-due-days">Días para vencer</label>
                        <input type="number" name="recurring_due_days" id="recurring-due-days" class="form-control" value="30" min="0" max="365">
                    </div>
                    <div class="form-group">
                        <label for="recurring-remaining-cycles">Ciclos restantes</label>
                        <input type="number" name="recurring_remaining_cycles" id="recurring-remaining-cycles" class="form-control" min="1" placeholder="Sin límite">
                    </div>
                </div>
            </div>
        </section>

        <h3 class="section-subtitle">Líneas de Detalle</h3>
        <div class="table-container mb-20">
            <table id="items-table" class="table-clean">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-right w-110">Cantidad</th>
                        <th class="text-right w-160">Precio Neto</th>
                        <th class="text-right w-160">Total</th>
                        <th class="w-52"></th>
                    </tr>
                </thead>
                <tbody id="items-body">
                    <tr class="invoice-item-row">
                        <td>
                            <select name="product_id[]" class="form-control prod-select" aria-label="Producto de la línea">
                                <?php $renderProductOptions(); ?>
                            </select>
                            <div class="row-rate-display"></div>
                        </td>
                        <td>
                            <input type="number" name="qty[]" value="1" min="0.01" step="0.01" class="form-control qty-input text-right" aria-label="Cantidad de la línea">
                        </td>
                        <td>
                            <input type="number" name="price[]" value="0" step="0.01" min="0" class="form-control price-input text-right" aria-label="Precio neto de la línea">
                        </td>
                        <td class="text-right">
                            <div class="row-total-display highlight">$0</div>
                        </td>
                        <td class="text-right">
                            <button type="button" class="remove-line-btn btn-icon text-danger" aria-label="Eliminar línea" title="Eliminar línea">
                                <i data-lucide="trash-2" class="icon-md"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="button" id="add-line-btn" class="btn-secondary btn-dashed w-full mb-30">
            + Añadir Línea
        </button>

        <div class="totals-section">
            <div class="totals-wrapper">
                <div class="flex-between mb-10 text-muted">
                    <span>Subtotal Neto</span>
                    <span id="subtotal-display">$0</span>
                </div>
                <div class="flex-between mb-10 text-muted">
                    <span>IVA (19%)</span>
                    <span id="tax-display">$0</span>
                </div>
                <div class="flex-between total-row">
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
        row.style.borderBottom = '1px solid var(--panel-border)';
        row.innerHTML = `
            <td class="py-12 px-0">
                <select name="product_id[]" class="form-control prod-select" aria-label="Producto de la linea">${optionHtml()}</select>
                <div class="row-rate-display"></div>
            </td>
            <td class="p-12">
                <input type="number" name="qty[]" value="1" min="0.01" step="0.01" class="form-control qty-input text-right" aria-label="Cantidad de la linea">
            </td>
            <td class="p-12">
                <input type="number" name="price[]" value="0" step="0.01" min="0" class="form-control price-input text-right" aria-label="Precio neto de la linea">
            </td>
            <td class="p-12 text-right">
                <div class="row-total-display font-700 text-main">$0</div>
            </td>
            <td class="p-12 text-right">
                <button type="button" class="remove-line-btn btn-icon text-danger p-6" aria-label="Eliminar linea" title="Eliminar linea">
                    <i data-lucide="trash-2" class="icon-md"></i>
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
