<form action="settings.php?action=update&section=product" method="POST" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="section" value="product">

<div class="glass-card" style="margin-bottom: 24px;">
    <h3 class="section-heading">Configuración del Producto</h3>
    <p class="form-help">Defina las unidades de medida por defecto para sus productos y servicios.</p>
    <div class="form-group">
        <label for="product_default_unit">Unidad de precio por defecto</label>
        <?php $unit = $settings['product_default_unit'] ?? 'unit'; ?>
        <select id="product_default_unit" name="product_default_unit">
            <option value="unit" <?= $unit === 'unit' ? 'selected' : '' ?>>Unidad</option>
            <option value="meter" <?= $unit === 'meter' ? 'selected' : '' ?>>Metro</option>
        </select>
        <small class="form-help">Unidad estándar para nuevos productos (puede cambiarse individualmente)</small>
    </div>
</div>
<button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuración</button>
</form>