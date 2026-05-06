<?php
/**
 * FACTURADOR-PCCURICO
 * cPanel Release Builder
 * Generates a ZIP file containing the application ready to be uploaded to cPanel.
 */

$sourceDir = __DIR__;
$outputZip = __DIR__ . '/cpanel_release.zip';

// Folders and files to exclude from the production build
$excludes = [
    '.git',
    '.gemini',
    'scratch',
    'bitacora.md',
    'biblia.md',
    'arquitectura.md',
    'build_cpanel.php',
    'cpanel_release.zip',
    'storage/logs/error.log',
    'storage/installed.lock' // Exclude lock to allow clean install on target
];

if (file_exists($outputZip)) {
    unlink($outputZip);
}

$zip = new ZipArchive();
if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Error: No se pudo crear el archivo ZIP.\n");
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

echo "Construyendo el paquete para cPanel...\n";

$fileCount = 0;
foreach ($iterator as $file) {
    $filePath = $file->getPathname();
    $relativePath = substr($filePath, strlen($sourceDir) + 1);

    // Normalize slashes for matching
    $normalizedPath = str_replace('\\', '/', $relativePath);
    
    $skip = false;
    foreach ($excludes as $exclude) {
        if (strpos($normalizedPath, $exclude) === 0) {
            $skip = true;
            break;
        }
    }

    if (!$skip) {
        $zip->addFile($filePath, $relativePath);
        $fileCount++;
    }
}

$zip->close();
echo "Construcción completada exitosamente.\n";
echo "Total archivos empaquetados: {$fileCount}\n";
echo "Archivo generado: {$outputZip}\n";
echo "\nInstrucciones para cPanel:\n";
echo "1. Suba el archivo cpanel_release.zip al directorio public_html o directorio de subdominio.\n";
echo "2. Extraiga el contenido del archivo.\n";
echo "3. Asegúrese de que el directorio 'storage' tenga permisos de escritura (0755 o 0777).\n";
echo "4. Acceda a la URL (ej: https://su-dominio.com/install.php) para iniciar la configuración.\n";
