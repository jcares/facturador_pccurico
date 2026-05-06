<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-weight: 800; margin: 0;">Configuración del Sistema</h2>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px; color: #10b981;">
            Configuración guardada exitosamente.
        </div>
    <?php endif; ?>

    <form action="settings.php?action=update" method="POST" enctype="multipart/form-data">
        <?= \Core\Security::csrfField() ?>
        
        <div class="glass-card" style="margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px; font-weight: 700; color: var(--primary);">Perfil de la Empresa</h3>
            
            <div style="display: flex; gap: 30px; margin-bottom: 20px;">
                <!-- Logo Upload -->
                <div style="flex: 1; text-align: center;">
                    <?php if(!empty($settings['biz_logo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($settings['biz_logo']) ?>" alt="Logo" style="max-width: 150px; max-height: 150px; object-fit: contain; margin-bottom: 15px; border-radius: 8px; background: white; padding: 10px;">
                    <?php else: ?>
                        <div style="width: 150px; height: 150px; margin: 0 auto 15px auto; background: rgba(255,255,255,0.05); border: 2px dashed var(--glass-border); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--text-muted);">
                            Sin Logo
                        </div>
                    <?php endif; ?>
                    <div class="form-group" style="text-align: left;">
                        <label>Subir Nuevo Logo (JPG/PNG)</label>
                        <input type="file" name="biz_logo" accept="image/jpeg, image/png, image/gif" style="width: 100%; padding: 8px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                    </div>
                </div>

                <!-- Info -->
                <div style="flex: 2; display: flex; flex-direction: column; gap: 15px;">
                    <div class="form-group">
                        <label>Nombre de Empresa / Razón Social *</label>
                        <input type="text" name="biz_name" value="<?= htmlspecialchars($settings['biz_name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>RUT *</label>
                        <input type="text" name="biz_rut" value="<?= htmlspecialchars($settings['biz_rut'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Giro Comercial</label>
                        <input type="text" name="biz_giro" value="<?= htmlspecialchars($settings['biz_giro'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Dirección Matriz</label>
                        <input type="text" name="biz_address" value="<?= htmlspecialchars($settings['biz_address'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-card" style="margin-bottom: 30px;">
            <h3 style="margin-bottom: 20px; font-weight: 700; color: #e0245e;">Integración Transbank Webpay Plus</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">Credenciales para cobro en línea. Obtenga estos datos en el portal de desarrolladores de Transbank.</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label>Entorno</label>
                    <select name="webpay_env" class="form-control" style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                        <option value="integration" <?= (isset($settings['webpay_env']) && $settings['webpay_env'] === 'integration') ? 'selected' : '' ?>>Integración (Pruebas)</option>
                        <option value="production" <?= (isset($settings['webpay_env']) && $settings['webpay_env'] === 'production') ? 'selected' : '' ?>>Producción</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Código de Comercio (Commerce Code)</label>
                    <input type="text" name="webpay_cc" value="<?= htmlspecialchars($settings['webpay_cc'] ?? '') ?>">
                </div>
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>API Key (Secret)</label>
                    <input type="password" name="webpay_key" value="<?= htmlspecialchars($settings['webpay_key'] ?? '') ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;">Guardar Configuración</button>
    </form>
</div>
