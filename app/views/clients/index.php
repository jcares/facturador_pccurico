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
            gap: 8px;
        }

        .mobile-card-actions a {
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
    <div style="display: flex; justify-content: space-between; gap: 16px; align-items: center; flex-wrap: wrap;">
        <h3 class="section-heading">Directorio de Clientes</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            <?php if(!$showForm): ?>
                <button id="toggle-client-form" class="btn-primary" style="white-space: nowrap;">+ Nuevo Clientes</button>
            <?php endif; ?>
            <a href="tools.php?action=export" class="btn-secondary" style="text-decoration: none; white-space: nowrap;">Exportar</a>
            <a href="tools.php" class="btn-secondary" style="text-decoration: none; white-space: nowrap;">Importar</a>
        </div>
    </div>

    <?php if(empty($clients)): ?>
        <p class="text-center" style="color: var(--text-muted); padding: 20px;">No hay clientes registrados.</p>
    <?php else: ?>
        <div class="table-container">
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>RUT</th>
                        <th>Razón Social</th>
                        <th>Nombre Contacto</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients as $c): ?>
                        <tr>
                            <td class="highlight"><?= htmlspecialchars($c['rut']) ?></td>
                            <td class="highlight" style="color: var(--primary);"><?= htmlspecialchars($c['business_name']) ?></td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($c['contact_name'] ?? '-') ?></td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                            <td style="text-align: right;">
                                <a href="clients.php?edit=<?= (int)$c['id'] ?>" style="color: var(--primary); text-decoration: none;" title="Editar">
                                    <i data-lucide="pencil"></i>
                                </a>
                                <a href="clients.php?action=delete&id=<?= (int)$c['id'] ?>" style="color: #ef4444; text-decoration: none; margin-left: 10px;" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este cliente?')">
                                    <i data-lucide="trash-2"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards View -->
        <div class="mobile-cards" style="display: none;">
            <?php foreach($clients as $c): ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title"><?= htmlspecialchars($c['business_name']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            RUT: <?= htmlspecialchars($c['rut']) ?>
                            <?php if(!empty($c['contact_name'])): ?>
                                | Contacto: <?= htmlspecialchars($c['contact_name']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="mobile-card-meta">
                        <?php if(!empty($c['email'])): ?>
                            <strong>Email:</strong> <?= htmlspecialchars($c['email']) ?><br>
                        <?php endif; ?>
                        <?php if(!empty($c['phone'])): ?>
                            <strong>Teléfono:</strong> <?= htmlspecialchars($c['phone']) ?><br>
                        <?php endif; ?>
                        <?php if(!empty($c['address'])): ?>
                            <strong>Dirección:</strong> <?= htmlspecialchars($c['address']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="mobile-card-actions">
                        <a href="clients.php?edit=<?= (int)$c['id'] ?>" class="btn-primary" style="flex: 1; text-align: center; padding: 8px; font-size: 0.8rem;">
                            <i data-lucide="pencil" style="width: 14px;"></i> Editar
                        </a>
                        <a href="clients.php?action=delete&id=<?= (int)$c['id'] ?>" class="btn-secondary" style="flex: 1; text-align: center; padding: 8px; font-size: 0.8rem; background: rgba(239,68,68,0.1); color: #ef4444; border-color: rgba(239,68,68,0.3);" onclick="return confirm('¿Estás seguro de eliminar este cliente?')">
                            <i data-lucide="trash-2" style="width: 14px;"></i> Eliminar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div id="client-form-container" style="display: <?= $showForm ? 'block' : 'none' ?>; margin-top: 24px;">
        <h3 class="section-heading"><?= !empty($editing) ? 'Editar Cliente' : 'Nuevo Cliente' ?></h3>
        <form action="clients.php?action=<?= !empty($editing) ? 'update' : 'store' ?>" method="POST">
            <?= \Core\Security::csrfField() ?>
            <?php if(!empty($editing)): ?>
                <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>RUT *</label>
                <input type="text" name="rut" required placeholder="12.345.678-9" value="<?= htmlspecialchars($editing['rut'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Razón Social *</label>
                <input type="text" name="business_name" required placeholder="Empresa S.A." value="<?= htmlspecialchars($editing['business_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Nombre de Contacto</label>
                <input type="text" name="contact_name" placeholder="Juan Pérez" value="<?= htmlspecialchars($editing['contact_name'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" placeholder="cliente@empresa.com" value="<?= htmlspecialchars($editing['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="phone" placeholder="+56 9 1234 5678" value="<?= htmlspecialchars($editing['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="address" placeholder="Calle Principal 123, Ciudad" value="<?= htmlspecialchars($editing['address'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-primary"><?= !empty($editing) ? 'Actualizar Cliente' : 'Guardar Cliente' ?></button>
            <?php if(!empty($editing)): ?>
                <a href="clients.php" class="btn-primary" style="display: block; margin-top: 10px; text-align: center; background: rgba(255,255,255,0.1); text-decoration: none;">Cancelar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toggleButton = document.getElementById('toggle-client-form');
        var formContainer = document.getElementById('client-form-container');

        if (toggleButton && formContainer) {
            toggleButton.addEventListener('click', function () {
                var isVisible = formContainer.style.display === 'block';
                formContainer.style.display = isVisible ? 'none' : 'block';
                toggleButton.textContent = isVisible ? '+ Nuevo Clientes' : 'Cerrar formulario';
            });
        }
    });
</script>
