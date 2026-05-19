<form action="settings.php?action=update&section=email-templates" method="POST" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="section" value="email-templates">

<div class="glass-card mb-20">
    <h3 class="section-heading">Plantillas & Recordatorios</h3>
    <p class="form-help mb-20">Personalice los correos electrónicos automáticos enviados a sus clientes. Haga clic en los botones azules para añadir información de forma automática.</p>
    
    <div class="form-group">
        <label for="email_subject_template">Asunto del correo</label>
        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px;">
            <span class="var-badge" onclick="insertVar('email_subject_template', '{invoice_number}')"># Número de Factura</span>
            <span class="var-badge" onclick="insertVar('email_subject_template', '{business_name}')">🏢 Mi Empresa</span>
        </div>
        <input type="text" id="email_subject_template" name="email_subject_template" class="form-control" value="<?= htmlspecialchars($settings['email_subject_template'] ?? $defaultEmailSubject) ?>" onfocus="lastFocused = this;">
    </div>

    <div class="form-group">
        <label for="email_body_template">Cuerpo del correo</label>
        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px;">
            <span class="var-badge" onclick="insertVar('email_body_template', '{client_name}')">👤 Nombre Cliente</span>
            <span class="var-badge" onclick="insertVar('email_body_template', '{invoice_number}')"># Número Factura</span>
            <span class="var-badge" onclick="insertVar('email_body_template', '{invoice_total}')">💰 Total Venta</span>
            <span class="var-badge" onclick="insertVar('email_body_template', '{due_date}')">📅 Vencimiento</span>
            <span class="var-badge" onclick="insertVar('email_body_template', '{public_url}')">🔗 Link de Pago</span>
            <span class="var-badge" onclick="insertVar('email_body_template', '{business_name}')">🏢 Mi Empresa</span>
        </div>
        <textarea id="email_body_template" name="email_body_template" rows="10" class="form-control" style="resize: vertical;" onfocus="lastFocused = this;"><?= htmlspecialchars($settings['email_body_template'] ?? $defaultEmailBody) ?></textarea>
        <small class="form-help">Edite el mensaje a su gusto. Las palabras entre llaves {} se cambiarán por datos reales al enviar el correo.</small>
    </div>

    <div style="display: grid; gap: 15px; margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--panel-border);">
        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
            <input type="checkbox" id="email_include_webpay_button" name="email_include_webpay_button" value="1" <?= ($settings['email_include_webpay_button'] ?? '1') === '1' ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--primary);">
            <span style="color: var(--text-main); font-size: 0.9rem;">Mostrar botón de pago <strong>Webpay (Transbank)</strong> cuando exista saldo pendiente</span>
        </label>
        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer;">
            <input type="checkbox" id="email_attach_pdf" name="email_attach_pdf" value="1" <?= ($settings['email_attach_pdf'] ?? '0') === '1' ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--primary);">
            <span style="color: var(--text-main); font-size: 0.9rem;">Adjuntar copia de la <strong>Factura en PDF</strong> automáticamente</span>
        </label>
    </div>

    <script>
    let lastFocused = null;
    function insertVar(targetId, variable) {
        const el = document.getElementById(targetId);
        const start = el.selectionStart;
        const end = el.selectionEnd;
        const text = el.value;
        el.value = text.substring(0, start) + variable + text.substring(end);
        el.selectionStart = el.selectionEnd = start + variable.length;
        el.focus();
    }
    </script>
</div>
<button type="submit" class="btn-primary" style="width: 100%;">Guardar Plantillas</button>
</form>