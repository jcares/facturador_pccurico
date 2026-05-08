<form action="settings.php?action=update&section=company" method="POST" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="section" value="company">

<div class="glass-card" style="margin-bottom: 24px;">
    <h3 class="section-heading" style="color: var(--primary);">Detalles de la Empresa</h3>
    <p class="form-help">Configure la información básica de su empresa. Estos datos aparecerán en las facturas y comunicaciones con clientes.</p>
    <div style="display: grid; grid-template-columns: 180px 1fr; gap: 30px;">
        <div>
            <?php if(!empty($settings['biz_logo'])): ?>
                <img src="uploads/<?= htmlspecialchars($settings['biz_logo']) ?>" alt="Logo de la empresa" style="width: 150px; height: 150px; object-fit: contain; background: white; border-radius: 8px; padding: 10px; margin-bottom: 12px;">
            <?php else: ?>
                <div style="width: 150px; height: 150px; background: rgba(255,255,255,0.05); border: 1px dashed var(--glass-border); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); margin-bottom: 12px;">Sin Logo</div>
            <?php endif; ?>
            <label for="biz_logo">Logo de la Empresa</label>
            <input type="file" id="biz_logo" name="biz_logo" accept="image/jpeg,image/png,image/gif,image/webp">
            <small class="form-help">Formato recomendado: PNG o JPG, máximo 2MB</small>
        </div>
        <div>
            <div class="form-group">
                <label for="biz_name">Nombre / Razón Social *</label>
                <input type="text" id="biz_name" name="biz_name" value="<?= htmlspecialchars($settings['biz_name'] ?? '') ?>" required>
                <small class="form-help">Nombre oficial de la empresa tal como aparece en documentos legales</small>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="biz_rut">RUT *</label>
                    <input type="text" id="biz_rut" name="biz_rut" value="<?= htmlspecialchars($settings['biz_rut'] ?? '') ?>" required>
                    <small class="form-help">Número de identificación tributaria</small>
                </div>
                <div class="form-group">
                    <label for="biz_giro">Giro</label>
                    <input type="text" id="biz_giro" name="biz_giro" value="<?= htmlspecialchars($settings['biz_giro'] ?? '') ?>">
                    <small class="form-help">Actividad económica principal</small>
                </div>
            </div>
            <div class="form-group">
                <label for="biz_address">Dirección</label>
                <input type="text" id="biz_address" name="biz_address" value="<?= htmlspecialchars($settings['biz_address'] ?? '') ?>">
                <small class="form-help">Dirección completa para facturación</small>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="biz_email">Email</label>
                    <input type="email" id="biz_email" name="biz_email" value="<?= htmlspecialchars($settings['biz_email'] ?? '') ?>">
                    <small class="form-help">Correo electrónico de contacto</small>
                </div>
                <div class="form-group">
                    <label for="biz_phone">Teléfono</label>
                    <input type="text" id="biz_phone" name="biz_phone" value="<?= htmlspecialchars($settings['biz_phone'] ?? '') ?>">
                    <small class="form-help">Número de teléfono de la empresa</small>
                </div>
            </div>
            <div class="form-group">
                <label for="public_base_url">URL Pública</label>
                <input type="url" id="public_base_url" name="public_base_url" value="<?= htmlspecialchars($settings['public_base_url'] ?? '') ?>" placeholder="https://facturador.pccurico.cl">
                <small class="form-help">Dirección web para enlaces públicos de facturas</small>
            </div>
        </div>
    </div>
</div>
<button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuración</button>
</form>