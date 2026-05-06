<div style="max-width: 900px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 30px;">
        <h2 style="font-weight: 800; margin: 0;">Configuracion del Sistema</h2>
        <a href="tools.php" class="btn-secondary">Herramientas</a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">Configuracion guardada exitosamente.</div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">No se pudo guardar la configuracion.</div>
    <?php endif; ?>

    <form action="settings.php?action=update" method="POST" enctype="multipart/form-data">
        <?= \Core\Security::csrfField() ?>

        <div class="glass-card" style="margin-bottom: 30px;">
            <h3 class="section-heading" style="color: var(--primary);">Empresa</h3>
            <div style="display: grid; grid-template-columns: 180px 1fr; gap: 30px;">
                <div>
                    <?php if(!empty($settings['biz_logo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($settings['biz_logo']) ?>" alt="Logo" style="width: 150px; height: 150px; object-fit: contain; background: white; border-radius: 8px; padding: 10px; margin-bottom: 12px;">
                    <?php else: ?>
                        <div style="width: 150px; height: 150px; background: rgba(255,255,255,0.05); border: 1px dashed var(--glass-border); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); margin-bottom: 12px;">Sin Logo</div>
                    <?php endif; ?>
                    <label>Logo</label>
                    <input type="file" name="biz_logo" accept="image/jpeg,image/png,image/gif,image/webp">
                </div>
                <div>
                    <div class="form-group">
                        <label>Nombre / Razon Social *</label>
                        <input type="text" name="biz_name" value="<?= htmlspecialchars($settings['biz_name'] ?? '') ?>" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>RUT *</label>
                            <input type="text" name="biz_rut" value="<?= htmlspecialchars($settings['biz_rut'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Giro</label>
                            <input type="text" name="biz_giro" value="<?= htmlspecialchars($settings['biz_giro'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Direccion</label>
                        <input type="text" name="biz_address" value="<?= htmlspecialchars($settings['biz_address'] ?? '') ?>">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="biz_email" value="<?= htmlspecialchars($settings['biz_email'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Telefono</label>
                            <input type="text" name="biz_phone" value="<?= htmlspecialchars($settings['biz_phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>URL Publica</label>
                        <input type="url" name="public_base_url" value="<?= htmlspecialchars($settings['public_base_url'] ?? '') ?>" placeholder="https://facturador.pccurico.cl">
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card" style="margin-bottom: 30px;">
            <h3 class="section-heading" style="color: #e0245e;">Transbank Webpay Plus</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Entorno</label>
                    <select name="webpay_env">
                        <option value="integration" <?= ($settings['webpay_env'] ?? 'integration') === 'integration' ? 'selected' : '' ?>>Integracion</option>
                        <option value="production" <?= ($settings['webpay_env'] ?? '') === 'production' ? 'selected' : '' ?>>Produccion</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Commerce Code</label>
                    <input type="text" name="webpay_cc" value="<?= htmlspecialchars($settings['webpay_cc'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>API Key Secret</label>
                <input type="password" name="webpay_key" value="<?= htmlspecialchars($settings['webpay_key'] ?? '') ?>">
            </div>
        </div>

        <div class="glass-card" style="margin-bottom: 30px;">
            <h3 class="section-heading" style="color: var(--primary);">Correo SMTP</h3>
            <div style="display: grid; grid-template-columns: 1fr 120px; gap: 20px;">
                <div class="form-group">
                    <label>Servidor SMTP</label>
                    <input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Puerto</label>
                    <input type="number" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Usuario</label>
                    <input type="text" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Clave</label>
                    <input type="password" name="smtp_pass" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Seguridad</label>
                    <?php $smtpSecure = $settings['smtp_secure'] ?? 'tls'; ?>
                    <select name="smtp_secure">
                        <option value="tls" <?= $smtpSecure === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= $smtpSecure === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="" <?= $smtpSecure === '' ? 'selected' : '' ?>>Sin cifrado</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Remitente</label>
                    <input type="email" name="smtp_from" value="<?= htmlspecialchars($settings['smtp_from'] ?? '') ?>" placeholder="facturacion@pccurico.cl">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.05rem;">Guardar Configuracion</button>
    </form>
</div>
