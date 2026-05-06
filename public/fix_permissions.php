<?php
session_start();
/**
 * FACTURADOR-PCCURICO — Fix Permissions
 */
require_once __DIR__ . '/../bootstrap/app.php';

$dirs = [
    __DIR__ . '/../storage',
    __DIR__ . '/../storage/logs',
    __DIR__ . '/../storage/cache',
    __DIR__ . '/../storage/backups',
    __DIR__ . '/uploads'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    @chmod($dir, 0777);
}

header('Location: tools.php?action=diagnostic&msg=Permisos+Corregidos');
exit;
