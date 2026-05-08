<?php if (isset($testMessage) && $testMessage): ?>
    <div class="alert alert-success">
        <i data-lucide="check-circle" style="display:inline-block; vertical-align:middle; margin-right:8px;"></i>
        <?= htmlspecialchars($testMessage) ?>
    </div>
<?php endif; ?>

<?php if (isset($testError) && $testError): ?>
    <div class="alert alert-error">
        <i data-lucide="alert-circle" style="display:inline-block; vertical-align:middle; margin-right:8px;"></i>
        <?= htmlspecialchars($testError) ?>
    </div>
<?php endif; ?>

<form action="settings.php?action=update&section=email" method="POST" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="section" value="email">

<div class="glass-card" style="margin-bottom: 24px;">
    <h3 class="section-heading" style="color: var(--primary);">Configuración del Correo Electrónico</h3>
    <p class="form-help">Configure el servidor SMTP para enviar correos electrónicos automáticos desde el sistema.</p>
    <div style="display: grid; grid-template-columns: 1fr 120px; gap: 20px;">
        <div class="form-group">
            <label for="smtp_host">Servidor SMTP</label>
            <input type="text" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
            <small class="form-help">Dirección del servidor de correo saliente</small>
        </div>
        <div class="form-group">
            <label for="smtp_port">Puerto</label>
            <input type="number" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" min="1" max="65535">
            <small class="form-help">Puerto del servidor SMTP</small>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
            <label for="smtp_user">Usuario</label>
            <input type="text" id="smtp_user" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>" placeholder="usuario@dominio.com">
            <small class="form-help">Nombre de usuario para autenticación SMTP</small>
        </div>
        <div class="form-group">
            <label for="smtp_pass">Contraseña</label>
            <input type="password" id="smtp_pass" name="smtp_pass" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>">
            <small class="form-help">Contraseña o clave de aplicación del correo</small>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="form-group">
            <label for="smtp_secure">Seguridad</label>
            <?php $smtpSecure = $settings['smtp_secure'] ?? 'tls'; ?>
            <select id="smtp_secure" name="smtp_secure">
                <option value="tls" <?= $smtpSecure === 'tls' ? 'selected' : '' ?>>TLS</option>
                <option value="ssl" <?= $smtpSecure === 'ssl' ? 'selected' : '' ?>>SSL</option>
                <option value="" <?= $smtpSecure === '' ? 'selected' : '' ?>>Sin cifrado</option>
            </select>
            <small class="form-help">Tipo de conexión segura para SMTP</small>
        </div>
        <div class="form-group">
            <label for="smtp_from">Remitente</label>
            <input type="email" id="smtp_from" name="smtp_from" value="<?= htmlspecialchars($settings['smtp_from'] ?? '') ?>" placeholder="facturacion@pccurico.cl">
            <small class="form-help">Dirección de correo que aparecerá como remitente</small>
        </div>
    </div>
    
    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; margin-top: 10px;">Guardar Configuración</button>
</div>
</form>

<div class="glass-card">
    <h3 class="section-heading" style="color: var(--primary); display: flex; align-items: center; gap: 8px;">
        <i data-lucide="send"></i> Correo de Prueba
    </h3>
    <p class="form-help" style="margin-bottom: 20px;">Asegúrese de guardar la configuración antes de enviar un correo de prueba.</p>
    
    <form action="email_settings.php" method="POST">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="action" value="test_email">
        <div style="display: flex; gap: 15px; align-items: flex-start;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <input type="email" name="test_email_to" required placeholder="Ingrese correo de destino..." value="<?= htmlspecialchars($user['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-secondary" style="margin-top: 0; padding: 12px 20px;">
                Enviar Prueba
            </button>
        </div>
    </form>
</div>