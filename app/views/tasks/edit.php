<div style="max-width: 800px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Editar Tarea</h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;">Modifica los detalles de la tarea.</p>
        </div>
        <a href="tasks.php" class="btn-secondary">Volver</a>
    </div>

    <form action="tasks.php?action=edit&id=<?= $task['id'] ?>" method="POST" class="glass-card" style="padding: 32px;">
        <?= \Core\Security::csrfField() ?>

        <div class="form-group">
            <label for="title">Título de la Tarea *</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($task['title']) ?>" required placeholder="Ej: Revisar facturas pendientes">
            <small class="form-help">Describe brevemente la tarea a realizar.</small>
        </div>

        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" rows="4" placeholder="Detalles adicionales sobre la tarea..."><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
            <small class="form-help">Proporciona más contexto o instrucciones para completar la tarea.</small>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="status">Estado</label>
                <select id="status" name="status">
                    <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                    <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completada</option>
                    <option value="cancelled" <?= $task['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
                <small class="form-help">Estado actual de la tarea.</small>
            </div>

            <div class="form-group">
                <label for="priority">Prioridad</label>
                <select id="priority" name="priority">
                    <option value="low" <?= $task['priority'] === 'low' ? 'selected' : '' ?>>Baja</option>
                    <option value="medium" <?= $task['priority'] === 'medium' ? 'selected' : '' ?>>Media</option>
                    <option value="high" <?= $task['priority'] === 'high' ? 'selected' : '' ?>>Alta</option>
                </select>
                <small class="form-help">Nivel de importancia de la tarea.</small>
            </div>
        </div>

        <div class="form-group">
            <label for="due_date">Fecha de Vencimiento</label>
            <input type="date" id="due_date" name="due_date" value="<?= $task['due_date'] ?? '' ?>">
            <small class="form-help">Fecha límite para completar la tarea (opcional).</small>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 15px; margin-top: 24px;">Actualizar Tarea</button>
    </form>
</div>