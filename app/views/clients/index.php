<?php $showForm = !empty($editing); ?>



<div class="glass-card">
    <div class="flex-between align-center flex-wrap gap-16">
        <h3 class="section-heading m-0">Directorio de Clientes</h3>
        <div class="action-buttons align-center">
            <?php if(!$showForm): ?>
                <button id="toggle-client-form" class="btn-primary whitespace-nowrap">+ Nuevo Clientes</button>
            <?php endif; ?>
            <a href="tools.php?action=export" class="btn-secondary whitespace-nowrap">Exportar</a>
            <a href="tools.php" class="btn-secondary whitespace-nowrap">Importar</a>
        </div>
    </div>

    <?php if(empty($clients)): ?>
        <p class="text-center text-muted p-20">No hay clientes registrados.</p>
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
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients as $c): ?>
                        <tr>
                            <td class="highlight"><?= htmlspecialchars($c['rut']) ?></td>
                            <td class="highlight text-primary"><?= htmlspecialchars($c['business_name']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($c['contact_name'] ?? '-') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                            <td class="text-muted"><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                            <td class="text-right">
                                <a href="clients.php?edit=<?= (int)$c['id'] ?>" class="text-primary no-underline" title="Editar">
                                    <i data-lucide="pencil"></i>
                                </a>
                                <a href="clients.php?action=delete&id=<?= (int)$c['id'] ?>" class="text-danger no-underline ml-10" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este cliente?')">
                                    <i data-lucide="trash-2"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards View -->
        <div class="mobile-cards">
            <?php foreach($clients as $c): ?>
                <div class="mobile-card">
                    <div class="mobile-card-header">
                        <div class="mobile-card-title"><?= htmlspecialchars($c['business_name']) ?></div>
                        <div class="mobile-card-subtitle">
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
                        <a href="clients.php?edit=<?= (int)$c['id'] ?>" class="btn-primary btn-sm flex-1">
                            <i data-lucide="pencil" class="icon-sm"></i> Editar
                        </a>
                        <a href="clients.php?action=delete&id=<?= (int)$c['id'] ?>" class="btn-secondary btn-sm btn-danger flex-1" onclick="return confirm('¿Estás seguro de eliminar este cliente?')">
                            <i data-lucide="trash-2" class="icon-sm"></i> Eliminar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div id="client-form-container" class="mt-24" style="display: <?= $showForm ? 'block' : 'none' ?>;">
        <h3 class="section-heading m-0"><?= !empty($editing) ? 'Editar Cliente' : 'Nuevo Cliente' ?></h3>
        <form action="clients.php?action=<?= !empty($editing) ? 'update' : 'store' ?>" method="POST">
            <?= \Core\Security::csrfField() ?>
            <?php if(!empty($editing)): ?>
                <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label for="rut">RUT *</label>
                <input type="text" id="rut" name="rut" required placeholder="12.345.678-9" value="<?= htmlspecialchars($editing['rut'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="business_name">Razón Social *</label>
                <input type="text" id="business_name" name="business_name" required placeholder="Empresa S.A." value="<?= htmlspecialchars($editing['business_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="contact_name">Nombre de Contacto</label>
                <input type="text" id="contact_name" name="contact_name" placeholder="Juan Pérez" value="<?= htmlspecialchars($editing['contact_name'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" placeholder="cliente@empresa.com" value="<?= htmlspecialchars($editing['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="text" id="phone" name="phone" placeholder="+56 9 1234 5678" value="<?= htmlspecialchars($editing['phone'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="address">Dirección</label>
                <input type="text" id="address" name="address" placeholder="Calle Principal 123, Ciudad" value="<?= htmlspecialchars($editing['address'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-primary"><?= !empty($editing) ? 'Actualizar Cliente' : 'Guardar Cliente' ?></button>
            <?php if(!empty($editing)): ?>
                <a href="clients.php" class="btn-primary btn-cancel">Cancelar</a>
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
