<?php
$totalMonth = $totalMonth ?? 0;
$byCategory = $byCategory ?? [];
$expenses = $expenses ?? [];
?>
<div style="max-width: 1200px; margin: 0 auto;">
    <div class="glass-card" style="padding: 24px; margin-bottom: 24px;">
        <div class="flex-between" style="gap: 16px; flex-wrap: wrap;">
            <div>
                <h2 style="font-weight: 800; margin: 0; font-size: 1.3rem; color: #f8fafc;">Gestión de Gastos</h2>
                <p style="color: var(--text-muted); margin: 6px 0 0;">Controla y registra todos tus gastos operativos.</p>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="expenses.php?action=create" class="btn-primary" style="white-space: nowrap;">
                    <i data-lucide="plus"></i> Nuevo Gasto
                </a>
                <a href="tools.php?action=export" class="btn-secondary" style="white-space: nowrap;">
                    <i data-lucide="download"></i> Exportar
                </a>
                <a href="tools.php" class="btn-secondary" style="white-space: nowrap;">
                    <i data-lucide="upload"></i> Importar
                </a>
            </div>
        </div>
    </div>

    <?php if(isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?php if($_GET['success'] === 'created'): ?>Gasto registrado exitosamente.<?php endif; ?>
            <?php if($_GET['success'] === 'updated'): ?>Gasto actualizado exitosamente.<?php endif; ?>
            <?php if($_GET['success'] === 'deleted'): ?>Gasto eliminado exitosamente.<?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <?php if($_GET['error'] === 'invalid_id'): ?>ID de gasto inválido.<?php endif; ?>
            <?php if($_GET['error'] === 'not_found'): ?>Gasto no encontrado.<?php endif; ?>
            <?php if($_GET['error'] === 'empty_fields'): ?>Los campos obligatorios no pueden estar vacíos.<?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="glass-card" style="padding: 20px; text-align: center;">
            <i data-lucide="dollar-sign" style="width: 32px; height: 32px; color: var(--primary); margin-bottom: 8px;"></i>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">$<?= number_format($totalMonth, 0, ',', '.') ?></div>
            <div style="color: var(--text-muted); font-size: 0.9rem;">Gastos del Mes</div>
        </div>

        <div class="glass-card" style="padding: 20px; text-align: center;">
            <i data-lucide="pie-chart" style="width: 32px; height: 32px; color: #10b981; margin-bottom: 8px;"></i>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);"><?= count($byCategory) ?></div>
            <div style="color: var(--text-muted); font-size: 0.9rem;">Categorías</div>
        </div>

        <div class="glass-card" style="padding: 20px; text-align: center;">
            <i data-lucide="receipt" style="width: 32px; height: 32px; color: #f59e0b; margin-bottom: 8px;"></i>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);"><?= count($expenses) ?></div>
            <div style="color: var(--text-muted); font-size: 0.9rem;">Total Gastos</div>
        </div>
    </div>

    <!-- Expenses List -->
    <div class="glass-card">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px;">
            <?php foreach ($expenses as $expense): ?>
                <div class="expense-card" style="border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; background: rgba(255,255,255,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <div>
                            <h4 style="margin: 0; font-weight: 600; color: var(--text-main); font-size: 1rem;"><?= htmlspecialchars($expense['title']) ?></h4>
                            <?php if(!empty($expense['category'])): ?>
                                <span class="badge" style="background: #e5e7eb; color: var(--text-main); font-size: 0.75rem; margin-top: 4px;"><?= htmlspecialchars($expense['category']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main);">$<?= number_format($expense['amount'], 0, ',', '.') ?> <?= $expense['currency'] ?></div>
                            <?php if($expense['tax_deductible']): ?>
                                <span class="badge" style="background: #10b981; color: white; font-size: 0.7rem;">Deducible</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if(!empty($expense['description'])): ?>
                        <p style="color: var(--text-muted); margin: 8px 0; font-size: 0.9rem; line-height: 1.4;">
                            <?= htmlspecialchars(substr($expense['description'], 0, 80)) ?><?php if(strlen($expense['description']) > 80): ?>...<?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <small style="color: var(--text-muted);">Fecha: <?= date('d/m/Y', strtotime($expense['date'])) ?></small>
                            <?php if(!empty($expense['supplier'])): ?>
                                <small style="color: var(--text-muted);">Proveedor: <?= htmlspecialchars($expense['supplier']) ?></small>
                            <?php endif; ?>
                            <?php if(!empty($expense['payment_method'])): ?>
                                <small style="color: var(--text-muted);">Pago: <?= htmlspecialchars($expense['payment_method']) ?></small>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            <a href="expenses.php?action=edit&id=<?= $expense['id'] ?>" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Editar</a>
                            <form method="POST" action="expenses.php?action=delete" style="display: inline;" onsubmit="return confirm('¿Eliminar este gasto?')">
                                <?= \Core\Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $expense['id'] ?>">
                                <button type="submit" class="btn-secondary" style="padding: 6px 12px; font-size: 0.8rem; background: #ef4444; border-color: #ef4444;">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($expenses)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--text-muted);">
                    <i data-lucide="receipt" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <h3 style="margin: 0 0 8px; font-weight: 600;">No hay gastos registrados</h3>
                    <p style="margin: 0;">Registra tu primer gasto para comenzar a controlar tus finanzas.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>