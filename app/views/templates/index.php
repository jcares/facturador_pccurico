<div class="glass-card" style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">Plantillas de Documentos</h2>
        <a href="templates.php?action=create" class="btn-primary" style="padding: 10px 15px; border-radius: 8px; text-decoration: none; color: white;">Crear Plantilla</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #10b981; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--glass-border); text-align: left;">
                <th style="padding: 15px;">Nombre</th>
                <th style="padding: 15px;">Tipo</th>
                <th style="padding: 15px;">Por Defecto</th>
                <th style="padding: 15px; text-align: right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($templates as $t): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 15px;"><?= htmlspecialchars($t['name']) ?></td>
                    <td style="padding: 15px;"><?= htmlspecialchars($t['type']) ?></td>
                    <td style="padding: 15px;">
                        <?php if ($t['is_default']): ?>
                            <span style="background: var(--primary); color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">Sí</span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td style="padding: 15px; text-align: right;">
                        <a href="templates.php?action=visual_edit&id=<?= $t['id'] ?>" style="color: var(--primary); margin-right: 15px; text-decoration: none;">🎨 Editar Visual</a>
                        <a href="templates.php?action=edit&id=<?= $t['id'] ?>" style="color: var(--text-main); text-decoration: none;">⚙️ Avanzado</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($templates)): ?>
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: var(--text-muted);">No hay plantillas registradas. Use la herramienta de sincronización de base de datos o cree una.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
