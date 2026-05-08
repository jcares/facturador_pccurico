<form action="settings.php?action=update&section=localization" method="POST" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="section" value="localization">

<div class="glass-card" style="margin-bottom: 24px;">
    <h3 class="section-heading">Localización</h3>
    <p class="form-help">Configure los ajustes regionales para adaptar el sistema a su ubicación y preferencias.</p>
    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px;">
        <div class="form-group">
            <label for="locale_country">País</label>
            <input type="text" id="locale_country" name="locale_country" value="<?= htmlspecialchars($settings['locale_country'] ?? 'Chile') ?>">
            <small class="form-help">País donde opera la empresa</small>
        </div>
        <div class="form-group">
            <label for="locale_language">Idioma</label>
            <input type="text" id="locale_language" name="locale_language" value="<?= htmlspecialchars($settings['locale_language'] ?? 'es_CL') ?>">
            <small class="form-help">Código de idioma (ej: es_CL para español de Chile)</small>
        </div>
        <div class="form-group">
            <label for="locale_timezone">Zona horaria</label>
            <input type="text" id="locale_timezone" name="locale_timezone" value="<?= htmlspecialchars($settings['locale_timezone'] ?? 'America/Santiago') ?>">
            <small class="form-help">Zona horaria para fechas y horarios</small>
        </div>
        <div class="form-group">
            <label for="default_currency">Moneda por defecto</label>
            <?php $defaultCurrency = $settings['default_currency'] ?? 'CLP'; ?>
            <select id="default_currency" name="default_currency">
                <option value="CLP" <?= $defaultCurrency === 'CLP' ? 'selected' : '' ?>>CLP - Peso Chileno</option>
                <option value="USD" <?= $defaultCurrency === 'USD' ? 'selected' : '' ?>>USD - Dólar Americano</option>
                <option value="UF" <?= $defaultCurrency === 'UF' ? 'selected' : '' ?>>UF - Unidad de Fomento</option>
            </select>
            <small class="form-help">Moneda principal para nuevos documentos</small>
        </div>
    </div>
</div>
<button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuración</button>
</form>