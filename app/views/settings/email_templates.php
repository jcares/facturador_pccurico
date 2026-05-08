<form action="settings.php?action=update&section=email-templates" method="POST" enctype="multipart/form-data">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="section" value="email-templates">

<div class="glass-card" style="margin-bottom: 24px;">
    <h3 class="section-heading">Plantillas & Recordatorios</h3>
    <p class="form-help">Personalice los correos electrónicos automáticos enviados a sus clientes.</p>
    <div class="form-group">
        <label for="email_subject_template">Asunto del correo</label>
        <input type="text" id="email_subject_template" name="email_subject_template" value="<?= htmlspecialchars($settings['email_subject_template'] ?? $defaultEmailSubject) ?>">
        <small class="form-help">Línea de asunto que verán los destinatarios</small>
    </div>
    <div class="form-group">
        <label for="email_body_template">Cuerpo del correo</label>
        <textarea id="email_body_template" name="email_body_template" rows="12" style="width: 100%; resize: vertical;"><?= htmlspecialchars($settings['email_body_template'] ?? $defaultEmailBody) ?></textarea>
        <small class="form-help">Contenido del mensaje. Use variables como {client_name}, {invoice_total}, {due_date}, {public_url}</small>
    </div>
    <div style="display: grid; gap: 12px; color: var(--text-main);">
        <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" id="email_include_webpay_button" name="email_include_webpay_button" value="1" <?= ($settings['email_include_webpay_button'] ?? '1') === '1' ? 'checked' : '' ?>>
            Mostrar botón de pago Webpay con marca Transbank cuando exista saldo pendiente
        </label>
        <label style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" id="email_attach_pdf" name="email_attach_pdf" value="1" <?= ($settings['email_attach_pdf'] ?? '0') === '1' ? 'checked' : '' ?>>
            Adjuntar PDF de la factura al correo
        </label>
    </div>
</div>
<button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuración</button>
</form>