<div style="display: flex; gap: 30px;">
    <!-- Lista de Clientes -->
    <div class="glass-card" style="flex: 2;">
        <h3 class="section-heading">Directorio de Clientes</h3>
        <?php if(empty($clients)): ?>
            <p class="text-center" style="color: var(--text-muted); padding: 20px;">No hay clientes registrados.</p>
        <?php else: ?>
            <table class="table-clean">
                <thead>
                    <tr>
                        <th>RUT</th>
                        <th>Razón Social / Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients as $c): ?>
                        <tr>
                            <td class="highlight"><?= htmlspecialchars($c['rut']) ?></td>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($c['email'] ?? '-') ?></td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($c['phone'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Formulario Crear -->
    <div class="glass-card" style="flex: 1; align-self: flex-start;">
        <h3 class="section-heading">Nuevo Cliente</h3>
        <form action="clients.php?action=store" method="POST">
            <?= \Core\Security::csrfField() ?>
            <div class="form-group">
                <label>RUT *</label>
                <input type="text" name="rut" required placeholder="12.345.678-9">
            </div>
            <div class="form-group">
                <label>Razón Social / Nombre *</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="phone">
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="address">
            </div>
            <button type="submit" class="btn-primary">Guardar Cliente</button>
        </form>
    </div>
</div>
