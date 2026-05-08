<form action="settings.php?action=update&section=taxes" method="POST" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="section" value="taxes">

<div class="glass-card" style="margin-bottom: 24px;">
    <h3 class="section-heading">Configuración de Impuestos</h3>
    <p class="form-help">Establezca las tasas impositivas que se aplicarán por defecto en sus productos y servicios.</p>
    <div class="form-group">
        <label for="default_tax_rate">Tasa IVA por defecto (%)</label>
        <input type="number" id="default_tax_rate" step="0.01" name="default_tax_rate" value="<?= htmlspecialchars($settings['default_tax_rate'] ?? '19') ?>" min="0" max="100">
        <small class="form-help">Porcentaje de IVA aplicado a productos sin tasa específica (ej: 19 para Chile)</small>
    </div>
</div>
<button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuración</button>
</form>