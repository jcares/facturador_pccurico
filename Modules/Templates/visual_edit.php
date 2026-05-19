<style>
    .template-editor {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .editor-container {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 24px;
    }

    .editor-panel {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: 12px;
        padding: 20px;
        height: calc(100vh - 140px);
        overflow-y: auto;
    }

    .preview-panel {
        background: #f5f5f5;
        border: 1px solid var(--panel-border);
        border-radius: 12px;
        padding: 20px;
        height: calc(100vh - 140px);
        overflow-y: auto;
    }

    .block-item {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--panel-border);
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
    }

    .block-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .block-title {
        font-weight: 600;
        color: var(--text-main);
        font-size: 0.9rem;
    }

    .block-options {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--panel-border);
    }

    .block-option {
        margin-bottom: 8px;
    }

    .block-option label {
        display: block;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-bottom: 4px;
    }

    .block-option input[type="text"],
    .block-option input[type="number"] {
        width: 100%;
        padding: 6px 10px;
        font-size: 0.85rem;
    }

    .block-option input[type="checkbox"] {
        width: auto;
        margin-right: 6px;
    }

    .style-section {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid var(--panel-border);
    }

    .style-section h4 {
        margin: 0 0 12px 0;
        color: var(--text-main);
        font-size: 0.95rem;
    }

    .color-picker {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .color-picker input[type="color"] {
        width: 40px;
        height: 30px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .preview-frame {
        background: white;
        border-radius: 8px;
        padding: 30px;
        min-height: 600px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .editor-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid var(--panel-border);
    }

    .editor-actions button {
        flex: 1;
    }

    @media (max-width: 768px) {
        .template-editor {
            padding: 10px;
        }

        .editor-container {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .editor-panel,
        .preview-panel {
            height: auto;
            max-height: none;
        }

        .preview-frame {
            padding: 15px;
            min-height: 400px;
        }

        .editor-actions {
            flex-direction: column;
        }

        .block-option input[type="text"],
        .block-option input[type="number"] {
            font-size: 16px;
        }
    }
</style>

<div class="template-editor">
    <h2 style="margin: 0 0 20px 0; color: var(--text-main);">Editor de Plantilla: <?= htmlspecialchars($template['name']) ?></h2>

    <form id="template-form" method="POST" action="templates.php?action=save_visual&id=<?= (int)$template['id'] ?>">
        <?= \Core\Security::csrfField() ?>

        <div class="editor-container">
            <div class="editor-panel">
                <h3 style="margin: 0 0 15px 0; color: var(--primary);">Bloques</h3>

                <div id="blocks-container">
                    <?php foreach ($blocks as $index => $block): ?>
                        <div class="block-item" data-index="<?= $index ?>">
                            <div class="block-header">
                                <label class="block-title">
                                    <input type="checkbox" name="blocks[<?= $index ?>][enabled]" <?= $block['enabled'] ? 'checked' : '' ?>>
                                    <?= htmlspecialchars($block['label']) ?>
                                </label>
                                <i data-lucide="chevron-down" class="toggle-icon"></i>
                            </div>
                            <input type="hidden" name="blocks[<?= $index ?>][id]" value="<?= htmlspecialchars($block['id']) ?>">
                            <input type="hidden" name="blocks[<?= $index ?>][label]" value="<?= htmlspecialchars($block['label']) ?>">
                            <input type="hidden" name="blocks[<?= $index ?>][position]" value="<?= $block['position'] ?>">

                            <div class="block-options" style="display: none;">
                                <?php if ($block['id'] === 'company'): ?>
                                    <div class="block-option">
                                        <label><input type="checkbox" name="blocks[<?= $index ?>][options][show_logo]" <?= ($block['options']['show_logo'] ?? true) ? 'checked' : '' ?>> Mostrar Logo</label>
                                    </div>
                                    <div class="block-option">
                                        <label><input type="checkbox" name="blocks[<?= $index ?>][options][show_rut]" <?= ($block['options']['show_rut'] ?? true) ? 'checked' : '' ?>> Mostrar RUT</label>
                                    </div>
                                    <div class="block-option">
                                        <label>Ancho Logo (px)</label>
                                        <input type="number" name="blocks[<?= $index ?>][options][logo_width]" value="<?= $block['options']['logo_width'] ?? 150 ?>" min="50" max="300">
                                    </div>
                                    <div class="block-option">
                                        <label>Posición X (px)</label>
                                        <input type="number" name="blocks[<?= $index ?>][options][logo_x]" value="<?= $block['options']['logo_x'] ?? 0 ?>">
                                    </div>
                                    <div class="block-option">
                                        <label>Posición Y (px)</label>
                                        <input type="number" name="blocks[<?= $index ?>][options][logo_y]" value="<?= $block['options']['logo_y'] ?? 0 ?>">
                                    </div>
                                <?php elseif ($block['id'] === 'client'): ?>
                                    <div class="block-option">
                                        <label>Este bloque muestra los datos del cliente en la factura.</label>
                                    </div>
                                <?php elseif ($block['id'] === 'header'): ?>
                                    <div class="block-option">
                                        <label><input type="checkbox" name="blocks[<?= $index ?>][options][show_number]" <?= ($block['options']['show_number'] ?? true) ? 'checked' : '' ?>> Mostrar Número</label>
                                    </div>
                                    <div class="block-option">
                                        <label><input type="checkbox" name="blocks[<?= $index ?>][options][show_date]" <?= ($block['options']['show_date'] ?? true) ? 'checked' : '' ?>> Mostrar Fecha</label>
                                    </div>
                                <?php elseif ($block['id'] === 'items'): ?>
                                    <div class="block-option">
                                        <label><input type="checkbox" name="blocks[<?= $index ?>][options][show_sku]" <?= ($block['options']['show_sku'] ?? true) ? 'checked' : '' ?>> Mostrar SKU</label>
                                    </div>
                                    <div class="block-option">
                                        <label><input type="checkbox" name="blocks[<?= $index ?>][options][show_tax]" <?= ($block['options']['show_tax'] ?? true) ? 'checked' : '' ?>> Mostrar Impuestos</label>
                                    </div>
                                <?php elseif ($block['id'] === 'webpay_payment'): ?>
                                    <div class="block-option">
                                        <label>Texto del Botón</label>
                                        <input type="text" name="blocks[<?= $index ?>][options][text]" value="<?= htmlspecialchars($block['options']['text'] ?? 'Pagar con Webpay Plus') ?>">
                                    </div>
                                    <div class="block-option">
                                        <label>Ancho Botón (px)</label>
                                        <input type="number" name="blocks[<?= $index ?>][options][button_width]" value="<?= $block['options']['button_width'] ?? 200 ?>" min="150" max="300">
                                    </div>
                                <?php elseif ($block['id'] === 'notes'): ?>
                                    <div class="block-option">
                                        <label>Texto de Notas</label>
                                        <input type="text" name="blocks[<?= $index ?>][options][text]" value="<?= htmlspecialchars($block['options']['text'] ?? '') ?>">
                                    </div>
                                <?php elseif ($block['id'] === 'footer'): ?>
                                    <div class="block-option">
                                        <label>Texto del Pie</label>
                                        <input type="text" name="blocks[<?= $index ?>][options][text]" value="<?= htmlspecialchars($block['options']['text'] ?? '') ?>">
                                    </div>
                                <?php elseif ($block['id'] === 'greeting'): ?>
                                    <div class="block-option">
                                        <label>Texto de Saludo</label>
                                        <input type="text" name="blocks[<?= $index ?>][options][text]" value="<?= htmlspecialchars($block['options']['text'] ?? 'Hola,') ?>">
                                    </div>
                                <?php elseif ($block['id'] === 'message'): ?>
                                    <div class="block-option">
                                        <label>Mensaje</label>
                                        <input type="text" name="blocks[<?= $index ?>][options][text]" value="<?= htmlspecialchars($block['options']['text'] ?? 'Adjunto encontrará su documento.') ?>">
                                    </div>
                                <?php elseif ($block['id'] === 'button'): ?>
                                    <div class="block-option">
                                        <label>Texto del Botón</label>
                                        <input type="text" name="blocks[<?= $index ?>][options][text]" value="<?= htmlspecialchars($block['options']['text'] ?? 'Ver Documento') ?>">
                                    </div>
                                <?php elseif ($block['id'] === 'signature'): ?>
                                    <div class="block-option">
                                        <label>Texto de Firma</label>
                                        <input type="text" name="blocks[<?= $index ?>][options][text]" value="<?= htmlspecialchars($block['options']['text'] ?? 'Atentamente,') ?>">
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="style-section">
                    <h4>Estilos</h4>
                    <div class="block-option">
                        <label>Color Principal</label>
                        <div class="color-picker">
                            <input type="color" id="primary_color" name="primary_color" value="<?= htmlspecialchars($styles['primary_color']) ?>">
                            <span id="color-value"><?= htmlspecialchars($styles['primary_color']) ?></span>
                        </div>
                    </div>
                    <div class="block-option">
                        <label>Fuente</label>
                        <select name="font_family" style="width: 100%;">
                            <option value="sans-serif" <?= ($styles['font_family'] ?? 'sans-serif') === 'sans-serif' ? 'selected' : '' ?>>Sans Serif</option>
                            <option value="serif" <?= ($styles['font_family'] ?? '') === 'serif' ? 'selected' : '' ?>>Serif</option>
                        </select>
                    </div>
                </div>

                <div class="editor-actions">
                    <button type="button" id="preview-btn" class="btn-secondary">Vista Previa</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </div>

            <div class="preview-panel">
                <h3 style="margin: 0 0 15px 0; color: var(--text-main);">Vista Previa</h3>
                <div id="preview-content" class="preview-frame">
                    Cargando vista previa...
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle block options
    document.querySelectorAll('.block-header').forEach(header => {
        header.addEventListener('click', function() {
            const options = this.nextElementSibling;
            const icon = this.querySelector('.toggle-icon');
            if (options && options.classList.contains('block-options')) {
                options.style.display = options.style.display === 'none' ? 'block' : 'none';
                if (icon) {
                    icon.style.transform = options.style.display === 'none' ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        });
    });

    // Color picker update
    const colorPicker = document.getElementById('primary_color');
    const colorValue = document.getElementById('color-value');
    if (colorPicker && colorValue) {
        colorPicker.addEventListener('input', function() {
            colorValue.textContent = this.value;
        });
    }

    // Preview button
    const previewBtn = document.getElementById('preview-btn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            const form = document.getElementById('template-form');
            const formData = new FormData(form);

            fetch('templates.php?action=preview_visual', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('preview-content').innerHTML = html;
                if (window.lucide) lucide.createIcons();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    }

    // Initial preview
    if (previewBtn) {
        previewBtn.click();
    }
});
</script>
