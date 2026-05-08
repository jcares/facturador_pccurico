<div class="main-grid" style="display: flex; gap: 30px;">
    <div class="glass-card" style="flex: 2;">
        <h3 style="margin-bottom: 20px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="package"></i> Inventario de Productos
        </h3>
        <?php if(empty($products)): ?>
            <div style="text-align: center; padding: 40px; color: var(--text-muted);">
                <i data-lucide="inbox" style="width: 40px; height: 40px; margin-bottom: 10px; opacity: 0.3;"></i>
                <p>No hay productos registrados aun.</p>
            </div>
        <?php else: ?>
        <div class="table-container">
            <table style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">SKU</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Producto / Categoria</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Precio Definido</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Equivalente CLP</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Medida</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem;">Stock</th>
                        <th style="padding: 12px; color: var(--text-muted); font-size: 0.85rem; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                        <?php
                            $priceUnit = $p['price_unit'] ?? 'unit';
                            $unitLabel = $priceUnit === 'meter' ? 'Metro' : 'Unidad';
                            $unitSuffix = $priceUnit === 'meter' ? 'mts.' : 'unds.';
                            $categoryLabel = !empty($p['parent_category_name']) ? $p['parent_category_name'] . ' / ' . $p['category_name'] : ($p['category_name'] ?: 'Sin Categoria');
                        ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 15px 12px; font-family: monospace; color: var(--primary);"><?= htmlspecialchars($p['sku'] ?: 'S/N') ?></td>
                            <td style="padding: 15px 12px;">
                                <div style="font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($p['name']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($categoryLabel) ?></div>
                            </td>
                            <td style="padding: 15px 12px;">
                                <div style="font-weight: 700; color: var(--primary);">
                                    <?php $currency = $p['currency'] ?? 'CLP'; ?>
                                    <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$p['price'], $currency === 'CLP' ? 0 : 2, ',', '.') ?>
                                </div>
                            </td>
                            <td style="padding: 15px 12px;">
                                <?php
                                    $rate = (float)($rates[$currency] ?? 1);
                                    $priceClp = ceil((float)$p['price'] * $rate);
                                ?>
                                <div style="font-weight: 700; color: var(--text-main);">$<?= number_format($priceClp, 0, ',', '.') ?></div>
                                <?php if ($currency !== 'CLP'): ?>
                                    <div style="font-size: 0.72rem; color: var(--text-muted);">1 <?= htmlspecialchars($currency) ?> = $<?= number_format($rate, 0, ',', '.') ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px 12px;">
                                <span class="status-badge"><?= $unitLabel ?></span>
                            </td>
                            <td style="padding: 15px 12px;">
                                <span style="background: <?= $p['stock'] > 0 ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' ?>; color: <?= $p['stock'] > 0 ? '#10b981' : '#ef4444' ?>; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 700;">
                                    <?= number_format((float)$p['stock'], $priceUnit === 'meter' ? 2 : 0, ',', '.') ?> <span style="font-weight: 400; opacity: 0.7;"><?= $unitSuffix ?></span>
                                </span>
                            </td>
                            <td style="padding: 15px 12px; text-align: right;">
                                <a href="products.php?edit=<?= (int)$p['id'] ?>" style="color: var(--primary); text-decoration: none;" title="Editar">
                                    <i data-lucide="pencil"></i>
                                </a>
                                <form action="products.php?action=delete" method="POST" style="display: inline; margin-left: 10px;" onsubmit="return confirm('Eliminar este producto? Esta accion no se puede deshacer.');">
                                    <?= \Core\Security::csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;" title="Eliminar">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mobile-cards" style="display: none;">
            <?php foreach($products as $p): ?>
                <?php
                    $priceUnit = $p['price_unit'] ?? 'unit';
                    $unitLabel = $priceUnit === 'meter' ? 'Metro' : 'Unidad';
                    $unitSuffix = $priceUnit === 'meter' ? 'mts.' : 'unds.';
                    $categoryLabel = !empty($p['parent_category_name']) ? $p['parent_category_name'] . ' / ' . $p['category_name'] : ($p['category_name'] ?: 'Sin Categoria');
                ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title"><?= htmlspecialchars($p['name']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            <?= htmlspecialchars($p['sku'] ?: 'S/N') ?>
                        </div>
                    </div>
                    <div class="mobile-card-meta">
                        <strong>Categoria:</strong> <?= htmlspecialchars($categoryLabel) ?><br>
                        <?php $currency = $p['currency'] ?? 'CLP'; ?>
                        <strong>Precio:</strong> <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$p['price'], $currency === 'CLP' ? 0 : 2, ',', '.') ?><br>
                        <strong>Medida:</strong> <?= $unitLabel ?><br>
                        <?php
                            $rate = (float)($rates[$currency] ?? 1);
                            $priceClp = ceil((float)$p['price'] * $rate);
                        ?>
                        <strong>Equiv. CLP:</strong> $<?= number_format($priceClp, 0, ',', '.') ?><br>
                        <strong>Stock:</strong>
                        <span style="background: <?= $p['stock'] > 0 ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' ?>; color: <?= $p['stock'] > 0 ? '#10b981' : '#ef4444' ?>; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 700;">
                            <?= number_format((float)$p['stock'], $priceUnit === 'meter' ? 2 : 0, ',', '.') ?> <?= $unitSuffix ?>
                        </span>
                    </div>
                    <div class="mobile-card-actions">
                        <a href="products.php?edit=<?= (int)$p['id'] ?>" class="btn-primary" style="flex: 1; text-align: center; padding: 8px; font-size: 0.8rem;">
                            <i data-lucide="pencil" style="width: 14px;"></i> Editar
                        </a>
                        <form action="products.php?action=delete" method="POST" style="flex: 1;" onsubmit="return confirm('Eliminar este producto? Esta accion no se puede deshacer.');">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn-secondary" style="width: 100%; background: rgba(239,68,68,0.1); color: #ef4444; border-color: rgba(239,68,68,0.3); padding: 8px; font-size: 0.8rem;">
                                <i data-lucide="trash-2" style="width: 14px;"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="glass-card" style="flex: 1; align-self: flex-start; padding: 25px;">
        <h3 style="margin-bottom: 25px; font-weight: 800; display: flex; align-items: center; gap: 10px;">
            <i data-lucide="<?= !empty($editing) ? 'pencil' : 'plus-circle' ?>" style="color: var(--primary);"></i>
            <?= !empty($editing) ? 'Editar Producto' : 'Nuevo Producto' ?>
        </h3>
        <form action="products.php?action=<?= !empty($editing) ? 'update' : 'store' ?>" method="POST">
            <?= \Core\Security::csrfField() ?>
            <?php if(!empty($editing)): ?>
                <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Nombre del Producto *</label>
                <input type="text" name="name" required placeholder="Ej: Servicio de Mantenimiento" value="<?= htmlspecialchars($editing['name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>SKU / Codigo Interno</label>
                <input type="text" name="sku" placeholder="Se genera automaticamente si queda vacio" value="<?= htmlspecialchars($editing['sku'] ?? '') ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Categoria hija</label>
                    <select name="category_id">
                        <option value="">Sin Categoria</option>
                        <?php foreach($categories as $cat): ?>
                            <?php $displayCat = !empty($cat['parent_name']) ? htmlspecialchars($cat['parent_name'] . ' / ' . $cat['name']) : htmlspecialchars($cat['name']); ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= !empty($editing) && (int)($editing['category_id'] ?? 0) === (int)$cat['id'] ? 'selected' : '' ?>><?= $displayCat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Moneda</label>
                    <?php $selectedCurrency = $editing['currency'] ?? 'CLP'; ?>
                    <select name="currency">
                        <option value="CLP" <?= $selectedCurrency === 'CLP' ? 'selected' : '' ?>>CLP ($)</option>
                        <option value="USD" <?= $selectedCurrency === 'USD' ? 'selected' : '' ?>>USD (US$)</option>
                        <option value="UF" <?= $selectedCurrency === 'UF' ? 'selected' : '' ?>>UF (Unidad Fom.)</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio Neto *</label>
                    <input type="number" step="0.01" name="price" required placeholder="0.00" value="<?= htmlspecialchars($editing['price'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Precio por</label>
                    <?php $selectedUnit = $editing['price_unit'] ?? 'unit'; ?>
                    <select name="price_unit">
                        <option value="unit" <?= $selectedUnit === 'unit' ? 'selected' : '' ?>>Unidad</option>
                        <option value="meter" <?= $selectedUnit === 'meter' ? 'selected' : '' ?>>Metro</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" step="0.01" name="stock" value="<?= htmlspecialchars($editing['stock'] ?? '0') ?>">
                </div>
                <div class="form-group">
                    <label>Tasa IVA</label>
                    <input type="number" step="0.01" name="tax_rate" value="<?= htmlspecialchars($editing['tax_rate'] ?? '0.19') ?>" readonly style="background: rgba(0,0,0,0.2); opacity: 0.7;">
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 15px; width: 100%;">
                <i data-lucide="save" style="width: 18px;"></i> <?= !empty($editing) ? 'Actualizar Producto' : 'Guardar Producto' ?>
            </button>
            <?php if(!empty($editing)): ?>
                <a href="products.php" class="btn-primary" style="display: block; margin-top: 10px; text-align: center; background: rgba(255,255,255,0.1); text-decoration: none;">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>
</div>
