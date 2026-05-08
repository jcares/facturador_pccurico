<?php
$section = $section ?? 'company';
$meta = $sectionMeta ?? ['label' => 'Configuracion', 'icon' => 'settings'];
$placeholder = static function (string $title): void {
    ?>
    <div class="glass-card" style="padding: 28px;">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 12px;">
            <i data-lucide="construction" style="color: var(--primary);"></i>
            <h3 style="margin: 0; font-weight: 800;"><?= htmlspecialchars($title) ?></h3>
        </div>
        <p style="color: var(--text-muted); margin: 0;">Seccion organizada en el menu de configuracion. La funcionalidad especifica queda lista para implementar sin mezclarla con otros ajustes.</p>
    </div>
    <?php
};
$defaultEmailSubject = 'Recordatorio de pago: Documento #{invoice_number}';
$defaultEmailBody = "Hola {client_name},\n\nLe recordamos que el documento #{invoice_number} por {invoice_total} vence el {due_date}.\n\nPuede revisar el detalle en este enlace seguro:\n{public_url}\n\nSaludos,\n{business_name}";
?>

<div style="max-width: 1180px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Configuracion</h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;"><?= htmlspecialchars($meta['label'] ?? 'Configuracion') ?></p>
        </div>
        <a href="tools.php" class="btn-secondary">Herramientas</a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">Configuracion guardada exitosamente.</div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">No se pudo guardar la configuracion.</div>
    <?php endif; ?>

    <div class="main-grid" style="display: grid; grid-template-columns: 320px minmax(0, 1fr); gap: 24px; align-items: start;">
        <aside class="glass-card" style="padding: 18px;">
            <?php foreach (($sections ?? []) as $group): ?>
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; font-weight: 800; margin: 0 0 10px 6px;">
                        <?= htmlspecialchars($group['title']) ?>
                    </div>
                    <div style="display: grid; gap: 6px;">
                        <?php foreach ($group['items'] as $key => $item): ?>
                            <?php
                                $href = $item['href'] ?? ('settings.php?section=' . urlencode($key));
                                $active = $section === $key ? 'background: rgba(16,185,129,0.14); color: var(--primary); border-color: rgba(16,185,129,0.3);' : 'color: var(--text-main); border-color: transparent;';
                            ?>
                            <a href="<?= htmlspecialchars($href) ?>" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border: 1px solid; border-radius: 8px; text-decoration: none; font-size: 0.9rem; <?= $active ?>">
                                <i data-lucide="<?= htmlspecialchars($item['icon'] ?? 'settings') ?>" style="width: 16px; height: 16px;"></i>
                                <span><?= htmlspecialchars($item['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </aside>

        <section>
            <form action="settings.php?action=update" method="POST" enctype="multipart/form-data">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">

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
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuracion</button>

                <?php elseif ($section === 'localization'): ?>
                    <div class="glass-card" style="margin-bottom: 24px;">
                        <h3 class="section-heading">Localizacion</h3>
                        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label>Pais</label>
                                <input type="text" name="locale_country" value="<?= htmlspecialchars($settings['locale_country'] ?? 'Chile') ?>">
                            </div>
                            <div class="form-group">
                                <label>Idioma</label>
                                <input type="text" name="locale_language" value="<?= htmlspecialchars($settings['locale_language'] ?? 'es_CL') ?>">
                            </div>
                            <div class="form-group">
                                <label>Zona horaria</label>
                                <input type="text" name="locale_timezone" value="<?= htmlspecialchars($settings['locale_timezone'] ?? 'America/Santiago') ?>">
                            </div>
                            <div class="form-group">
                                <label>Moneda por defecto</label>
                                <?php $defaultCurrency = $settings['default_currency'] ?? 'CLP'; ?>
                                <select name="default_currency">
                                    <option value="CLP" <?= $defaultCurrency === 'CLP' ? 'selected' : '' ?>>CLP</option>
                                    <option value="USD" <?= $defaultCurrency === 'USD' ? 'selected' : '' ?>>USD</option>
                                    <option value="UF" <?= $defaultCurrency === 'UF' ? 'selected' : '' ?>>UF</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuracion</button>

                <?php elseif ($section === 'payments'): ?>
                    <div class="glass-card" style="margin-bottom: 24px;">
                        <h3 class="section-heading" style="color: #e0245e;">Payment Settings</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="webpay_env">Entorno Webpay</label>
                                <select id="webpay_env" name="webpay_env">
                                    <option value="integration" <?= ($settings['webpay_env'] ?? 'integration') === 'integration' ? 'selected' : '' ?>>Integracion</option>
                                    <option value="production" <?= ($settings['webpay_env'] ?? '') === 'production' ? 'selected' : '' ?>>Produccion</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="webpay_cc">Commerce Code</label>
                                <input type="text" id="webpay_cc" name="webpay_cc" value="<?= htmlspecialchars($settings['webpay_cc'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="webpay_key">API Key Secret</label>
                            <input type="password" id="webpay_key" name="webpay_key" value="<?= htmlspecialchars($settings['webpay_key'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="glass-card" style="margin-bottom: 24px;">
                        <h3 class="section-heading" style="color: var(--primary);">Botón de Pago Webpay</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;">Personaliza el texto y la imagen del botón que se muestra en facturas y correos para pagar con Webpay Plus.</p>
                        <div class="form-group">
                            <label for="webpay_button_text">Texto del Botón</label>
                            <input type="text" id="webpay_button_text" name="webpay_button_text" value="<?= htmlspecialchars($settings['webpay_button_text'] ?? 'Pagar con Webpay Plus') ?>" placeholder="Pagar con Webpay Plus">
                            <small style="color: var(--text-muted);">Texto que aparecerá en el botón de pago</small>
                        </div>
                        <div class="form-group">
                            <label for="webpay_button_image">Imagen del Botón Webpay</label>
                            <?php if(!empty($settings['webpay_button_image'])): ?>
                                <div style="margin-bottom: 12px; padding: 15px; background: white; border-radius: 8px; display: inline-block;">
                                    <img src="uploads/<?= htmlspecialchars($settings['webpay_button_image']) ?>" alt="Webpay Button" style="max-height: 60px; display: block;">
                                </div>
                            <?php else: ?>
                                <div style="margin-bottom: 12px; padding: 15px; background: white; border-radius: 8px; display: inline-block;">
                                    <img src="assets/img/transbank-webpay.svg" alt="Webpay Default" style="height: 42px; display: block;">
                                    <small style="color: #666; display: block; margin-top: 6px;">Imagen por defecto</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="webpay_button_image" name="webpay_button_image" accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml">
                            <small style="color: var(--text-muted);">Sube una imagen personalizada (PNG, JPG, SVG). Si no subes nada, se usa el logo oficial de Transbank.</small>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuracion</button>

                <?php elseif ($section === 'taxes'): ?>
                    <div class="glass-card" style="margin-bottom: 24px;">
                        <h3 class="section-heading">Configuracion de Impuestos</h3>
                        <div class="form-group">
                            <label>Tasa IVA por defecto</label>
                            <input type="number" step="0.01" name="default_tax_rate" value="<?= htmlspecialchars($settings['default_tax_rate'] ?? '0.19') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuracion</button>

                <?php elseif ($section === 'product'): ?>
                    <div class="glass-card" style="margin-bottom: 24px;">
                        <h3 class="section-heading">Configuracion del Producto</h3>
                        <div class="form-group">
                            <label>Unidad de precio por defecto</label>
                            <?php $unit = $settings['product_default_unit'] ?? 'unit'; ?>
                            <select name="product_default_unit">
                                <option value="unit" <?= $unit === 'unit' ? 'selected' : '' ?>>Unidad</option>
                                <option value="meter" <?= $unit === 'meter' ? 'selected' : '' ?>>Metro</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuracion</button>

                <?php elseif ($section === 'email'): ?>
                    <div class="glass-card" style="margin-bottom: 24px;">
                        <h3 class="section-heading" style="color: var(--primary);">Configuracion del Correo Electronico</h3>
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
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuracion</button>

                <?php elseif ($section === 'email-templates'): ?>
                    <div class="glass-card" style="margin-bottom: 24px;">
                        <h3 class="section-heading">Plantillas & Recordatorios</h3>
                        <div class="form-group">
                            <label>Asunto del correo</label>
                            <input type="text" name="email_subject_template" value="<?= htmlspecialchars($settings['email_subject_template'] ?? $defaultEmailSubject) ?>">
                        </div>
                        <div class="form-group">
                            <label>Cuerpo del correo</label>
                            <textarea name="email_body_template" rows="12" style="width: 100%; resize: vertical;"><?= htmlspecialchars($settings['email_body_template'] ?? $defaultEmailBody) ?></textarea>
                        </div>
                        <div style="display: grid; gap: 12px; color: var(--text-main);">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="email_include_webpay_button" value="1" <?= ($settings['email_include_webpay_button'] ?? '1') === '1' ? 'checked' : '' ?>>
                                Mostrar boton de pago Webpay con marca Transbank cuando exista saldo pendiente
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="email_attach_pdf" value="1" <?= ($settings['email_attach_pdf'] ?? '0') === '1' ? 'checked' : '' ?>>
                                Adjuntar PDF de la factura
                            </label>
                        </div>
                        <div style="margin-top: 18px; color: var(--text-muted); font-size: 0.85rem;">
                            Variables: <code>{client_name}</code>, <code>{invoice_number}</code>, <code>{invoice_total}</code>, <code>{due_date}</code>, <code>{public_url}</code>, <code>{business_name}</code>.
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%; padding: 15px;">Guardar Configuracion</button>

                <?php else: ?>
                    <?php $placeholder($meta['label'] ?? 'Configuracion'); ?>
                <?php endif; ?>
            </form>
        </section>
    </div>
</div>
