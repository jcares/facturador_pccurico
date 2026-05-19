<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/run_migrations.php';

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\View;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? 'index';
$standalone = !isset($contentFile);

// Shared logic for all tool actions
function getToolContent(string $action): array
{
    $logPath = ROOT_PATH . '/storage/logs/error.log';
    $logExists = file_exists($logPath);
    $cleared = isset($_GET['cleared']);

    switch ($action) {
        case 'clear_log':
            Security::validatePost();
            if ($logExists) {
                file_put_contents($logPath, '');
            }
            header('Location: tools.php?action=log&cleared=1');
            exit;

        case 'log':
            ob_start();
            ?>
            <div class="flex-between mb-20" style="gap: 15px; flex-wrap: wrap;">
                <div style="display: flex; gap: 10px; flex: 1; min-width: 300px;">
                    <div class="form-group" style="flex: 1; margin: 0;">
                        <input type="text" id="logSearch" class="form-control" placeholder="Buscar en el historial..." onkeyup="filterLogs()">
                    </div>
                    <div class="form-group" style="width: 160px; margin: 0;">
                        <select id="levelFilter" class="form-control" onchange="filterLogs()">
                            <option value="all">Todos los niveles</option>
                            <option value="ERROR">ERROR</option>
                            <option value="WARNING">WARNING</option>
                            <option value="INFO">INFO</option>
                        </select>
                    </div>
                </div>
                <form action="tools.php?action=clear_log" method="POST" onsubmit="return confirm('¿Confirma que desea borrar todos los registros históricos?')">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn-secondary" style="color: #ef4444; border-color: rgba(239,68,68,0.2);">
                        <i data-lucide="trash-2"></i> Limpiar Logs
                    </button>
                </form>
            </div>

            <?php if ($cleared): ?>
                <div class="alert alert-success mb-20">Registros eliminados correctamente.</div>
            <?php endif; ?>

            <div class="table-container" style="max-height: 550px; overflow-y: auto;">
                <table class="table-clean" style="font-size: 0.85rem;">
                    <thead style="position: sticky; top: 0; z-index: 10; background: #0f172a;">
                        <tr>
                            <th style="width: 180px;">Fecha / Hora</th>
                            <th style="width: 100px;">Nivel</th>
                            <th>Mensaje de Error / Sistema</th>
                        </tr>
                    </thead>
                    <tbody id="logBody">
                    <?php
                    if ($logExists) {
                        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        $lines = array_reverse(array_slice($lines, -500)); // Last 500 lines for performance
                        foreach ($lines as $line) {
                            if (preg_match('/^\[(.*?)\] (.*?): (.*)$/', $line, $m)) {
                                $date = $m[1];
                                $level = trim($m[2]);
                                $msg = $m[3];
                                $statusClass = match($level) {
                                    'ERROR' => 'status-canceled',
                                    'WARNING' => 'status-warning',
                                    'INFO' => 'status-sent',
                                    default => 'status-sent'
                                };
                                ?>
                                <tr class="log-row" data-level="<?= $level ?>">
                                    <td style="white-space: nowrap; font-family: monospace; color: var(--text-muted);"><?= $date ?></td>
                                    <td>
                                        <span class="status-badge <?= $statusClass ?>" style="font-size: 0.65rem; padding: 2px 6px; font-weight: 800;">
                                            <?= $level ?>
                                        </span>
                                    </td>
                                    <td class="log-msg" style="word-break: break-word; line-height: 1.5; color: var(--text-subtle);">
                                        <?= htmlspecialchars($msg) ?>
                                    </td>
                                </tr>
                                <?php
                            } else {
                                ?>
                                <tr class="log-row" data-level="OTHER">
                                    <td colspan="2"></td>
                                    <td style="color: var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($line) ?></td>
                                </tr>
                                <?php
                            }
                        }
                    } else {
                        echo "<tr><td colspan='3' style='padding: 60px; text-align: center; color: var(--text-muted);'>No hay registros de errores disponibles.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <script>
            function filterLogs() {
                const search = document.getElementById('logSearch').value.toLowerCase();
                const level = document.getElementById('levelFilter').value;
                const rows = document.querySelectorAll('.log-row');
                rows.forEach(row => {
                    const msg = row.querySelector('.log-msg')?.textContent.toLowerCase() || '';
                    const rowLevel = row.dataset.level;
                    const matchesSearch = msg.includes(search);
                    const matchesLevel = (level === 'all' || rowLevel === level);
                    row.style.display = (matchesSearch && matchesLevel) ? '' : 'none';
                });
            }
            </script>
            <?php
            return ['title' => 'Registros del Sistema', 'icon' => 'file-warning', 'body' => ob_get_clean()];

        case 'sync':
            try {
                $result = runDatabaseMigrations();
                $body = toolDetailList($result['details']);
                $notice = ['type' => 'success', 'title' => 'Sincronizacion completada', 'message' => $result['message']];
            } catch (Throwable $e) {
                \Core\Logger::error('Migration runner failed: ' . $e->getMessage());
                $body = '<p style="color: var(--text-muted);">Revisa el log de errores para ver el detalle tecnico.</p>';
                $notice = ['type' => 'error', 'title' => 'No se pudo sincronizar', 'message' => $e->getMessage()];
            }
            return ['title' => 'Sincronizar Base de Datos', 'icon' => 'database', 'body' => $body, 'notice' => $notice];

        case 'permissions':
            $dirs = [ROOT_PATH . '/storage', ROOT_PATH . '/storage/logs', ROOT_PATH . '/storage/cache', ROOT_PATH . '/storage/backups', __DIR__ . '/uploads'];
            $details = [];
            foreach ($dirs as $dir) {
                if (!is_dir($dir)) @mkdir($dir, 0777, true);
                @chmod($dir, 0777);
                $details[] = (is_writable($dir) ? 'Escritura OK: ' : 'Revisar permisos: ') . str_replace(ROOT_PATH, '', $dir);
            }
            return ['title' => 'Corregir Permisos', 'icon' => 'shield-check', 'body' => toolDetailList($details), 'notice' => ['type' => 'success', 'title' => 'Permisos revisados', 'message' => 'Carpetas críticas verificadas.']];

        case 'cleanup':
            $files = array_merge(glob(__DIR__ . '/migrate_v*.php') ?: [], [__DIR__ . '/check_templates.php']);
            $details = [];
            $deletedCount = 0;
            foreach ($files as $file) {
                if (file_exists($file) && @unlink($file)) {
                    $deletedCount++;
                    $details[] = "Archivo eliminado: " . basename($file);
                }
            }
            try {
                $db = Database::getInstance();
                $templates = $db->query('SELECT id, name, type FROM document_templates ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
                $seen = [];
                foreach ($templates as $template) {
                    $key = $template['name'] . '_' . $template['type'];
                    if (isset($seen[$key])) {
                        $db->prepare('DELETE FROM document_templates WHERE id = ?')->execute([$template['id']]);
                        $deletedCount++;
                        $details[] = 'Plantilla duplicada eliminada: ' . $template['name'];
                    } else { $seen[$key] = true; }
                }
            } catch (Throwable $e) { $details[] = 'No se pudo revisar plantillas duplicadas.'; }
            return ['title' => 'Limpiar Sistema', 'icon' => 'sparkles', 'body' => toolDetailList($details ?: ['No habia nada que limpiar.']), 'notice' => ['type' => 'success', 'title' => 'Limpieza finalizada', 'message' => "{$deletedCount} elementos removidos."]];

        case 'diagnostic':
            $phpOk = PHP_VERSION_ID >= 80000;
            $extensions = ['pdo_mysql' => 'Base de Datos PDO', 'curl' => 'Transferencia CURL', 'mbstring' => 'Cadenas MBString', 'openssl' => 'Seguridad OpenSSL', 'gd' => 'Imagenes GD', 'json' => 'Datos JSON'];
            $dirs = [ROOT_PATH . '/storage' => 'Almacenamiento interno', __DIR__ . '/uploads' => 'Carga de archivos', ROOT_PATH . '/bootstrap/cache' => 'Cache de sistema'];
            ob_start();
            ?>
            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 24px;">
                <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 8px; border: 1px solid var(--glass-border);">
                    <div style="font-weight: 800;">Version de PHP</div>
                    <div style="color: var(--text-muted); margin-top: 4px;"><?= htmlspecialchars(PHP_VERSION) ?></div>
                    <div style="color: <?= $phpOk ? '#10b981' : '#ef4444' ?>; font-weight: 800; margin-top: 10px;"><?= $phpOk ? 'Compatible' : 'Actualizar recomendado' ?></div>
                </div>
                <?php
                try { Database::getInstance(); $dbMessage = 'Conexion exitosa.'; $dbOk = true; } catch (Throwable $e) { $dbMessage = $e->getMessage(); $dbOk = false; }
                ?>
                <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 8px; border: 1px solid var(--glass-border);">
                    <div style="font-weight: 800;">Base de datos</div>
                    <div style="color: var(--text-muted); margin-top: 4px;"><?= htmlspecialchars($dbMessage) ?></div>
                    <div style="color: <?= $dbOk ? '#10b981' : '#ef4444' ?>; font-weight: 800; margin-top: 10px;"><?= $dbOk ? 'Operativa' : 'Con error' ?></div>
                </div>
            </div>
            <h3 style="font-size: 0.95rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px;">Extensiones</h3>
            <?= toolDetailList(array_map(fn($l, $e) => (extension_loaded($e) ? 'OK: ' : 'Falta: ') . $l, $extensions, array_keys($extensions))) ?>
            <h3 style="font-size: 0.95rem; color: var(--text-muted); text-transform: uppercase; margin: 24px 0 12px;">Carpetas</h3>
            <?= toolDetailList(array_map(fn($l, $p) => (is_writable($p) ? 'Escritura OK: ' : 'Sin escritura: ') . $l, $dirs, array_keys($dirs))) ?>
            <?php
            return ['title' => 'Diagnostico', 'icon' => 'activity', 'body' => ob_get_clean()];

        case 'export':
            $db = Database::getInstance();
            $data = ['clients' => $db->query("SELECT * FROM clients")->fetchAll(), 'invoices' => $db->query("SELECT * FROM invoices")->fetchAll(), 'products' => $db->query("SELECT * FROM products")->fetchAll(), 'exported_at' => date('Y-m-d H:i:s')];
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="backup_' . date('Ymd') . '.json"');
            echo json_encode($data, JSON_PRETTY_PRINT);
            exit;

        default:
            $tools = [
                ['diagnostic', 'activity', 'Diagnostico del Sistema', 'Verifica PHP, extensiones, permisos y conexion a base de datos.'],
                ['sync', 'database', 'Sincronizar Esquema BD', 'Aplica migraciones pendientes.'],
                ['export', 'download', 'Exportar Datos JSON', 'Descarga un respaldo completo.'],
                ['permissions', 'shield-check', 'Corregir Permisos', 'Restablece permisos de escritura.'],
                ['cleanup', 'sparkles', 'Limpiar Sistema', 'Elimina archivos temporales.'],
                ['log', 'file-warning', 'Ver Log de Errores', 'Revisa los últimos errores.'],
            ];
            ob_start();
            ?>
            <div style="max-width: 980px; margin: 0 auto;">
                <div style="margin-bottom: 28px;">
                    <h2 style="font-weight: 800; margin: 0;">Herramientas del Sistema</h2>
                    <p style="color: var(--text-muted); margin-top: 6px;">Mantenimiento y utilidades.</p>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                    <?php foreach ($tools as [$tAct, $icon, $label, $desc]): ?>
                        <a href="tools.php?action=<?= $tAct ?>" style="text-decoration: none;">
                            <div class="glass-card" style="padding: 22px; min-height: 142px; cursor: pointer; border: 1px solid var(--glass-border); transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.transform='translateY(-1px)'" onmouseout="this.style.borderColor='var(--glass-border)'; this.style.transform='none'">
                                <i data-lucide="<?= $icon ?>" style="width: 30px; height: 30px; color: var(--primary); margin-bottom: 16px;"></i>
                                <h3 style="margin: 0 0 8px 0; font-size: 1rem; color: var(--text-main);"><?= $label ?></h3>
                                <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; line-height: 1.45;"><?= $desc ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
            return ['title' => 'Herramientas', 'icon' => 'box', 'body' => ob_get_clean()];
    }
}

function renderToolShell(string $title, string $icon, string $body, ?array $notice = null): void
{
    global $standalone;
    ob_start();
    ?>
    <div class="glass-card" style="max-width: 980px; margin: 0 auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-bottom: 26px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <i data-lucide="<?= htmlspecialchars($icon) ?>" style="width: 26px; height: 26px; color: var(--primary);"></i>
                <h2 style="font-weight: 800; margin: 0;"><?= htmlspecialchars($title) ?></h2>
            </div>
            <a href="tools.php" class="btn-secondary" style="text-decoration: none;">Volver</a>
        </div>
        <?php if ($notice): ?>
            <?php $nType = $notice['type'] === 'error' ? '239,68,68' : '16,185,129'; ?>
            <div class="alert" style="background: rgba(<?= $nType ?>,0.08); border: 1px solid rgba(<?= $nType ?>,0.25); color: var(--text-main); padding: 16px 18px; border-radius: 10px; margin-bottom: 22px;">
                <div style="font-weight: 800; margin-bottom: 4px;"><?= htmlspecialchars($notice['title']) ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($notice['message']) ?></div>
            </div>
        <?php endif; ?>
        <?= $body ?>
    </div>
    <?php
    if ($standalone) {
        View::renderRaw(ob_get_clean(), ['title' => $title]);
    } else {
        echo ob_get_clean();
    }
}

function toolDetailList(array $items): string
{
    ob_start();
    ?>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach ($items as $item): ?>
            <div style="display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.03); padding: 13px 15px; border-radius: 8px; border: 1px solid var(--glass-border);">
                <i data-lucide="check-circle" style="width: 18px; height: 18px; color: #10b981; flex-shrink: 0;"></i>
                <span style="font-size: 0.9rem; color: var(--text-main);"><?= htmlspecialchars($item) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

$content = getToolContent($action);
if ($standalone) {
    if ($action === 'index' || $action === 'log') {
        $body = $content['body'];
        $title = $content['title'];
        $contentFile = null;
        include __DIR__ . '/../app/views/settings_layout.php';
    } else {
        renderToolShell($content['title'], $content['icon'], $content['body'], $content['notice'] ?? null);
    }
} else {
    echo $content['body'];
}