<?php
$section = 'transbank';
?>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <i data-lucide="check-circle"></i>
        Configuración de Transbank guardada exitosamente.
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-error">
        <i data-lucide="alert-circle"></i>
        No se pudo guardar la configuración.
    </div>
<?php endif; ?>

<div id="test-alert-container"></div>

<form action="settings.php?action=update" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 24px;">
    <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
    <?= \Core\Security::csrfInput() ?>

    <div class="glass-card">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(224, 36, 94, 0.15); display: flex; align-items: center; justify-content: center;">
                <i data-lucide="landmark" style="color: #e0245e;"></i>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700;">Credenciales Webpay Plus</h3>
                <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">Configure su conexión oficial con Transbank</p>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <div class="form-group">
                <label for="webpay_env">Ambiente de Ejecución <span style="color: var(--danger);">*</span></label>
                <select id="webpay_env" name="webpay_env" required>
                    <option value="integration" <?= ($settings['webpay_env'] ?? 'integration') === 'integration' ? 'selected' : '' ?>>Integración (Pruebas)</option>
                    <option value="production" <?= ($settings['webpay_env'] ?? '') === 'production' ? 'selected' : '' ?>>Producción (Real)</option>
                </select>
                <small class="form-help">Utilice Integración para realizar pruebas sin cargos reales.</small>
            </div>

            <div class="form-group">
                <label for="webpay_cc">Código de Comercio <span style="color: var(--danger);">*</span></label>
                <input type="text" id="webpay_cc" name="webpay_cc" value="<?= htmlspecialchars($settings['webpay_cc'] ?? '') ?>" placeholder="Ej: 597012345678" required>
                <small class="form-help">Código de 12 dígitos proporcionado por Transbank.</small>
            </div>
        </div>

        <div class="form-group">
            <label for="webpay_key">API Key (Llave Secreta) <span style="color: var(--danger);">*</span></label>
            <div style="position: relative;">
                <input type="password" id="webpay_key" name="webpay_key" value="<?= htmlspecialchars($settings['webpay_key'] ?? '') ?>" placeholder="Su llave privada de Webpay" required style="padding-right: 45px;">
                <button type="button" onclick="togglePassword()" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;">
                    <i data-lucide="eye" id="eye-icon" style="width: 18px;"></i>
                </button>
            </div>
            <small class="form-help">Mantenga esta llave en secreto. Es necesaria para firmar las transacciones.</small>
        </div>
    </div>

    <div class="glass-card">
        <h3 class="section-heading">Personalización de Pago</h3>
        <p class="form-help">Ajuste la apariencia del botón de pago que verán sus clientes.</p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
            <div>
                <div class="form-group">
                    <label for="webpay_button_text">Texto del Botón</label>
                    <input type="text" id="webpay_button_text" name="webpay_button_text" value="<?= htmlspecialchars($settings['webpay_button_text'] ?? 'Pagar con Webpay Plus') ?>" oninput="updatePreview()">
                </div>

                <div class="form-group">
                    <label for="webpay_button_image">Imagen / Logo Personalizado</label>
                    <input type="file" id="webpay_button_image" name="webpay_button_image" accept="image/*" onchange="previewImage(this)">
                    <small class="form-help">Se recomienda un logo de Transbank o de su empresa (PNG/SVG).</small>
                </div>

                <div class="form-group">
                    <label for="buy_order_format">Formato Orden de Compra</label>
                    <input type="text" id="buy_order_format" name="buy_order_format" value="<?= htmlspecialchars($settings['buy_order_format'] ?? 'INV{invoiceId}{random,length=6}') ?>">
                    <small class="form-help">Variables: {invoiceId}, {random,length=N}</small>
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.02); border: 1px dashed var(--glass-border); border-radius: 12px; padding: 20px; text-align: center;">
                <span style="display: block; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.05em;">Vista Previa del Botón</span>
                
                <div id="button-preview" style="display: inline-flex; align-items: center; gap: 12px; background: white; color: #111; padding: 12px 24px; border-radius: 8px; font-weight: 700; cursor: default; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <?php if(!empty($settings['webpay_button_image'])): ?>
                        <img id="preview-img" src="uploads/<?= htmlspecialchars($settings['webpay_button_image']) ?>" alt="" style="height: 24px; object-fit: contain;">
                    <?php else: ?>
                        <img id="preview-img" src="assets/img/webpay-logo.png" alt="" style="height: 24px; object-fit: contain;" onerror="this.style.display='none'">
                    <?php endif; ?>
                    <span id="preview-text"><?= htmlspecialchars($settings['webpay_button_text'] ?? 'Pagar con Webpay Plus') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; gap: 15px; align-items: center;">
        <button type="submit" class="btn-primary" style="flex: 1;">
            <i data-lucide="save"></i> Guardar Cambios
        </button>
        <button type="button" onclick="testConnection()" class="btn-secondary" id="btn-test">
            <i data-lucide="refresh-cw" id="icon-test"></i> Probar Conexión
        </button>
    </div>
</form>

<script>
function togglePassword() {
    const input = document.getElementById('webpay_key');
    const icon = document.getElementById('eye-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
    } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
    }
    if (typeof lucide !== 'undefined') lucide.createIcons();
}

function updatePreview() {
    const text = document.getElementById('webpay_button_text').value || 'Pagar con Webpay Plus';
    document.getElementById('preview-text').innerText = text;
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('preview-img');
            img.src = e.target.result;
            img.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

async function testConnection() {
    const btn = document.getElementById('btn-test');
    const icon = document.getElementById('icon-test');
    const container = document.getElementById('test-alert-container');
    
    btn.disabled = true;
    icon.classList.add('spin-animation');
    container.innerHTML = '';

    try {
        const response = await fetch('transbank_settings.php?action=test');
        const data = await response.json();
        
        const alert = document.createElement('div');
        alert.className = `alert ${data.success ? 'alert-success' : 'alert-error'}`;
        alert.style.marginBottom = '20px';
        alert.innerHTML = `
            <i data-lucide="${data.success ? 'check-circle' : 'alert-circle'}"></i>
            ${data.message}
        `;
        container.appendChild(alert);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
    } catch (error) {
        console.error('Test failed:', error);
        Toast.show('Error al realizar la prueba de conexión.', 'error');
    } finally {
        btn.disabled = false;
        icon.classList.remove('spin-animation');
    }
}
</script>

<style>
.spin-animation {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
#button-preview {
    transition: all 0.2s ease;
}
#button-preview:hover {
    transform: scale(1.02);
}
</style>
