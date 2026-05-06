<div class="stat-grid">
    <div class="stat-card">
        <div class="flex-between">
            <div>
                <div class="stat-label">Ventas del Mes</div>
                <div class="stat-value">$<?= number_format($stats['month_total'] ?? 0, 0, ',', '.') ?></div>
            </div>
            <div class="icon-badge green">
                <i data-lucide="trending-up"></i>
            </div>
        </div>
        <div class="stat-meta" style="color: var(--primary);">
            <i data-lucide="calendar" style="width: 14px;"></i> Mes en curso
        </div>
    </div>

    <div class="stat-card">
        <div class="flex-between">
            <div>
                <div class="stat-label">Documentos Emitidos</div>
                <div class="stat-value"><?= number_format($stats['total_docs'] ?? 0, 0, ',', '.') ?></div>
            </div>
            <div class="icon-badge blue">
                <i data-lucide="file-text"></i>
            </div>
        </div>
        <div class="stat-meta">Facturas y Boletas</div>
    </div>

    <div class="stat-card">
        <div class="flex-between">
            <div>
                <div class="stat-label">Clientes Activos</div>
                <div class="stat-value"><?= number_format($stats['total_clients'] ?? 0, 0, ',', '.') ?></div>
            </div>
            <div class="icon-badge yellow">
                <i data-lucide="users"></i>
            </div>
        </div>
        <div class="stat-meta">Base de datos local</div>
    </div>
</div>

<div class="glass-card card-lg">
    <div class="flex-between" style="margin-bottom: 30px;">
        <h3 class="section-heading" style="display: flex; align-items: center; gap: 12px; margin: 0;">
            <i data-lucide="activity" style="color: var(--primary);"></i>
            Actividad Reciente
        </h3>
        <a href="invoices.php" class="btn-secondary link-button">Ver todo</a>
    </div>
    
    <div class="panel text-center">
        <div class="panel-icon">
            <i data-lucide="database-zap" style="width: 30px; height: 30px; opacity: 0.5;"></i>
        </div>
        <p class="section-heading" style="color: var(--text-main);">No hay transacciones hoy</p>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px;">Comienza emitiendo tu primer documento tributario.</p>
        <a href="invoices.php?action=create" class="btn-primary link-button">
            <i data-lucide="plus" style="width: 18px;"></i> Emitir Nueva Factura
        </a>
    </div>
</div>

