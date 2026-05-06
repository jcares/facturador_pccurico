<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

use Core\Auth;
use Core\View;
use Core\Database;

if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'diagnostic':
        $phpOk = PHP_VERSION_ID >= 70400;
        $extensions = [
            'pdo_mysql' => 'Base de Datos (PDO)',
            'curl'      => 'Transferencia (CURL)',
            'mbstring'  => 'Cadenas (MBString)',
            'openssl'   => 'Seguridad (OpenSSL)',
            'gd'        => 'Imágenes (GD)',
            'json'      => 'Datos (JSON)'
        ];
        $dirs = [
            '/storage'         => 'Almacenamiento Interno',
            '/public/uploads'  => 'Carga de Archivos',
            '/bootstrap/cache' => 'Caché de Sistema'
        ];
        ob_start();
        ?>
        <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <h2 style="font-weight: 800; margin: 0;">🔍 Informe de Diagnóstico</h2>
                <a href="tools.php" class="btn btn-secondary" style="width: auto; padding: 10px 20px;">Volver</a>
            </div>

            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Entorno del Servidor</h3>
                <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.03); padding: 15px 20px; border-radius: 12px; border: 1px solid var(--glass-border);">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 1.5rem;">🐘</div>
                        <div>
                            <div style="font-weight: 700;">Versión de PHP</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= PHP_VERSION ?></div>
                        </div>
                    </div>
                    <span class="badge" style="background: <?= $phpOk ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)' ?>; color: <?= $phpOk ? '#10b981' : '#ef4444' ?>; border: 1px solid currentColor;">
                        <?= $phpOk ? 'COMPATIBLE' : 'ACTUALIZAR RECOMENDADO' ?>
                    </span>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Extensiones de PHP</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <?php foreach ($extensions as $ext => $label):
                        $loaded = extension_loaded($ext); ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.03); padding: 12px 15px; border-radius: 10px; border: 1px solid var(--glass-border);">
                            <span style="font-size: 0.9rem; font-weight: 500;"><?= $label ?></span>
                            <span style="color: <?= $loaded ? '#10b981' : '#ef4444' ?>; font-weight: 800;"><?= $loaded ? '✓' : '✗' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Permisos de Carpeta</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($dirs as $path => $label):
                        $writable = is_writable(ROOT_PATH . $path); ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.03); padding: 12px 15px; border-radius: 10px; border: 1px solid var(--glass-border);">
                            <div>
                                <div style="font-size: 0.9rem; font-weight: 600;"><?= $label ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?= $path ?></div>
                            </div>
                            <span style="font-size: 0.8rem; font-weight: 700; color: <?= $writable ? '#10b981' : '#ef4444' ?>;">
                                <?= $writable ? 'ESCRITURA OK' : 'SIN PERMISOS' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <h3 style="font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Estado de Conexión</h3>
                <?php
                try {
                    Database::getInstance();
                    $dbStatus = true;
                    $dbMsg = "Conexión exitosa con el servidor MySQL.";
                } catch (\Exception $e) {
                    $dbStatus = false;
                    $dbMsg = "Error: " . $e->getMessage();
                }
                ?>
                <div style="background: <?= $dbStatus ? 'rgba(16,185,129,0.05)' : 'rgba(239,68,68,0.05)' ?>; padding: 20px; border-radius: 12px; border: 1px solid <?= $dbStatus ? 'rgba(16,185,129,0.2)' : 'rgba(239,68,68,0.2)' ?>;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 1.5rem;"><?= $dbStatus ? '🛢️' : '⚠️' ?></div>
                        <div>
                            <div style="font-weight: 700; color: <?= $dbStatus ? '#34d399' : '#f87171' ?>;">Base de Datos</div>
                            <div style="font-size: 0.85rem; opacity: 0.8;"><?= $dbMsg ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        View::renderRaw($content, ['title' => 'Diagnóstico de Sistema']);
        break;

    case 'export':
        $db = Database::getInstance();
        $data = [
            'clients'     => $db->query("SELECT * FROM clients")->fetchAll(),
            'invoices'    => $db->query("SELECT * FROM invoices")->fetchAll(),
            'products'    => $db->query("SELECT * FROM products")->fetchAll(),
            'exported_at' => date('Y-m-d H:i:s')
        ];
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="facturador_backup_' . date('Ymd') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;

    default:
        ob_start();
        ?>
        <div class="glass-card" style="max-width: 900px; margin: 0 auto;">
            <div style="margin-bottom: 30px;">
                <h2 style="font-weight: 800; margin: 0;">🛠️ Herramientas del Sistema</h2>
                <p style="color: var(--text-muted); margin-top: 5px;">Administración y mantenimiento del sistema.</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                <a href="tools.php?action=diagnostic" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">🔍</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Diagnóstico del Sistema</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Verifica PHP, extensiones, permisos y conexión a base de datos.</p>
                    </div>
                </a>

                <a href="run_migrations.php" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">🗄️</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Sincronizar Esquema BD</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Aplica migraciones pendientes y crea tablas faltantes.</p>
                    </div>
                </a>

                <a href="tools.php?action=export" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">💾</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Exportar Datos (JSON)</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Descarga un respaldo completo de clientes, facturas y productos.</p>
                    </div>
                </a>

                <a href="fix_permissions.php" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">🔐</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Corregir Permisos</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Restablece permisos de escritura en carpetas críticas del sistema.</p>
                    </div>
                </a>

                <a href="cleanup.php" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#ef4444'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">🧹</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Limpiar Sistema</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Elimina archivos temporales, duplicados y plantillas repetidas.</p>
                    </div>
                </a>

                <a href="read_log.php" style="text-decoration: none;">
                    <div class="glass-card" style="padding: 25px; cursor: pointer; border: 1px solid var(--glass-border); transition: border-color 0.2s;" onmouseover="this.style.borderColor='#3b82f6'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        <div style="font-size: 2rem; margin-bottom: 15px;">📋</div>
                        <h3 style="margin: 0 0 8px 0; font-size: 1rem;">Ver Log de Errores</h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Revisa los últimos errores registrados por el sistema.</p>
                    </div>
                </a>

            </div>
        </div>
        <?php
        $content = ob_get_clean();
        View::renderRaw($content, ['title' => 'Herramientas del Sistema']);
        break;
}
