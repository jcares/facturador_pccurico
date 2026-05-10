<?php
header('Content-Type: text/html; charset=utf-8');
$current = basename($_SERVER['PHP_SELF'] ?? '');
$isActive = function (array $files) use ($current) {
    return in_array($current, $files, true) ? 'active' : '';
};
$user = \Core\Auth::user();
$recurringInvoices = $recurringInvoices ?? [];
$recurringTotal = 0;
$nextRun = null;
foreach ($recurringInvoices as $r) {
    $recurringTotal += (float)($r['total'] ?? 0);
    if (!empty($r['next_run_date'])) {
        $date = strtotime($r['next_run_date']);
        if ($date && (!$nextRun || $date < $nextRun)) {
            $nextRun = $date;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas Recurrentes | FACTURADOR-PCCURICO</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <style>
        :root {
            color-scheme: dark;
            --bg: #050812;
            --surface: rgba(10, 16, 32, 0.96);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --primary: #22d3ee;
            --radius: 20px;
        }
        body { margin: 0; min-height: 100vh; background: #050812; color: var(--text-main); font-family: system-ui, sans-serif; }
        .app-layout { max-width: 1200px; margin: 0 auto; padding: 24px; }
        .glass-card { background: rgba(8, 12, 24, 0.95); border: 1px solid rgba(56, 189, 248, 0.08); border-radius: var(--radius); box-shadow: 0 20px 60px rgba(0,0,0,0.28); padding: 24px; }
        .section-heading { font-size: 1.15rem; margin: 0; }
        .button-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 22px; }
        .button-row a { display: inline-flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 999px; font-weight: 700; text-decoration: none; transition: transform 0.18s ease, box-shadow 0.18s ease; }
        .button-primary { background: linear-gradient(135deg, #22d3ee 0%, #7c3aed 100%); color: #020617; }
        .button-secondary { background: rgba(15, 23, 42, 0.9); color: #fff; border: 1px solid rgba(148, 163, 184, 0.14); }
        .button-row a:hover { transform: translateY(-1px); box-shadow: 0 16px 30px rgba(34,211,238,0.18); }
        .summary-strip { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
        .summary-pill { background: rgba(15, 23, 42, 0.92); border: 1px solid rgba(255,255,255,0.05); border-radius: 18px; padding: 18px; }
        .summary-pill .label { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.08em; }
        .summary-pill .value { font-size: 1.7rem; font-weight: 800; }
        .table-container { overflow-x: auto; }
        .table-clean { width: 100%; border-collapse: collapse; min-width: 720px; }
        .table-clean th, .table-clean td { padding: 16px 14px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .table-clean thead tr { background: rgba(15, 23, 42, 0.9); }
        .table-clean tbody tr:hover { background: rgba(255,255,255,0.04); }
        .text-center { text-align: center; }
        @media (max-width: 900px) { .summary-strip { grid-template-columns: 1fr; } .button-row { flex-direction: column; } .table-clean { min-width: 100%; } }
    </style>
    <script src="assets/js/lucide.min.js?v=20260506-charset"></script>
</head>
<body>
    <div class="app-layout">
        <div class="glass-card">
            <div class="flex-between" style="margin-bottom: 18px; gap: 16px; flex-wrap: wrap;">
                <div>
                    <h3 class="section-heading" style="margin: 0;">Facturas recurrentes</h3>
                    <p style="margin: 6px 0 0; color: var(--text-muted);">Gestiona ciclos, exportaciones e importaciones desde un solo lugar.</p>
                </div>
                <div class="button-row">
                    <a href="invoices.php?action=create" class="button-primary">
                        <i data-lucide="plus-circle"></i> Crear recurrente
                    </a>
                    <a href="tools.php?action=export" class="button-secondary">
                        <i data-lucide="download"></i> Exportar
                    </a>
                    <a href="tools.php" class="button-secondary">
                        <i data-lucide="upload"></i> Importar
                    </a>
                </div>
            </div>

            <div class="summary-strip">
                <div class="summary-pill">
                    <div class="label">Recurrencias totales</div>
                    <div class="value"><?= number_format(count($recurringInvoices), 0, ',', '.') ?></div>
                </div>
                <div class="summary-pill">
                    <div class="label">Valores programados</div>
                    <div class="value"><?= '$' . number_format($recurringTotal, 0, ',', '.') ?></div>
                </div>
                <div class="summary-pill">
                    <div class="label">Próxima emisión</div>
                    <div class="value"><?= $nextRun ? date('d/m/Y', $nextRun) : 'Sin datos' ?></div>
                </div>
            </div>

            <?php if(empty($recurringInvoices)): ?>
                <div class="text-center" style="padding: 40px; color: var(--text-muted);">
                    <i data-lucide="repeat" style="width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                    <p>No hay facturas recurrentes configuradas.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table-clean">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Frecuencia</th>
                                <th>Próxima emisión</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Ciclos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recurringInvoices as $r): ?>
                                <?php $currency = $r['currency'] ?? 'CLP'; ?>
                                <tr>
                                    <td class="highlight"><?= htmlspecialchars($r['client_name'] ?? 'Cliente') ?></td>
                                    <td><?= htmlspecialchars($r['frequency']) ?></td>
                                    <td style="color: var(--text-muted);">
                                        <?= !empty($r['next_run_date']) ? date('d/m/Y', strtotime($r['next_run_date'])) : '-' ?>
                                    </td>
                                    <td style="font-weight: 600;">
                                        <?= $currency === 'CLP' ? '$' : htmlspecialchars($currency) . ' ' ?><?= number_format((float)$r['total'], $currency === 'CLP' ? 0 : 2, ',', '.') ?>
                                    </td>
                                    <td><span class="status-badge status-sent"><?= htmlspecialchars($r['status'] ?? 'Pendiente') ?></span></td>
                                    <td>
                                        <?= (int)$r['cycles_generated'] ?><?= $r['remaining_cycles'] !== null ? ' / ' . (int)$r['remaining_cycles'] : ' / sin límite' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>
</html>
