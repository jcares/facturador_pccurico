<?php $showForm = !empty($editing); ?>

<style>
    @media (max-width: 900px) {
        .table-container {
            display: none !important;
        }

        .mobile-cards {
            display: block !important;
        }

        .glass-card {
            padding: 20px;
        }

        .mobile-card {
            margin-bottom: 16px;
        }

        .mobile-card-actions {
            flex-direction: column;
            gap: 10px;
        }

        .mobile-card-actions a,
        .mobile-card-actions form {
            width: 100%;
        }
    }

    @media (min-width: 901px) {
        .mobile-cards {
            display: none !important;
        }
    }
</style>

<div class="glass-card">
    <div style="display: flex; justify-content: space-between; gap: 16px; align-items: center; flex-wrap: wrap; margin-bottom: 24px;">
        <h3 class="section-heading">Categorias de Productos</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            <?php if(!$showForm): ?>
                <button id="toggle-category-form" class="btn-primary" style="white-space: nowrap;">+ Nueva Categoria</button>
            <?php endif; ?>
            <a href="tools.php?action=export" class="btn-secondary" style="text-decoration: none; white-space: nowrap;">Exportar</a>
            <a href="tools.php" class="btn-secondary" style="text-decoration: none; white-space: nowrap;">Importar</a>
        </div>
    </div>

    <?php if(empty($categories)): ?>
        <div class="text-center" style="padding: 40px; color: var(--text-muted);">
            <i data-lucide="folder" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
            <p>No hay categorias registradas.</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Productos</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories as $c): ?>
                        <?php $isChild = !empty($c['parent_id']); ?>
                        <tr>
                            <td class="highlight" style="color: var(--primary);">
                                <?php if ($isChild): ?>
                                    <?= htmlspecialchars($c['parent_name'] ?? 'Padre') ?> / <?= htmlspecialchars($c['name']) ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($c['name']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge"><?= $isChild ? 'Hija' : 'Padre' ?></span>
                            </td>
                            <td>
                                <span class="status-badge">
                                    <?= (int)$c['product_count'] ?> productos
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="categories.php?edit=<?= (int)$c['id'] ?>" style="color: var(--primary); text-decoration: none;" title="Editar">
                                    <i data-lucide="pencil"></i>
                                </a>
                                <form action="categories.php?action=delete" method="POST" style="display: inline; margin-left: 10px;" onsubmit="return confirm('Eliminar esta categoria? Esta accion no se puede deshacer.');">
                                    <?= \Core\Security::csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 0;" title="Eliminar">
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mobile-cards" style="display: none;">
            <?php foreach($categories as $c): ?>
                <?php $isChild = !empty($c['parent_id']); ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title">
                            <?= $isChild ? htmlspecialchars(($c['parent_name'] ?? 'Padre') . ' / ' . $c['name']) : htmlspecialchars($c['name']) ?>
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            <?= $isChild ? 'Hija' : 'Padre' ?> · <?= (int)$c['product_count'] ?> productos
                        </div>
                    </div>
                    <div class="mobile-card-actions">
                        <a href="categories.php?edit=<?= (int)$c['id'] ?>" class="btn-primary" style="flex: 1; text-align: center; padding: 8px; font-size: 0.8rem;">
                            <i data-lucide="pencil" style="width: 14px;"></i> Editar
                        </a>
                        <form action="categories.php?action=delete" method="POST" style="flex: 1;" onsubmit="return confirm('Eliminar esta categoria? Esta accion no se puede deshacer.');">
                            <?= \Core\Security::csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button type="submit" class="btn-secondary" style="width: 100%; background: rgba(239,68,68,0.1); color: #ef4444; border-color: rgba(239,68,68,0.3); padding: 8px; font-size: 0.8rem;">
                                <i data-lucide="trash-2" style="width: 14px;"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div id="category-form-container" style="display: <?= $showForm ? 'block' : 'none' ?>; margin-top: 24px;">
        <div class="glass-card" style="padding: 25px;">
            <h3 class="section-heading">
                <?= !empty($editing) ? 'Editar Categoria' : 'Nueva Categoria' ?>
            </h3>
            <form action="categories.php?action=<?= !empty($editing) ? 'update' : 'store' ?>" method="POST">
                <?= \Core\Security::csrfField() ?>
                <?php if(!empty($editing)): ?>
                    <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Categoria padre</label>
                    <select name="parent_id">
                        <option value="">Sin padre / categoria superior</option>
                        <?php foreach(($parentCategories ?? []) as $parent): ?>
                            <?php if (!empty($editing) && (int)$editing['id'] === (int)$parent['id']) continue; ?>
                            <option value="<?= (int)$parent['id'] ?>" <?= !empty($editing) && (int)($editing['parent_id'] ?? 0) === (int)$parent['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($parent['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nombre de la Categoria *</label>
                    <input type="text" name="name" required placeholder="Ej: Servicios, Hardware, Mantencion" value="<?= htmlspecialchars($editing['name'] ?? '') ?>">
                </div>
                <button type="submit" class="btn-primary">
                    <?= !empty($editing) ? 'Actualizar Categoria' : 'Guardar Categoria' ?>
                </button>
                <?php if(!empty($editing)): ?>
                    <a href="categories.php" class="btn-primary" style="display: block; margin-top: 10px; text-align: center; background: rgba(255,255,255,0.1); text-decoration: none;">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleButton = document.getElementById('toggle-category-form');
        var formContainer = document.getElementById('category-form-container');

        if (toggleButton && formContainer) {
            toggleButton.addEventListener('click', function () {
                var isVisible = formContainer.style.display === 'block';
                formContainer.style.display = isVisible ? 'none' : 'block';
                toggleButton.textContent = isVisible ? '+ Nueva Categoria' : 'Cerrar formulario';
            });
        }
    });
</script>
