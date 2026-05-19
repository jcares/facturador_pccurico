<form action="settings.php?action=update&section=email" method="POST" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="section" value="email">

<div class="glass-card mb-20">
    <h3 class="section-heading">Configuración del Correo Electrónico</h3>
    <p class="form-help mb-20">Configure el servidor SMTP para enviar correos electrónicos automáticos desde el sistema.</p>
    
    <div class="form-row">
        <div class="form-group">
            <label for="smtp_host">Servidor SMTP</label>
            <input type="text" id="smtp_host" name="smtp_host" class="form-control" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
            <small class="form-help">Dirección del servidor de correo saliente</small>
        </div>
        <div class="form-group" style="max-width: 140px;">
            <label for="smtp_port">Puerto</label>
            <input type="number" id="smtp_port" name="smtp_port" class="form-control" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" min="1" max="65535">
            <small class="form-help">Puerto SMTP</small>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="smtp_user">Usuario</label>
            <input type="text" id="smtp_user" name="smtp_user" class="form-control" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>" placeholder="usuario@dominio.com">
            <small class="form-help">Nombre de usuario para autenticación</small>
        </div>
        <div class="form-group">
            <label for="smtp_pass">Contraseña</label>
            <input type="password" id="smtp_pass" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>">
            <small class="form-help">Contraseña o clave de aplicación</small>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="smtp_secure">Seguridad</label>
            <?php $smtpSecure = $settings['smtp_secure'] ?? 'tls'; ?>
            <select id="smtp_secure" name="smtp_secure" class="form-control">
                <option value="tls" <?= $smtpSecure === 'tls' ? 'selected' : '' ?>>TLS (Recomendado)</option>
                <option value="ssl" <?= $smtpSecure === 'ssl' ? 'selected' : '' ?>>SSL</option>
                <option value="" <?= $smtpSecure === '' ? 'selected' : '' ?>>Sin cifrado</option>
            </select>
            <small class="form-help">Tipo de conexión segura</small>
        </div>
        <div class="form-group">
            <label for="smtp_from">Remitente</label>
            <input type="email" id="smtp_from" name="smtp_from" class="form-control" value="<?= htmlspecialchars($settings['smtp_from'] ?? '') ?>" placeholder="facturacion@pccurico.cl">
            <small class="form-help">Email que aparecerá como emisor</small>
        </div>
    </div>
    
    <button type="submit" class="btn-primary" style="width: 100%;">Guardar Configuración SMTP</button>
</div>
</form>

<div class="glass-card">
    <h3 class="section-heading" style="display: flex; align-items: center; gap: 8px;">
        <i data-lucide="send"></i> Probar Conexión
    </h3>
    <p class="form-help mb-20">Asegúrese de guardar los cambios antes de realizar la prueba.</p>
    
    <div style="background: rgba(34, 211, 238, 0.05); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px;">
        <div class="form-group" style="margin-bottom: 16px;">
            <label for="test_email_to" style="font-weight: 600; color: var(--text-main); margin-bottom: 8px; display: block;">Correo de Prueba</label>
            <input type="email" id="test_email_to" class="form-control" placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($user['email'] ?? '') ?>" style="padding: 12px 16px; font-size: 1rem;">
            <small class="form-help" style="display: block; margin-top: 6px; color: var(--text-muted);">Dirección donde recibirás el correo de prueba</small>
        </div>
        
        <button type="button" onclick="sendTestEmail()" class="btn-primary" style="width: auto; padding: 12px 24px; display: inline-flex; align-items: center; gap: 8px;">
            <i data-lucide="send" style="width: 16px; height: 16px;"></i> Enviar Correo de Prueba
        </button>
    </div>
</div>

<script>
function sendTestEmail() {
    const email = document.getElementById('test_email_to').value;
    if (!email) {
        Toast.show('Ingrese un correo válido', 'error');
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader" class="animate-spin"></i> Enviando...';
    if(window.lucide) lucide.createIcons();

    fetch('settings.php?action=test_email&email=' + encodeURIComponent(email))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Toast.show(data.message, 'success');
            } else {
                Toast.show(data.message, 'error');
            }
        })
        .catch(err => {
            Toast.show('Error al conectar con el servidor', 'error');
            console.error(err);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if(window.lucide) lucide.createIcons();
        });
}
</script>