<?php
session_start();
/**
 * FACTURADOR-PCCURICO — System Cleanup
 * Removes legacy migration files after consolidation.
 */
require_once __DIR__ . '/../bootstrap/app.php';

$files = glob(__DIR__ . '/migrate_v*.php');
$files[] = __DIR__ . '/check_templates.php';
$files[] = __DIR__ . '/webpay_init.php';
$files[] = __DIR__ . '/webpay_return.php';

ob_start();
?>
<div class="glass-card" style="max-width: 700px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-weight: 800; margin: 0;">🧹 Limpieza Realizada</h2>
        <a href="tools.php" class="btn btn-secondary" style="width: auto; padding: 10px 20px;">Volver</a>
    </div>

    <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php
        $deletedCount = 0;
        foreach ($files as $file) {
            $name = basename($file);
            if (file_exists($file)) {
                if (unlink($file)) {
                    echo "
                    <div style='display: flex; align-items: center; justify-content: space-between; background: rgba(16,185,129,0.05); padding: 12px 20px; border-radius: 10px; border: 1px solid rgba(16,185,129,0.1);'>
                        <span style='font-family: monospace; font-size: 0.9rem;'>$name</span>
                        <span style='color: #10b981; font-weight: 700; font-size: 0.8rem;'>ELIMINADO</span>
                    </div>";
                    $deletedCount++;
                } else {
                    echo "
                    <div style='display: flex; align-items: center; justify-content: space-between; background: rgba(239,68,68,0.05); padding: 12px 20px; border-radius: 10px; border: 1px solid rgba(239,68,68,0.1);'>
                        <span style='font-family: monospace; font-size: 0.9rem;'>$name</span>
                        <span style='color: #ef4444; font-weight: 700; font-size: 0.8rem;'>ERROR</span>
                    </div>";
                }
            }
        }

        // Clean duplicate templates in DB
        $db = \Core\Database::getInstance();
        $stmt = $db->query('SELECT id, name, type FROM document_templates ORDER BY id ASC');
        $templates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $seen = [];
        $deletedTemplates = 0;

        foreach ($templates as $t) {
            $key = $t['name'] . '_' . $t['type'];
            if (isset($seen[$key])) {
                $del = $db->prepare('DELETE FROM document_templates WHERE id = ?');
                $del->execute([$t['id']]);
                $deletedTemplates++;
                echo "
                <div style='display: flex; align-items: center; justify-content: space-between; background: rgba(16,185,129,0.05); padding: 12px 20px; border-radius: 10px; border: 1px solid rgba(16,185,129,0.1); margin-top: 5px;'>
                    <span style='font-family: monospace; font-size: 0.9rem;'>Duplicado: {$t['name']}</span>
                    <span style='color: #10b981; font-weight: 700; font-size: 0.8rem;'>ELIMINADO BD</span>
                </div>";
            } else {
                $seen[$key] = true;
            }
        }
        $deletedCount += $deletedTemplates;

        if ($deletedCount === 0) {
            echo "
            <div style='text-align: center; padding: 40px; color: var(--text-muted);'>
                <div style='font-size: 3rem; margin-bottom: 20px;'>✨</div>
                <p>No se encontraron archivos temporales o legados para eliminar.</p>
                <p style='font-size: 0.8rem;'>El sistema ya está limpio.</p>
            </div>";
        }
        ?>
    </div>

    <?php if ($deletedCount > 0): ?>
        <div style="margin-top: 30px; text-align: center; padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px;">
            <div style="font-size: 1.2rem; font-weight: 700; color: #10b981;">¡Limpieza Completada!</div>
            <div style="font-size: 0.85rem; color: var(--text-muted);"><?= $deletedCount ?> archivos y/o registros fueron removidos permanentemente.</div>
        </div>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
Core\View::renderRaw($content, ['title' => 'Limpieza de Sistema']);
