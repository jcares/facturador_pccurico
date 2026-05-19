<?php
$section = $section ?? 'company';
$defaultEmailSubject = 'Recordatorio de pago: Documento #{invoice_number}';
$defaultEmailBody = "Hola {client_name},\n\nLe recordamos que el documento #{invoice_number} por {invoice_total} vence el {due_date}.\n\nPuede revisar el detalle en este enlace seguro:\n{public_url}\n\nSaludos,\n{business_name}";
?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <i data-lucide="check-circle"></i>
        Configuración guardada exitosamente.
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <i data-lucide="alert-circle"></i>
        No se pudo guardar la configuración.
    </div>
<?php endif; ?>

<form action="settings.php?action=update" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 24px;">
    <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
    <?= \Core\Security::csrfField() ?>

    <?php if ($section === 'company'): ?>
        <div class="glass-card" style="margin-bottom: 24px;">
            <h3 class="section-heading" style="color: var(--primary);">Detalles de la Empresa</h3>
            <div style="display: grid; grid-template-columns: 180px 1fr; gap: 30px;">
                <div>
                    <?php if(!empty($settings['biz_logo'])): ?>
                        <img src="uploads/<?= htmlspecialchars($settings['biz_logo']) ?>" alt="Logo" style="width: 150px; height: 150px; object-fit: contain; background: white; border-radius: 8px; padding: 10px; margin-bottom: 12px;">
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
        <button type="submit" class="btn-primary">Guardar Configuración</button>

    <?php elseif ($section === 'localization'): ?>
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
        <button type="submit" class="btn-primary">Guardar Configuración</button>



    <?php elseif ($section === 'transbank'): ?>
        <div class="glass-card" style="margin-bottom: 24px;">
            <h3 class="section-heading" style="color: var(--primary);">Configuración de Pagos (Webpay)</h3>
            <p class="form-help">Configure sus credenciales oficiales de Webpay Plus para procesar pagos.</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="webpay_env">Ambiente Webpay <span style="color: var(--danger);">*</span></label>
                    <select id="webpay_env" name="webpay_env">
                        <option value="integration" <?= ($settings['webpay_env'] ?? 'integration') === 'integration' ? 'selected' : '' ?>>Integración (Pruebas)</option>
                        <option value="production" <?= ($settings['webpay_env'] ?? '') === 'production' ? 'selected' : '' ?>>Producción</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="webpay_cc">Código de Comercio <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="webpay_cc" name="webpay_cc" value="<?= htmlspecialchars($settings['webpay_cc'] ?? '') ?>" placeholder="Ej: 597012345678">
                </div>
            </div>
            <div class="form-group">
                <label for="webpay_key">API Key (Llave Secreta) <span style="color: var(--danger);">*</span></label>
                <input type="password" id="webpay_key" name="webpay_key" value="<?= htmlspecialchars($settings['webpay_key'] ?? '') ?>" placeholder="XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX">
            </div>
        </div>

        <div class="glass-card" style="margin-bottom: 24px;">
            <h3 class="section-heading">Botón de Pago Webpay</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div class="form-group">
                        <label for="webpay_button_text">Texto del Botón</label>
                        <input type="text" id="webpay_button_text" name="webpay_button_text" value="<?= htmlspecialchars($settings['webpay_button_text'] ?? 'Pagar con Webpay Plus') ?>">
                    </div>
                    <div class="form-group">
                        <label for="buy_order_format">Formato Orden de Compra</label>
                        <input type="text" id="buy_order_format" name="buy_order_format" value="<?= htmlspecialchars($settings['buy_order_format'] ?? 'INV{invoiceId}{random,length=6}') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="webpay_button_image">Imagen del Botón</label>
                    <input type="file" id="webpay_button_image" name="webpay_button_image" accept="image/*">
                    <?php if(!empty($settings['webpay_button_image'])): ?>
                        <div style="margin-top: 10px; padding: 10px; background: white; border-radius: 8px; display: inline-block;">
                            <img src="uploads/<?= htmlspecialchars($settings['webpay_button_image']) ?>" alt="Preview" style="height: 30px;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn-primary" style="flex: 1;">Guardar Configuración</button>
            <button type="button" class="btn-secondary" onclick="testTbkConnection()">Probar Conexión</button>
        </div>

        <script>
        function testTbkConnection() {
            const btn = event.target;
            btn.disabled = true;
            btn.innerText = 'Probando...';
            
            fetch('settings.php?action=test_transbank')
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(e => alert('Error al conectar'))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerText = 'Probar Conexión';
                });
        }
        </script>

    <?php elseif ($section === 'taxes'): ?>
        <div class="glass-card" style="margin-bottom: 24px;">
            <h3 class="section-heading">Configuración de Impuestos</h3>
            <p class="form-help">Define los impuestos aplicables a tus productos y servicios.</p>
        </div>
        <button type="submit" class="btn-primary">Guardar Configuración</button>

    <?php elseif ($section === 'product'): ?>
        <div class="glass-card" style="margin-bottom: 24px;">
            <h3 class="section-heading">Configuración del Producto</h3>
            <p class="form-help">Ajustes generales para la gestión de productos e inventario.</p>
        </div>
        <button type="submit" class="btn-primary">Guardar Configuración</button>

    <?php elseif ($section === 'email'): ?>
        <div class="glass-card" style="margin-bottom: 24px;">
            <h3 class="section-heading">Configuración de Envío de Correo</h3>
            <p class="form-help">Configura cómo el sistema envía los correos electrónicos a tus clientes.</p>
            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px;">
                <div class="form-group">
                    <label for="smtp_from">Email Remitente</label>
                    <input type="email" id="smtp_from" name="smtp_from" value="<?= htmlspecialchars($settings['smtp_from'] ?? '') ?>" placeholder="facturacion@pccurico.cl">
                </div>
                <div class="form-group">
                    <label for="smtp_host">Servidor SMTP</label>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_port">Puerto</label>
                    <input type="text" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_user">Usuario SMTP</label>
                    <input type="text" id="smtp_user" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_pass">Contraseña SMTP</label>
                    <input type="password" id="smtp_pass" name="smtp_pass" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>" placeholder="••••••••••••">
                </div>
                <div class="form-group">
                    <label for="smtp_secure">Cifrado</label>
                    <select id="smtp_secure" name="smtp_secure">
                        <option value="tls" <?= ($settings['smtp_secure'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= ($settings['smtp_secure'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="none" <?= ($settings['smtp_secure'] ?? '') === 'none' ? 'selected' : '' ?>>Ninguno</option>
                    </select>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn-primary" style="flex: 1;">Guardar Configuración</button>
            <button type="button" class="btn-secondary" onclick="testEmailConnection()">Probar Envío</button>
        </div>

        <script>
        function testEmailConnection() {
            const email = prompt('Ingresa un correo para recibir la prueba:');
            if (!email) return;
            
            const btn = event.target;
            btn.disabled = true;
            btn.innerText = 'Enviando...';
            
            fetch('settings.php?action=test_email&email=' + encodeURIComponent(email))
                .then(r => r.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(e => alert('Error al conectar'))
                .finally(() => {
                    btn.disabled = false;
                    btn.innerText = 'Probar Envío';
                });
        }
        </script>

    <?php elseif ($section === 'email-templates'): ?>
        <div class="glass-card" style="margin-bottom: 24px;">
            <h3 class="section-heading">Plantillas de Correo</h3>
            <p class="form-help">Arrastra las etiquetas dinámicas para personalizar tus mensajes.</p>
            <div class="form-group">
                <label for="email_subject_template">Asunto del Recordatorio</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px;">
                    <span class="var-badge" onclick="insertVar('email_subject_template', '{invoice_number}')">{num_factura}</span>
                    <span class="var-badge" onclick="insertVar('email_subject_template', '{business_name}')">{mi_empresa}</span>
                </div>
                <input type="text" id="email_subject_template" name="email_subject_template" value="<?= htmlspecialchars($settings['email_subject_template'] ?? $defaultEmailSubject) ?>">
            </div>
            <div class="form-group">
                <label for="email_body_template">Cuerpo del Mensaje</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 10px;">
                    <span class="var-badge" onclick="insertVar('email_body_template', '{client_name}')">Nombre Cliente</span>
                    <span class="var-badge" onclick="insertVar('email_body_template', '{invoice_number}')"># Factura</span>
                    <span class="var-badge" onclick="insertVar('email_body_template', '{invoice_total}')">Total</span>
                    <span class="var-badge" onclick="insertVar('email_body_template', '{due_date}')">Vencimiento</span>
                    <span class="var-badge" onclick="insertVar('email_body_template', '{public_url}')">Link Público</span>
                    <span class="var-badge" onclick="insertVar('email_body_template', '{business_name}')">Mi Empresa</span>
                </div>
                <textarea id="email_body_template" name="email_body_template" rows="8" style="font-family: inherit; resize: vertical;"><?= htmlspecialchars($settings['email_body_template'] ?? $defaultEmailBody) ?></textarea>
            </div>



            <script>
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
        <button type="submit" class="btn-primary">Guardar Configuración</button>

    <?php else: ?>
        <div class="glass-card">
            <h3 class="section-heading">Configuración</h3>
            <p style="color: var(--text-muted);">Esta sección (<?= htmlspecialchars($section) ?>) está en construcción o requiere módulos adicionales.</p>
        </div>
    <?php endif; ?>
</form>