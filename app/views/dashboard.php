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

<script src="assets/js/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Shared chart options for dark theme
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
    Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";

    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesLabels = <?= $charts['sales']['labels'] ?>;
    const salesData = <?= $charts['sales']['data'] ?>;

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
    const statusLabels = <?= $charts['status']['labels'] ?>;
    const statusData = <?= $charts['status']['data'] ?>;
    const statusColors = <?= $charts['status']['colors'] ?>;

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

