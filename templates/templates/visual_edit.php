<div class="glass-card" style="max-width: 1300px; margin: 0 auto; padding: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--glass-border); padding-bottom: 25px;">
        <div>
            <h2 style="font-weight: 800; margin: 0; display: flex; align-items: center; gap: 15px;">
                <div style="width: 45px; height: 45px; background: rgba(16,185,129,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                    <i data-lucide="palette"></i>
                </div>
                Editor Visual de Plantilla
            </h2>
            <p style="color: var(--text-muted); margin: 8px 0 0 60px; font-size: 0.95rem;">Diseña documentos profesionales con previsualización en tiempo real.</p>
        </div>
        <div style="display: flex; gap: 15px;">
            <a href="templates.php" class="btn-secondary" style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="x" style="width: 18px;"></i> Cancelar
            </a>
            <button type="submit" form="visual-editor-form" class="btn-primary" style="width: auto; padding: 12px 25px; display: flex; align-items: center; gap: 8px; margin: 0;">
                <i data-lucide="save" style="width: 18px;"></i> Guardar Cambios
            </button>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 420px 1fr; gap: 40px; align-items: start;">
        
        <!-- Panel de Control (Izquierda) -->
        <div style="display: flex; flex-direction: column; gap: 25px;">
            <form action="templates.php?action=save_visual&id=<?= $template['id'] ?>" method="POST" id="visual-editor-form">
                <?= \Core\Security::csrfField() ?>
                
                <div style="margin-bottom: 25px;">
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; margin-bottom: 20px; color: var(--primary); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="layers" style="width: 16px;"></i> Componentes del Documento
                    </h3>
                    <div id="blocks-container" style="display: flex; flex-direction: column; gap: 15px;">
                        <?php foreach ($blocks as $index => $block): ?>
                            <div class="block-config-item" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px; transition: all 0.3s ease;" onmouseover="this.style.background='rgba(255,255,255,0.04)'" onmouseout="this.style.background='rgba(255,255,255,0.02)'">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="cursor: grab; color: var(--text-muted); opacity: 0.5;">
                                            <i data-lucide="grip-vertical" style="width: 18px;"></i>
                                        </div>
                                        <span style="font-weight: 700; font-size: 1rem; color: var(--text-main);"><?= htmlspecialchars($block['label']) ?></span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="hidden" name="blocks[<?= $index ?>][id]" value="<?= $block['id'] ?>">
                                        <input type="hidden" name="blocks[<?= $index ?>][label]" value="<?= $block['label'] ?>">
                                        <input type="hidden" name="blocks[<?= $index ?>][position]" value="<?= $block['position'] ?>" class="block-pos">
                                        <label class="switch">
                                            <input type="checkbox" name="blocks[<?= $index ?>][enabled]" <?= $block['enabled'] ? 'checked' : '' ?> onchange="updatePreview()">
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Opciones específicas del bloque -->
                                <div class="block-options" style="font-size: 0.85rem; color: var(--text-muted); display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.05);">
                                    <?php if ($block['id'] === 'company'): ?>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" name="blocks[<?= $index ?>][options][show_logo]" <?= ($block['options']['show_logo'] ?? true) ? 'checked' : '' ?> onchange="updatePreview()"> Mostrar Logo
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" name="blocks[<?= $index ?>][options][show_rut]" <?= ($block['options']['show_rut'] ?? true) ? 'checked' : '' ?> onchange="updatePreview()"> Mostrar RUT
                                        </label>
                                        <div style="grid-column: 1 / -1; margin-top: 10px; display: grid; grid-template-columns: 1fr; gap: 15px;">
                                            <div class="range-group">
                                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                    <span style="font-weight: 500;">Ancho del Logo</span>
                                                    <span style="color: var(--primary); font-weight: 700;" id="logo-size-val-<?= $index ?>"><?= htmlspecialchars($block['options']['logo_width'] ?? '150') ?>px</span>
                                                </div>
                                                <input type="range" name="blocks[<?= $index ?>][options][logo_width]" min="50" max="400" step="10" value="<?= htmlspecialchars($block['options']['logo_width'] ?? '150') ?>" class="modern-range" oninput="document.getElementById('logo-size-val-<?= $index ?>').innerText = this.value + 'px'; updatePreview()">
                                            </div>
                                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                                <div class="range-group">
                                                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                        <span>Eje X (↔)</span>
                                                        <span style="color: var(--primary);" id="logo-x-val-<?= $index ?>"><?= htmlspecialchars($block['options']['logo_x'] ?? '0') ?>px</span>
                                                    </div>
                                                    <input type="range" name="blocks[<?= $index ?>][options][logo_x]" min="-200" max="200" step="5" value="<?= htmlspecialchars($block['options']['logo_x'] ?? '0') ?>" class="modern-range" oninput="document.getElementById('logo-x-val-<?= $index ?>').innerText = this.value + 'px'; updatePreview()">
                                                    <?php
                                                    $logo = $data['settings']['biz_logo'] ?? '';
                                                    $logoWidth = (int)($options['logo_width'] ?? 150);
                                                    $logoX = (int)($options['logo_x'] ?? 0);
                                                    $logoY = (int)($options['logo_y'] ?? 0);
                                                    $logoPath = $logo ? 'uploads/' . $logo : 'assets/img/logo.png';
                                                    ?>
                                                </div>
                                                <div class="range-group">
                                                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                                        <span>Eje Y (↕)</span>
                                                        <span style="color: var(--primary);" id="logo-y-val-<?= $index ?>"><?= htmlspecialchars($block['options']['logo_y'] ?? '0') ?>px</span>
                                                    </div>
                                                    <input type="range" name="blocks[<?= $index ?>][options][logo_y]" min="-200" max="200" step="5" value="<?= htmlspecialchars($block['options']['logo_y'] ?? '0') ?>" class="modern-range" oninput="document.getElementById('logo-y-val-<?= $index ?>').innerText = this.value + 'px'; updatePreview()">
                                                </div>
                                            </div>
                                        </div>
                                    <?php elseif ($block['id'] === 'items'): ?>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" name="blocks[<?= $index ?>][options][show_sku]" <?= ($block['options']['show_sku'] ?? true) ? 'checked' : '' ?> onchange="updatePreview()"> Mostrar SKU
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                            <input type="checkbox" name="blocks[<?= $index ?>][options][show_tax]" <?= ($block['options']['show_tax'] ?? true) ? 'checked' : '' ?> onchange="updatePreview()"> Mostrar IVA
                                        </label>
                                    <?php elseif (in_array($block['id'], ['footer', 'notes', 'greeting', 'message', 'button', 'signature'])): ?>
                                        <div style="grid-column: 1 / -1;">
                                            <label style="display: block; margin-bottom: 8px;">Contenido Personalizado:</label>
                                            <textarea name="blocks[<?= $index ?>][options][text]" style="width: 100%; height: 70px; background: rgba(0,0,0,0.3); color: white; border: 1px solid var(--glass-border); border-radius: 10px; padding: 12px; font-size: 0.85rem; font-family: inherit; resize: vertical;" placeholder="Escribe aquí el contenido..." oninput="updatePreview()"><?= htmlspecialchars($block['options']['text'] ?? '') ?></textarea>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="glass-card" style="padding: 25px; border-radius: 20px; background: rgba(255,255,255,0.02);">
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 800; margin-bottom: 20px; color: var(--primary); display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="brush" style="width: 16px;"></i> Estilos Visuales
                    </h3>
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="margin-bottom: 12px;">Color de Identidad</label>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <input type="color" name="primary_color" value="<?= htmlspecialchars($styles['primary_color'] ?? '#3b82f6') ?>" style="width: 60px; height: 40px; border: 2px solid var(--glass-border); border-radius: 8px; padding: 2px; background: rgba(0,0,0,0.3); cursor: pointer;" onchange="updatePreview()">
                            <input type="text" value="<?= htmlspecialchars($styles['primary_color'] ?? '#3b82f6') ?>" style="flex: 1; padding: 10px; font-family: monospace; font-size: 0.9rem; text-align: center; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="margin-bottom: 12px;">Familia Tipográfica</label>
                        <div style="position: relative;">
                            <select name="font_family" style="width: 100%; padding: 12px 15px; background: rgba(15,23,42,0.8); color: white; border: 1px solid var(--glass-border); border-radius: 10px; appearance: none; cursor: pointer;" onchange="updatePreview()">
                                <option value="sans-serif" <?= ($styles['font_family'] ?? 'sans-serif') === 'sans-serif' ? 'selected' : '' ?>>Moderno (Inter / Sans)</option>
                                <option value="serif" <?= ($styles['font_family'] ?? '') === 'serif' ? 'selected' : '' ?>>Clásico (Georgia / Serif)</option>
                                <option value="monospace" <?= ($styles['font_family'] ?? '') === 'monospace' ? 'selected' : '' ?>>Monoespaciado (Roboto Mono)</option>
                            </select>
                            <div style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.5;">
                                <i data-lucide="chevron-down" style="width: 16px;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Previsualización (Derecha) -->
        <div style="position: sticky; top: 30px;">
            <div style="background: rgba(30, 41, 59, 0.95); padding: 15px 25px; border-radius: 20px 20px 0 0; border: 1px solid var(--glass-border); border-bottom: none; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="display: flex; gap: 6px;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #ff5f56; box-shadow: 0 0 5px rgba(255,95,86,0.3);"></div>
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #ffbd2e; box-shadow: 0 0 5px rgba(255,189,46,0.3);"></div>
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #27c93f; box-shadow: 0 0 5px rgba(39,201,63,0.3);"></div>
                    </div>
                    <span style="margin-left: 15px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Live Preview</span>
                </div>
                <div style="font-size: 0.75rem; color: var(--primary); font-weight: 700; background: rgba(16,185,129,0.1); padding: 4px 12px; border-radius: 10px;">A4 Document</div>
            </div>
            <div id="preview-frame-container" style="background: #e2e8f0; min-height: 850px; border-radius: 0 0 20px 20px; overflow: hidden; position: relative; border: 1px solid var(--glass-border); padding: 40px; display: flex; justify-content: center;">
                <div id="preview-frame" style="background: white; width: 100%; max-width: 800px; min-height: 1000px; box-shadow: 0 30px 60px rgba(0,0,0,0.2); border-radius: 2px;">
                    <!-- Content loaded via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modern Switch UI */
    .switch { position: relative; display: inline-block; width: 44px; height: 22px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(255,255,255,0.1); transition: .4s; border-radius: 34px; border: 1px solid var(--glass-border); }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    input:checked + .slider { background-color: var(--primary); border-color: var(--primary); }
    input:checked + .slider:before { transform: translateX(22px); }

    /* Modern Range UI */
    .modern-range {
        -webkit-appearance: none;
        width: 100%;
        height: 6px;
        background: rgba(255,255,255,0.1);
        border-radius: 5px;
        outline: none;
    }
    .modern-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 18px;
        height: 18px;
        background: var(--primary);
        cursor: pointer;
        border-radius: 50%;
        border: 3px solid #1e293b;
        box-shadow: 0 0 0 1px var(--primary);
    }
    
    /* Animation */
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .block-config-item { animation: fadeIn 0.4s ease forwards; }
</style>

<script>
    function updatePreview() {
        const form = document.getElementById('visual-editor-form');
        const formData = new FormData(form);
        
        // Add loading state
        document.getElementById('preview-frame').style.opacity = '0.6';
        
        fetch('templates.php?action=preview_visual', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            const frame = document.getElementById('preview-frame');
            frame.innerHTML = html;
            frame.style.opacity = '1';
            
            // Re-run lucide in preview if needed (though preview usually has its own styles)
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        updatePreview();
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>

