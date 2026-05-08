<div style="max-width: 1200px; margin: 0 auto;">
    <div class="flex-between" style="margin-bottom: 24px;">
        <div>
            <h2 style="font-weight: 800; margin: 0;">Gestión de Tareas</h2>
            <p style="color: var(--text-muted); margin: 6px 0 0;">Organiza y sigue tus tareas pendientes.</p>
        </div>
        <a href="tasks.php?action=create" class="btn-primary">Nueva Tarea</a>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php if($_GET['success'] === 'created'): ?>Tarea creada exitosamente.<?php endif; ?>
            <?php if($_GET['success'] === 'updated'): ?>Tarea actualizada exitosamente.<?php endif; ?>
            <?php if($_GET['success'] === 'deleted'): ?>Tarea eliminada exitosamente.<?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <?php if($_GET['error'] === 'invalid_id'): ?>ID de tarea inválido.<?php endif; ?>
            <?php if($_GET['error'] === 'not_found'): ?>Tarea no encontrada.<?php endif; ?>
            <?php if($_GET['error'] === 'empty_title'): ?>El título de la tarea es obligatorio.<?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="glass-card">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
            <?php foreach ($tasks as $task): ?>
                <div class="task-card" style="border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; background: rgba(255,255,255,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <h4 style="margin: 0; font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($task['title']) ?></h4>
                        <div style="display: flex; gap: 8px;">
                            <span class="badge" style="background: 
                                <?php if($task['priority'] === 'high'): ?>#ef4444<?php elseif($task['priority'] === 'medium'): ?>#f59e0b<?php else: ?>#10b981<?php endif; ?>; 
                                color: white;">
                                <?= ucfirst($task['priority']) ?>
                            </span>
                            <span class="badge" style="background: 
                                <?php if($task['status'] === 'completed'): ?>#10b981<?php elseif($task['status'] === 'in_progress'): ?>#3b82f6<?php elseif($task['status'] === 'cancelled'): ?>#6b7280<?php else: ?>#f59e0b<?php endif; ?>; 
                                color: white;">
                                <?php 
                                $statusLabels = [
                                    'pending' => 'Pendiente',
                                    'in_progress' => 'En Progreso',
                                    'completed' => 'Completada',
                                    'cancelled' => 'Cancelada'
                                ];
                                echo $statusLabels[$task['status']] ?? $task['status'];
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if(!empty($task['description'])): ?>
                        <p style="color: var(--text-muted); margin: 8px 0; font-size: 0.9rem; line-height: 1.4;">
                            <?= htmlspecialchars(substr($task['description'], 0, 100)) ?><?php if(strlen($task['description']) > 100): ?>...<?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php if(!empty($task['due_date'])): ?>
                        <div style="display: flex; align-items: center; gap: 6px; margin: 8px 0; color: var(--text-muted); font-size: 0.85rem;">
                            <i data-lucide="calendar" style="width: 14px; height: 14px;"></i>
                            Vence: <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
                        <small style="color: var(--text-muted);">Creada: <?= date('d/m/Y', strtotime($task['created_at'])) ?></small>
                        <div style="display: flex; gap: 8px;">
                            <a href="tasks.php?action=edit&id=<?= $task['id'] ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Editar</a>
                            <form method="POST" action="tasks.php?action=delete" style="display: inline;" onsubmit="return confirm('¿Eliminar esta tarea?')">
                                <?= \Core\Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $task['id'] ?>">
                                <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; background: #ef4444; border-color: #ef4444;">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($tasks)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <i data-lucide="check-square" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <h3 style="margin: 0 0 8px; font-weight: 600;">No hay tareas</h3>
                    <p style="margin: 0;">Crea tu primera tarea para comenzar a organizar tu trabajo.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>