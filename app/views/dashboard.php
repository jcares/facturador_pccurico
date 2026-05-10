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

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-title">Facturas recurrentes activas</div>
        <div class="summary-value"><?= number_format($stats['active_recurrences'] ?? 0, 0, ',', '.') ?></div>
        <div class="summary-small">Ciclos de facturación programados</div>
        <div class="progress-bar"><span style="width: <?= min(100, max(0, (int)($stats['active_recurrences'] ?? 0) * 2)) ?>%"></span></div>
    </div>
    <div class="summary-card">
        <div class="summary-title">Próxima emisión</div>
        <div class="summary-value"><?= !empty($stats['next_recurrence_date']) ? date('d/m/Y', strtotime($stats['next_recurrence_date'])) : 'Pendiente' ?></div>
        <div class="summary-small">Siguiente documento recurrente</div>
    </div>
    <div class="summary-card">
        <div class="summary-title">Exportaciones recientes</div>
        <div class="summary-value"><?= isset($stats['exports_last_30']) ? number_format($stats['exports_last_30'], 0, ',', '.') : 'N/A' ?></div>
        <div class="summary-small">Últimos 30 días</div>
    </div>
</div>

<div class="main-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Sales Chart -->
    <div class="glass-card">
        <h3 class="section-heading" style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
            <i data-lucide="bar-chart-2" style="color: var(--primary);"></i>
            Ventas (Últimos 6 meses)
        </h3>
        <div style="height: 300px; width: 100%;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Status Chart -->
    <div class="glass-card">
        <h3 class="section-heading" style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
            <i data-lucide="pie-chart" style="color: var(--primary);"></i>
            Estado de Facturas
        </h3>
        <div style="height: 300px; width: 100%; display: flex; justify-content: center;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="glass-card">
    <div class="flex-between" style="margin-bottom: 20px; gap: 12px;">
        <div>
            <h3 class="section-heading" style="margin: 0;">Acciones rápidas</h3>
            <p style="margin: 6px 0 0; color: var(--text-muted);">Accede a las tareas clave desde el dashboard.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="invoices.php?action=create" class="btn-primary">Nueva factura</a>
            <a href="recurring_invoices.php" class="btn-secondary">Recurrentes</a>
            <a href="tools.php?action=export" class="btn-secondary">Exportar</a>
        </div>
    </div>
    <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px;">
        <div style="background: rgba(15, 23, 42, 0.85); border-radius: 18px; padding: 18px;">
            <div style="font-size: 0.9rem; color: var(--text-muted);">Tasa de conversión</div>
            <div style="font-size: 1.5rem; font-weight: 800; margin-top: 10px;"><?= isset($stats['conversion_rate']) ? number_format($stats['conversion_rate'], 1, ',', '.') . '%' : 'N/A' ?></div>
        </div>
        <div style="background: rgba(15, 23, 42, 0.85); border-radius: 18px; padding: 18px;">
            <div style="font-size: 0.9rem; color: var(--text-muted);">Ticket promedio</div>
            <div style="font-size: 1.5rem; font-weight: 800; margin-top: 10px;"><?= isset($stats['average_ticket']) ? '$' . number_format($stats['average_ticket'], 0, ',', '.') : 'N/A' ?></div>
        </div>
        <div style="background: rgba(15, 23, 42, 0.85); border-radius: 18px; padding: 18px;">
            <div style="font-size: 0.9rem; color: var(--text-muted);">Clientes con actividad</div>
            <div style="font-size: 1.5rem; font-weight: 800; margin-top: 10px;"><?= number_format($stats['active_clients'] ?? ($stats['total_clients'] ?? 0), 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<script src="assets/js/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared chart options for dark theme
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";

    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesLabels = <?= json_encode($charts['sales']['labels'] ?? []) ?>;
    const salesData = <?= json_encode($charts['sales']['data'] ?? []) ?>;

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Ventas ($)',
                data: salesData,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: '#10b981',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#f8fafc',
                    bodyColor: '#f8fafc',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                    },
                    ticks: {
                        callback: function(value, index, values) {
                            return '$' + new Intl.NumberFormat('es-CL').format(value);
                        }
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false,
                    }
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = <?= json_encode($charts['status']['labels'] ?? []) ?>;
    const statusData = <?= json_encode($charts['status']['data'] ?? []) ?>;
    const statusColors = <?= json_encode($charts['status']['colors'] ?? ['#22d3ee', '#7c3aed', '#38bdf8']) ?>;

    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusColors,
                borderColor: 'rgba(30, 41, 59, 1)',
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#f8fafc',
                    bodyColor: '#f8fafc',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            }
        }
    });
});
</script>

