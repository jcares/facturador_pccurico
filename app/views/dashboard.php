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
        <div class="stat-meta text-primary">
            <i data-lucide="calendar" class="icon-sm"></i> Mes en curso
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

<div class="main-grid">
    <!-- Sales Chart -->
    <div class="glass-card">
        <h3 class="section-heading mb-20">
            <i data-lucide="bar-chart-2" class="text-primary"></i>
            Ventas (Últimos 6 meses)
        </h3>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Status Chart -->
    <div class="glass-card">
        <h3 class="section-heading mb-20">
            <i data-lucide="pie-chart" class="text-primary"></i>
            Estado de Facturas
        </h3>
        <div class="chart-container flex-center">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<div class="glass-card">
    <div class="flex-between mb-20 gap-12">
        <div>
            <h3 class="section-heading m-0">Acciones rápidas</h3>
            <p class="section-desc">Accede a las tareas clave desde el dashboard.</p>
        </div>
        <div class="action-buttons">
            <a href="invoices.php?action=create" class="btn-primary">Nueva factura</a>
            <a href="recurring_invoices.php" class="btn-secondary">Recurrentes</a>
            <a href="tools.php?action=export" class="btn-secondary">Exportar</a>
        </div>
    </div>
    <div class="stats-mini-grid">
        <div class="stat-mini-card">
            <div class="stat-mini-title">Tasa de conversión</div>
            <div class="stat-mini-value"><?= isset($stats['conversion_rate']) ? number_format($stats['conversion_rate'], 1, ',', '.') . '%' : 'N/A' ?></div>
        </div>
        <div class="stat-mini-card">
            <div class="stat-mini-title">Ticket promedio</div>
            <div class="stat-mini-value"><?= isset($stats['average_ticket']) ? '$' . number_format($stats['average_ticket'], 0, ',', '.') : 'N/A' ?></div>
        </div>
        <div class="stat-mini-card">
            <div class="stat-mini-title">Clientes con actividad</div>
            <div class="stat-mini-value"><?= number_format($stats['active_clients'] ?? ($stats['total_clients'] ?? 0), 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<script src="/assets/js/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared chart options for dark theme
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";

    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesLabels = <?= json_encode(array_values((array)($charts['sales']['labels'] ?? []))) ?>;
    const salesData = <?= json_encode(array_values((array)($charts['sales']['data'] ?? []))) ?>;

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
    const statusLabels = <?= json_encode(array_values((array)($charts['status']['labels'] ?? []))) ?>;
    const statusData = <?= json_encode(array_values((array)($charts['status']['data'] ?? []))) ?>;
    const statusColors = <?= json_encode(array_values((array)($charts['status']['colors'] ?? ['#22d3ee', '#7c3aed', '#38bdf8']))) ?>;

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

