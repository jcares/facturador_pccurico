<?php
/**
 * FACTURADOR-PCCURICO - Database Migration Runner
 */

function runDatabaseMigrations(): array
{
    $db = \Core\Database::getInstance();
    $messages = [];

    $columnExists = function (string $table, string $column) use ($db): bool {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    };

    $indexExists = function (string $table, string $index) use ($db): bool {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ");
        $stmt->execute([$table, $index]);
        return (int)$stmt->fetchColumn() > 0;
    };

    $uniqueIndexExists = function (string $table, string $column) use ($db): bool {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND NON_UNIQUE = 0
        ");
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    };

    $constraintExists = function (string $table, string $constraint) use ($db): bool {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
        ");
        $stmt->execute([$table, $constraint]);
        return (int)$stmt->fetchColumn() > 0;
    };

    $foreignKeyExists = function (string $table, string $column) use ($db): bool {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE CONSTRAINT_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    };

    $addColumn = function (string $table, string $column, string $sql) use ($db, $columnExists, &$messages): void {
        if (!$columnExists($table, $column)) {
            $db->exec($sql);
            $messages[] = "Columna {$table}.{$column} creada.";
        }
    };

    $addConstraint = function (string $table, string $constraint, string $sql) use ($db, $constraintExists, &$messages): void {
        if ($constraintExists($table, $constraint)) {
            return;
        }

        try {
            $db->exec($sql);
            $messages[] = "Relacion {$constraint} creada.";
        } catch (PDOException $e) {
            \Core\Logger::error("Constraint {$constraint} skipped: " . $e->getMessage());
            $messages[] = "Relacion {$constraint} omitida; ya existe o hay datos incompatibles.";
        }
    };

    $addForeignKey = function (string $table, string $column, string $constraint, string $sql) use ($addConstraint, $foreignKeyExists): void {
        if ($foreignKeyExists($table, $column)) {
            return;
        }

        $addConstraint($table, $constraint, $sql);
    };

    $schemaSql = [
        "CREATE TABLE IF NOT EXISTS `categories` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `clients` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NULL,
            `business_name` VARCHAR(255) NULL,
            `contact_name` VARCHAR(255) NULL,
            `rut` VARCHAR(20) UNIQUE NOT NULL,
            `email` VARCHAR(255),
            `phone` VARCHAR(50),
            `address` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `products` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `sku` VARCHAR(100) UNIQUE,
            `price` DECIMAL(15,2) NOT NULL,
            `currency` VARCHAR(10) DEFAULT 'CLP',
            `category_id` INT NULL,
            `tax_rate` DECIMAL(5,2) DEFAULT 0.19,
            `stock` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `invoices` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `client_id` INT,
            `number` VARCHAR(50) UNIQUE NOT NULL,
            `status` ENUM('draft', 'sent', 'paid', 'canceled') DEFAULT 'draft',
            `subtotal` DECIMAL(15,2) NOT NULL,
            `tax` DECIMAL(15,2) NOT NULL,
            `total` DECIMAL(15,2) NOT NULL,
            `currency` VARCHAR(10) DEFAULT 'CLP',
            `exchange_rate` DECIMAL(15,4) DEFAULT 1,
            `due_date` DATE DEFAULT NULL,
            `token` VARCHAR(64) UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `invoice_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `invoice_id` INT,
            `product_id` INT,
            `qty` INT NOT NULL,
            `price` DECIMAL(15,2) NOT NULL,
            `original_price` DECIMAL(15,2) NULL,
            `original_currency` VARCHAR(10) NULL,
            `exchange_rate` DECIMAL(15,4) DEFAULT 1,
            `total` DECIMAL(15,2) NOT NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `payments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `invoice_id` INT,
            `amount` DECIMAL(15,2) NOT NULL,
            `currency` VARCHAR(10) DEFAULT 'CLP',
            `exchange_rate` DECIMAL(15,4) DEFAULT 1,
            `method` VARCHAR(50) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `settings` (
            `key` VARCHAR(100) PRIMARY KEY,
            `value` TEXT,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) UNIQUE NOT NULL,
            `password` VARCHAR(255) NOT NULL,
            `name` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `exchange_rates` (
            `currency` VARCHAR(10) PRIMARY KEY,
            `value` DECIMAL(15,4) NOT NULL,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `credit_notes` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `invoice_id` INT NOT NULL,
            `reason` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `document_templates` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `type` ENUM('invoice', 'ticket', 'quote') NOT NULL DEFAULT 'invoice',
            `is_default` BOOLEAN DEFAULT FALSE,
            `config_json` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `recurring_invoices` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `source_invoice_id` INT NULL,
            `last_invoice_id` INT NULL,
            `client_id` INT NOT NULL,
            `frequency` ENUM('weekly', 'monthly', 'quarterly', 'yearly') NOT NULL DEFAULT 'monthly',
            `status` ENUM('active', 'paused', 'completed') NOT NULL DEFAULT 'active',
            `start_date` DATE NOT NULL,
            `next_run_date` DATE NULL,
            `last_run_date` DATE NULL,
            `due_days` INT NOT NULL DEFAULT 30,
            `remaining_cycles` INT NULL,
            `cycles_generated` INT NOT NULL DEFAULT 0,
            `subtotal` DECIMAL(15,2) NOT NULL,
            `tax` DECIMAL(15,2) NOT NULL,
            `total` DECIMAL(15,2) NOT NULL,
            `currency` VARCHAR(10) DEFAULT 'CLP',
            `exchange_rate` DECIMAL(15,4) DEFAULT 1,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `recurring_invoice_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `recurring_invoice_id` INT NOT NULL,
            `product_id` INT NOT NULL,
            `qty` INT NOT NULL,
            `price` DECIMAL(15,2) NOT NULL,
            `original_price` DECIMAL(15,2) NULL,
            `original_currency` VARCHAR(10) NULL,
            `exchange_rate` DECIMAL(15,4) DEFAULT 1,
            `total` DECIMAL(15,2) NOT NULL
        ) ENGINE=InnoDB",
    ];

    foreach ($schemaSql as $sql) {
        $db->exec($sql);
    }
    $messages[] = 'Tablas base verificadas.';

    $duplicateCategories = $db->query("
        SELECT name, MIN(id) AS keep_id, GROUP_CONCAT(id ORDER BY id) AS ids, COUNT(*) AS qty
        FROM categories
        GROUP BY name
        HAVING COUNT(*) > 1
    ")->fetchAll();

    foreach ($duplicateCategories as $duplicate) {
        $keepId = (int)$duplicate['keep_id'];
        $ids = array_map('intval', explode(',', (string)$duplicate['ids']));
        $deleteIds = array_values(array_filter($ids, fn($id) => $id !== $keepId));

        if (!$deleteIds) {
            continue;
        }

        $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
        $update = $db->prepare("UPDATE products SET category_id = ? WHERE category_id IN ({$placeholders})");
        $update->execute(array_merge([$keepId], $deleteIds));

        $delete = $db->prepare("DELETE FROM categories WHERE id IN ({$placeholders})");
        $delete->execute($deleteIds);

        $messages[] = 'Categoria duplicada consolidada: ' . $duplicate['name'];
    }

    if (!$uniqueIndexExists('categories', 'name')) {
        try {
            $db->exec("ALTER TABLE categories ADD UNIQUE INDEX uq_categories_name (name)");
            $messages[] = 'Indice unico de categorias creado.';
        } catch (PDOException $e) {
            \Core\Logger::error('Category unique index skipped: ' . $e->getMessage());
            $messages[] = 'Indice unico de categorias omitido; revisar duplicados manualmente.';
        }
    }

    $addColumn('clients', 'name', "ALTER TABLE clients ADD COLUMN name VARCHAR(255) NULL AFTER id");
    $addColumn('clients', 'business_name', "ALTER TABLE clients ADD COLUMN business_name VARCHAR(255) NULL AFTER name");
    $addColumn('clients', 'contact_name', "ALTER TABLE clients ADD COLUMN contact_name VARCHAR(255) NULL AFTER business_name");
    $addColumn('products', 'currency', "ALTER TABLE products ADD COLUMN currency VARCHAR(10) DEFAULT 'CLP' AFTER price");
    $addColumn('products', 'category_id', "ALTER TABLE products ADD COLUMN category_id INT NULL AFTER currency");
    $addColumn('products', 'tax_rate', "ALTER TABLE products ADD COLUMN tax_rate DECIMAL(5,2) DEFAULT 0.19 AFTER category_id");
    $addColumn('products', 'stock', "ALTER TABLE products ADD COLUMN stock INT DEFAULT 0 AFTER tax_rate");
    $addColumn('invoices', 'currency', "ALTER TABLE invoices ADD COLUMN currency VARCHAR(10) DEFAULT 'CLP' AFTER total");
    $addColumn('invoices', 'exchange_rate', "ALTER TABLE invoices ADD COLUMN exchange_rate DECIMAL(15,4) DEFAULT 1 AFTER currency");
    $addColumn('invoice_items', 'original_price', "ALTER TABLE invoice_items ADD COLUMN original_price DECIMAL(15,2) NULL AFTER price");
    $addColumn('invoice_items', 'original_currency', "ALTER TABLE invoice_items ADD COLUMN original_currency VARCHAR(10) NULL AFTER original_price");
    $addColumn('invoice_items', 'exchange_rate', "ALTER TABLE invoice_items ADD COLUMN exchange_rate DECIMAL(15,4) DEFAULT 1 AFTER original_currency");
    $addColumn('payments', 'currency', "ALTER TABLE payments ADD COLUMN currency VARCHAR(10) DEFAULT 'CLP' AFTER amount");
    $addColumn('payments', 'exchange_rate', "ALTER TABLE payments ADD COLUMN exchange_rate DECIMAL(15,4) DEFAULT 1 AFTER currency");
    $addColumn('settings', 'updated_at', "ALTER TABLE settings ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    $addColumn('document_templates', 'config_json', "ALTER TABLE document_templates ADD COLUMN config_json TEXT AFTER is_default");

    $db->exec("
        UPDATE clients
        SET business_name = COALESCE(NULLIF(business_name, ''), name),
            contact_name = COALESCE(NULLIF(contact_name, ''), name)
        WHERE name IS NOT NULL
    ");

    if (!$indexExists('products', 'idx_products_category_id')) {
        $db->exec("ALTER TABLE products ADD INDEX idx_products_category_id (category_id)");
        $messages[] = 'Indice de categoria en productos creado.';
    }

    $addForeignKey('products', 'category_id', 'fk_products_category_id', "ALTER TABLE products ADD CONSTRAINT fk_products_category_id FOREIGN KEY (category_id) REFERENCES categories(id)");
    $addForeignKey('invoices', 'client_id', 'fk_invoices_client_id', "ALTER TABLE invoices ADD CONSTRAINT fk_invoices_client_id FOREIGN KEY (client_id) REFERENCES clients(id)");
    $addForeignKey('invoice_items', 'invoice_id', 'fk_invoice_items_invoice_id', "ALTER TABLE invoice_items ADD CONSTRAINT fk_invoice_items_invoice_id FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE");
    $addForeignKey('invoice_items', 'product_id', 'fk_invoice_items_product_id', "ALTER TABLE invoice_items ADD CONSTRAINT fk_invoice_items_product_id FOREIGN KEY (product_id) REFERENCES products(id)");
    $addForeignKey('payments', 'invoice_id', 'fk_payments_invoice_id', "ALTER TABLE payments ADD CONSTRAINT fk_payments_invoice_id FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE");
    $addForeignKey('credit_notes', 'invoice_id', 'fk_credit_notes_invoice_id', "ALTER TABLE credit_notes ADD CONSTRAINT fk_credit_notes_invoice_id FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE");
    $addForeignKey('recurring_invoices', 'client_id', 'fk_recurring_invoices_client_id', "ALTER TABLE recurring_invoices ADD CONSTRAINT fk_recurring_invoices_client_id FOREIGN KEY (client_id) REFERENCES clients(id)");
    $addForeignKey('recurring_invoices', 'source_invoice_id', 'fk_recurring_invoices_source_invoice_id', "ALTER TABLE recurring_invoices ADD CONSTRAINT fk_recurring_invoices_source_invoice_id FOREIGN KEY (source_invoice_id) REFERENCES invoices(id) ON DELETE SET NULL");
    $addForeignKey('recurring_invoices', 'last_invoice_id', 'fk_recurring_invoices_last_invoice_id', "ALTER TABLE recurring_invoices ADD CONSTRAINT fk_recurring_invoices_last_invoice_id FOREIGN KEY (last_invoice_id) REFERENCES invoices(id) ON DELETE SET NULL");
    $addForeignKey('recurring_invoice_items', 'recurring_invoice_id', 'fk_recurring_invoice_items_template_id', "ALTER TABLE recurring_invoice_items ADD CONSTRAINT fk_recurring_invoice_items_template_id FOREIGN KEY (recurring_invoice_id) REFERENCES recurring_invoices(id) ON DELETE CASCADE");
    $addForeignKey('recurring_invoice_items', 'product_id', 'fk_recurring_invoice_items_product_id', "ALTER TABLE recurring_invoice_items ADD CONSTRAINT fk_recurring_invoice_items_product_id FOREIGN KEY (product_id) REFERENCES products(id)");

    $seedCategory = $db->prepare("INSERT INTO categories (name) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = ?)");
    foreach (['Servicios', 'Hardware', 'Software'] as $categoryName) {
        $seedCategory->execute([$categoryName, $categoryName]);
    }
    $messages[] = 'Categorias base verificadas sin duplicar.';

    $stmt = $db->query("SELECT COUNT(*) FROM document_templates");
    if ((int)$stmt->fetchColumn() === 0) {
        $defaultConfig = [
            'blocks' => [
                ['id' => 'company', 'label' => 'Datos de Empresa', 'position' => 1, 'enabled' => true, 'options' => ['show_logo' => true, 'show_rut' => true, 'logo_width' => 150, 'logo_x' => 0, 'logo_y' => 0]],
                ['id' => 'client', 'label' => 'Datos del Cliente', 'position' => 2, 'enabled' => true, 'options' => []],
                ['id' => 'header', 'label' => 'Titulo del Documento', 'position' => 3, 'enabled' => true, 'options' => ['show_number' => true, 'show_date' => true]],
                ['id' => 'items', 'label' => 'Tabla de Items', 'position' => 4, 'enabled' => true, 'options' => ['show_sku' => true, 'show_tax' => true]],
                ['id' => 'notes', 'label' => 'Notas Adicionales', 'position' => 5, 'enabled' => true, 'options' => ['text' => '']],
                ['id' => 'footer', 'label' => 'Pie de Pagina', 'position' => 6, 'enabled' => true, 'options' => ['text' => 'Generado por Facturador']],
            ],
            'styles' => ['primary_color' => '#3b82f6', 'font_family' => 'sans-serif']
        ];

        $stmt = $db->prepare("INSERT INTO document_templates (name, type, is_default, config_json) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Factura Electronica (SII)', 'invoice', 1, json_encode($defaultConfig)]);
        $stmt->execute(['Ticket 80mm', 'ticket', 1, json_encode($defaultConfig)]);
        $messages[] = 'Plantillas por defecto creadas.';
    } else {
        $messages[] = 'Plantillas existentes conservadas.';
    }

    return [
        'ok' => true,
        'message' => 'Sincronizacion completada exitosamente.',
        'details' => $messages,
    ];
}

if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === basename(__FILE__)) {
    session_start();
    require_once __DIR__ . '/../bootstrap/app.php';

    if (!\Core\Auth::check()) {
        header('Location: login.php');
        exit;
    }

    header('Location: tools.php?action=sync');
    exit;
}
