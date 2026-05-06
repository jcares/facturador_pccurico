<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/run_migrations.php';

use Core\Auth;
use Core\Database;
use Core\View;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? 'index';

function renderToolShell(string $title, string $icon, string $body, ?array $notice = null): void
{
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
            <?php $noticeType = $notice['type'] === 'error' ? '239,68,68' : '16,185,129'; ?>
            <div class="alert" style="background: rgba(<?= $noticeType ?>,0.08); border: 1px solid rgba(<?= $noticeType ?>,0.25); color: var(--text-main); padding: 16px 18px; border-radius: 10px; margin-bottom: 22px;">
                <div style="font-weight: 800; margin-bottom: 4px;"><?= htmlspecialchars($notice['title']) ?></div>
                <div style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($notice['message']) ?></div>
            </div>
        <?php endif; ?>

        <?= $body ?>
    </div>
    <?php
    View::renderRaw(ob_get_clean(), ['title' => $title]);
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

switch ($action) {
    case 'sync':
        try {
            $result = runDatabaseMigrations();
            renderToolShell(
                'Sincronizar BD',
                'database',
                toolDetailList($result['details']),
                ['type' => 'success', 'title' => 'Sincronizacion completada', 'message' => $result['message']]
            );
        } catch (Throwable $e) {
            \Core\Logger::error('Migration runner failed: ' . $e->getMessage());
            renderToolShell(
                'Sincronizar BD',
                'database',
                '<p style="color: var(--text-muted);">Revisa el log de errores para ver el detalle tecnico.</p>',
                ['type' => 'error', 'title' => 'No se pudo sincronizar', 'message' => $e->getMessage()]
            );
        }
        break;

    case 'permissions':
        $dirs = [
            ROOT_PATH . '/storage',
            ROOT_PATH . '/storage/logs',
            ROOT_PATH . '/storage/cache',
            ROOT_PATH . '/storage/backups',
            __DIR__ . '/uploads',
        ];

        $details = [];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            @chmod($dir, 0777);
            $details[] = (is_writable($dir) ? 'Escritura OK: ' : 'Revisar permisos: ') . str_replace(ROOT_PATH, '', $dir);
        }

        renderToolShell(
            'Corregir Permisos',
            'shield-check',
            toolDetailList($details),
            ['type' => 'success', 'title' => 'Permisos revisados', 'message' => 'Las carpetas criticas fueron creadas o actualizadas cuando fue posible.']
        );
        break;

    case 'cleanup':
        $files = glob(__DIR__ . '/migrate_v*.php') ?: [];
        $files[] = __DIR__ . '/check_templates.php';
        $details = [];
        $deletedCount = 0;

        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }

            $name = basename($file);
            if (@unlink($file)) {
                $deletedCount++;
                $details[] = "Archivo eliminado: {$name}";
            } else {
                $details[] = "No se pudo eliminar: {$name}";
            }
        }

        try {
            $db = Database::getInstance();
            $stmt = $db->query('SELECT id, name, type FROM document_templates ORDER BY id ASC');
            $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $seen = [];

            foreach ($templates as $template) {
                $key = $template['name'] . '_' . $template['type'];
                if (isset($seen[$key])) {
                    $delete = $db->prepare('DELETE FROM document_templates WHERE id = ?');
                    $delete->execute([$template['id']]);
                    $deletedCount++;
                    $details[] = 'Plantilla duplicada eliminada: ' . $template['name'];
                } else {
                    $seen[$key] = true;
                }
            }
        } catch (Throwable $e) {
            \Core\Logger::error('Cleanup templates failed: ' . $e->getMessage());
            $details[] = 'No se pudo revisar plantillas duplicadas.';
        }

        if (!$details) {
            $details[] = 'No habia archivos temporales ni plantillas duplicadas para eliminar.';
        }

        renderToolShell(
            'Limpiar Sistema',
            'sparkles',
            toolDetailList($details),
            ['type' => 'success', 'title' => 'Limpieza finalizada', 'message' => "{$deletedCount} elementos fueron removidos."]
        );
        break;

    case 'log':
        $log = realpath(ROOT_PATH . '/storage/logs/error.log');
        ob_start();
        if ($log && file_exists($log)) {
            ?>
            <p style="color: var(--text-muted); margin-bottom: 12px;">Archivo: <?= htmlspecialchars($log) ?></p>
            <pre style="white-space: pre-wrap; background: rgba(0,0,0,0.35); border: 1px solid var(--glass-border); border-radius: 10px; padding: 18px; max-height: 650px; overflow: auto; color: #d1d5db;"><?= htmlspecialchars(file_get_contents($log)) ?></pre>
            <?php
        } else {
            ?>
            <div class="alert" style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.25); padding: 16px; border-radius: 10px;">No se encontro el archivo de log.</div>
            <?php
        }
        renderToolShell('Log de Errores', 'file-warning', ob_get_clean());
        break;

    case 'diagnostic':
        $phpOk = PHP_VERSION_ID >= 80000;
        $extensions = [
            'pdo_mysql' => 'Base de Datos PDO',
            'curl' => 'Transferencia CURL',
            'mbstring' => 'Cadenas MBString',
            'openssl' => 'Seguridad OpenSSL',
            'gd' => 'Imagenes GD',
            'json' => 'Datos JSON',
        ];
        $dirs = [
            ROOT_PATH . '/storage' => 'Almacenamiento interno',
            __DIR__ . '/uploads' => 'Carga de archivos',
            ROOT_PATH . '/bootstrap/cache' => 'Cache de sistema',
        ];

        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 24px;">
            <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 8px; border: 1px solid var(--glass-border);">
                <div style="font-weight: 800;">Version de PHP</div>
                <div style="color: var(--text-muted); margin-top: 4px;"><?= htmlspecialchars(PHP_VERSION) ?></div>
                <div style="color: <?= $phpOk ? '#10b981' : '#ef4444' ?>; font-weight: 800; margin-top: 10px;"><?= $phpOk ? 'Compatible' : 'Actualizar recomendado' ?></div>
            </div>
            <?php
            try {
                Database::getInstance();
                $dbMessage = 'Conexion exitosa con MySQL.';
                $dbOk = true;
            } catch (Throwable $e) {
                $dbMessage = $e->getMessage();
                $dbOk = false;
            }
            ?>
            <div style="background: rgba(255,255,255,0.03); padding: 16px; border-radius: 8px; border: 1px solid var(--glass-border);">
                <div style="font-weight: 800;">Base de datos</div>
                <div style="color: var(--text-muted); margin-top: 4px;"><?= htmlspecialchars($dbMessage) ?></div>
                <div style="color: <?= $dbOk ? '#10b981' : '#ef4444' ?>; font-weight: 800; margin-top: 10px;"><?= $dbOk ? 'Operativa' : 'Con error' ?></div>
            </div>
        </div>

        <h3 style="font-size: 0.95rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 12px;">Extensiones</h3>
        <?= toolDetailList(array_map(
            fn ($label, $ext) => (extension_loaded($ext) ? 'OK: ' : 'Falta: ') . $label,
            $extensions,
            array_keys($extensions)
        )) ?>

        <h3 style="font-size: 0.95rem; color: var(--text-muted); text-transform: uppercase; margin: 24px 0 12px;">Carpetas</h3>
        <?= toolDetailList(array_map(
            fn ($label, $path) => (is_writable($path) ? 'Escritura OK: ' : 'Sin escritura: ') . $label,
            $dirs,
            array_keys($dirs)
        )) ?>
        <?php
        renderToolShell('Diagnostico', 'activity', ob_get_clean());
        break;

    case 'export':
        $db = Database::getInstance();
        $data = [
            'clients' => $db->query("SELECT * FROM clients")->fetchAll(),
            'invoices' => $db->query("SELECT * FROM invoices")->fetchAll(),
            'products' => $db->query("SELECT * FROM products")->fetchAll(),
            'exported_at' => date('Y-m-d H:i:s'),
        ];
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="facturador_backup_' . date('Ymd') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;

    default:
        $tools = [
            ['diagnostic', 'activity', 'Diagnostico del Sistema', 'Verifica PHP, extensiones, permisos y conexion a base de datos.'],
            ['sync', 'database', 'Sincronizar Esquema BD', 'Aplica migraciones pendientes y crea tablas faltantes sin duplicar datos.'],
            ['export', 'download', 'Exportar Datos JSON', 'Descarga un respaldo completo de clientes, facturas y productos.'],
            ['permissions', 'shield-check', 'Corregir Permisos', 'Restablece permisos de escritura en carpetas criticas del sistema.'],
            ['cleanup', 'sparkles', 'Limpiar Sistema', 'Elimina archivos temporales y plantillas repetidas.'],
            ['log', 'file-warning', 'Ver Log de Errores', 'Revisa los ultimos errores registrados por el sistema.'],
        ];

        ob_start();
        ?>
        <div style="max-width: 980px; margin: 0 auto;">
            <div style="margin-bottom: 28px;">
                <h2 style="font-weight: 800; margin: 0;">Herramientas del Sistema</h2>
                <p style="color: var(--text-muted); margin-top: 6px;">Administracion y mantenimiento desde el panel.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px;">
                <?php foreach ($tools as [$toolAction, $icon, $label, $description]): ?>
                    <a href="tools.php?action=<?= htmlspecialchars($toolAction) ?>" style="text-decoration: none;">
                        <div class="glass-card" style="padding: 22px; min-height: 142px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s, transform 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.transform='translateY(-1px)'" onmouseout="this.style.borderColor='var(--glass-border)'; this.style.transform='none'">
                            <i data-lucide="<?= htmlspecialchars($icon) ?>" style="width: 30px; height: 30px; color: var(--primary); margin-bottom: 16px;"></i>
                            <h3 style="margin: 0 0 8px 0; font-size: 1rem; color: var(--text-main);"><?= htmlspecialchars($label) ?></h3>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; line-height: 1.45;"><?= htmlspecialchars($description) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        View::renderRaw(ob_get_clean(), ['title' => 'Herramientas del Sistema']);
        break;
}
