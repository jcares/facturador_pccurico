<?php
session_start();
/**
 * FACTURADOR-PCCURICO — Database Migration Runner
 * Executes required migrations and seeds default data.
 */
require_once __DIR__ . '/../bootstrap/app.php';

try {
    $db = \Core\Database::getInstance();

    // Check if document_templates table exists, if not create it
    $db->exec("
        CREATE TABLE IF NOT EXISTS `document_templates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `type` ENUM('invoice', 'ticket', 'quote') NOT NULL DEFAULT 'invoice',
            `is_default` BOOLEAN DEFAULT FALSE,
            `config_json` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ");

    // Add config_json column if it doesn't exist (for existing tables)
    try {
        $db->exec("ALTER TABLE document_templates ADD COLUMN config_json TEXT AFTER is_default");
    } catch (PDOException $e) {
        // Column probably already exists, ignore
    }

    // Seed default templates if empty
    $stmt = $db->query("SELECT COUNT(*) FROM document_templates");
    $countTemplates = $stmt->fetchColumn();

    if ($countTemplates == 0) {
        $defaultConfig = [
            'blocks' => [
                ['id' => 'company', 'label' => 'Datos de Empresa', 'position' => 1, 'enabled' => true, 'options' => ['show_logo' => true, 'show_rut' => true, 'logo_width' => 150, 'logo_x' => 0, 'logo_y' => 0]],
                ['id' => 'client', 'label' => 'Datos del Cliente', 'position' => 2, 'enabled' => true, 'options' => []],
                ['id' => 'header', 'label' => 'Título del Documento', 'position' => 3, 'enabled' => true, 'options' => ['show_number' => true, 'show_date' => true]],
                ['id' => 'items', 'label' => 'Tabla de Ítems', 'position' => 4, 'enabled' => true, 'options' => ['show_sku' => true, 'show_tax' => true]],
                ['id' => 'notes', 'label' => 'Notas Adicionales', 'position' => 5, 'enabled' => true, 'options' => ['text' => '']],
                ['id' => 'footer', 'label' => 'Pie de Página', 'position' => 6, 'enabled' => true, 'options' => ['text' => 'Generado por Facturador']],
            ],
            'styles' => ['primary_color' => '#3b82f6', 'font_family' => 'sans-serif']
        ];
        
        $json = json_encode($defaultConfig);

        $db->exec("INSERT INTO document_templates (name, type, is_default, config_json) VALUES 
            ('Factura Electrónica (SII)', 'invoice', 1, '$json'),
            ('Ticket 80mm', 'ticket', 1, '$json')
        ");
        echo "Plantillas por defecto creadas.<br>";
    }

    echo "Sincronización completada exitosamente. <a href='tools.php'>Volver</a>";

} catch (Exception $e) {
    echo "Error de sincronización: " . $e->getMessage();
}
