<div class="main-grid" style="display: flex; gap: 30px;">
    <!-- Lista de Productos -->
    <div class="glass-card" style="flex: 2;">
        <h3 style="margin-bottom: 20px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="package"></i> Inventario de Productos
        </h3>
        <?php if(empty($products)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i data-lucide="inbox" style="width: 40px; height: 40px; margin-bottom: 10px; opacity: 0.3;"></i>
                <p>No hay productos registrados aún.</p>
            </div>
        <?php else: ?>
        <div class="table-container">
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">SKU</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Producto / Categoría</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Precio</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 15px 12px; font-family: monospace; color: var(--primary);"><?= htmlspecialchars($p['sku'] ?: 'S/N') ?></td>
                            <td style="padding: 15px 12px;">
                                <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($p['name']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($p['category_name'] ?: 'Sin Categoría') ?></div>
                            </td>
                            <td style="padding: 15px 12px;">
                                <div style="font-weight: 700; color: var(--primary);">
                                    <?= ($p['currency'] ?? 'CLP') === 'CLP' ? '$' : ($p['currency'] ?? 'CLP') . ' ' ?><?= number_format($p['price'], 0, ',', '.') ?>
                                </div>
                            </td>
                            <td style="padding: 15px 12px;">
                                <span style="background: <?= $p['stock'] > 0 ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' ?>; color: <?= $p['stock'] > 0 ? '#10b981' : '#ef4444' ?>; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 700;">
                                    <?= $p['stock'] ?> <span style="font-weight: 400; opacity: 0.7;">unds.</span>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulario Crear -->
    <div class="glass-card" style="flex: 1; align-self: flex-start; padding: 25px;">
        <h3 style="margin-bottom: 25px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="plus-circle" style="color: var(--primary);"></i> Nuevo Producto
        </h3>
        <form action="products.php?action=store" method="POST">
            <?= \Core\Security::csrfField() ?>
            <div class="form-group">
                <label>Nombre del Producto *</label>
                <input type="text" name="name" required placeholder="Ej: Servicio de Mantenimiento">
            </div>
            <div class="form-group">
                <label>SKU / Código Interno</label>
                <input type="text" name="sku" placeholder="AUTO-GEN">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Categoría</label>
                    <select name="category_id">
                        <option value="">Sin Categoría</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Moneda</label>
                    <select name="currency">
                        <option value="CLP">CLP ($)</option>
                        <option value="USD">USD (US$)</option>
                        <option value="UF">UF (Unidad Fom.)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Precio Neto *</label>
                <input type="number" step="0.01" name="price" required placeholder="0.00">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Stock Inicial</label>
                    <input type="number" name="stock" value="0">
                </div>
                <div class="form-group">
                    <label>Tasa IVA</label>
                    <input type="number" step="0.01" name="tax_rate" value="0.19" readonly style="background: rgba(0,0,0,0.2); opacity: 0.7;">
                </div>
            </div>
            
            <button type="submit" class="btn-primary" style="margin-top: 15px; width: 100%;">
                <i data-lucide="save" style="width: 18px;"></i> Guardar Producto
            </button>
        </form>
    </div>
</div>
