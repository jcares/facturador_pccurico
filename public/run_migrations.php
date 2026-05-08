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
            `parent_id` INT NULL,
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
            `price_unit` VARCHAR(20) DEFAULT 'unit',
            `tax_rate` DECIMAL(5,2) DEFAULT 0.19,
            `stock` DECIMAL(12,2) DEFAULT 0,
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
            `qty` DECIMAL(12,2) NOT NULL,
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
            `qty` DECIMAL(12,2) NOT NULL,
            `price` DECIMAL(15,2) NOT NULL,
            `original_price` DECIMAL(15,2) NULL,
            `original_currency` VARCHAR(10) NULL,
            `exchange_rate` DECIMAL(15,4) DEFAULT 1,
            `total` DECIMAL(15,2) NOT NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `tasks` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
            `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
            `due_date` DATE NULL,
            `assigned_to` INT NULL,
            `created_by` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `purchase_orders` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `supplier_name` VARCHAR(255) NOT NULL,
            `supplier_rut` VARCHAR(20),
            `supplier_email` VARCHAR(255),
            `supplier_phone` VARCHAR(50),
            `supplier_address` TEXT,
            `number` VARCHAR(50) UNIQUE NOT NULL,
            `status` ENUM('draft', 'sent', 'received', 'canceled') DEFAULT 'draft',
            `subtotal` DECIMAL(15,2) NOT NULL,
            `tax` DECIMAL(15,2) NOT NULL,
            `total` DECIMAL(15,2) NOT NULL,
            `currency` VARCHAR(10) DEFAULT 'CLP',
            `exchange_rate` DECIMAL(15,4) DEFAULT 1,
            `due_date` DATE DEFAULT NULL,
            `notes` TEXT,
            `created_by` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `purchase_order_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `purchase_order_id` INT NOT NULL,
            `product_name` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `qty` DECIMAL(12,2) NOT NULL,
            `price` DECIMAL(15,2) NOT NULL,
            `tax_rate` DECIMAL(5,2) DEFAULT 0.19,
            `total` DECIMAL(15,2) NOT NULL
        ) ENGINE=InnoDB",
        "CREATE TABLE IF NOT EXISTS `expenses` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `category` VARCHAR(100),
            `amount` DECIMAL(15,2) NOT NULL,
            `currency` VARCHAR(10) DEFAULT 'CLP',
            `exchange_rate` DECIMAL(15,4) DEFAULT 1,
            `date` DATE NOT NULL,
            `receipt_file` VARCHAR(255),
            `supplier` VARCHAR(255),
            `payment_method` VARCHAR(50),
            `tax_deductible` BOOLEAN DEFAULT FALSE,
            `created_by` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
    $addColumn('categories', 'parent_id', "ALTER TABLE categories ADD COLUMN parent_id INT NULL AFTER name");
    $addColumn('products', 'currency', "ALTER TABLE products ADD COLUMN currency VARCHAR(10) DEFAULT 'CLP' AFTER price");
    $addColumn('products', 'category_id', "ALTER TABLE products ADD COLUMN category_id INT NULL AFTER currency");
    $addColumn('products', 'price_unit', "ALTER TABLE products ADD COLUMN price_unit VARCHAR(20) DEFAULT 'unit' AFTER category_id");
    $addColumn('products', 'tax_rate', "ALTER TABLE products ADD COLUMN tax_rate DECIMAL(5,2) DEFAULT 0.19 AFTER price_unit");
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

    try {
        $db->exec("ALTER TABLE products MODIFY stock DECIMAL(12,2) DEFAULT 0");
        $db->exec("ALTER TABLE invoice_items MODIFY qty DECIMAL(12,2) NOT NULL");
        if ($columnExists('recurring_invoice_items', 'qty')) {
            $db->exec("ALTER TABLE recurring_invoice_items MODIFY qty DECIMAL(12,2) NOT NULL");
        }
        $messages[] = 'Cantidades y stock decimal verificados.';
    } catch (PDOException $e) {
        \Core\Logger::error('Decimal quantity migration skipped: ' . $e->getMessage());
        $messages[] = 'Ajuste decimal de cantidades omitido; revisar datos manualmente.';
    }

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

    if (!$indexExists('categories', 'idx_categories_parent_id')) {
        $db->exec("ALTER TABLE categories ADD INDEX idx_categories_parent_id (parent_id)");
        $messages[] = 'Indice de categoria padre creado.';
    }

    $addForeignKey('categories', 'parent_id', 'fk_categories_parent_id', "ALTER TABLE categories ADD CONSTRAINT fk_categories_parent_id FOREIGN KEY (parent_id) REFERENCES categories(id)");
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

    $seedCategory = $db->prepare("INSERT INTO categories (name, parent_id) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = ?)");
    $seedParent = $db->prepare("INSERT INTO categories (name, parent_id) SELECT ?, NULL WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = ?)");
    foreach (['Servicios', 'Hardware', 'Software'] as $categoryName) {
        $seedParent->execute([$categoryName, $categoryName]);
        $parentId = (int)$db->query("SELECT id FROM categories WHERE name = " . $db->quote($categoryName) . " LIMIT 1")->fetchColumn();
        $childName = $categoryName . ' General';
        $seedCategory->execute([$childName, $parentId, $childName]);

        $stmt = $db->prepare("UPDATE products SET category_id = ? WHERE category_id = ?");
        $stmt->execute([(int)$db->query("SELECT id FROM categories WHERE name = " . $db->quote($childName) . " LIMIT 1")->fetchColumn(), $parentId]);
    }

    $directProductParents = $db->query("
        SELECT c.id, c.name
        FROM categories c
        INNER JOIN products p ON p.category_id = c.id
        WHERE c.parent_id IS NULL
        GROUP BY c.id, c.name
    ")->fetchAll();
    foreach ($directProductParents as $parent) {
        $parentId = (int)$parent['id'];
        $childName = $parent['name'] . ' General';
        $existingChild = $db->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
        $existingChild->execute([$childName]);
        $childId = (int)$existingChild->fetchColumn();

        if ($childId <= 0) {
            $insertChild = $db->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
            $insertChild->execute([$childName, $parentId]);
            $childId = (int)$db->lastInsertId();
        }

        $moveProducts = $db->prepare("UPDATE products SET category_id = ? WHERE category_id = ?");
        $moveProducts->execute([$childId, $parentId]);
    }
    $messages[] = 'Categorias padre e hijas base verificadas sin duplicar.';

    $stmt = $db->query("SELECT COUNT(*) FROM document_templates");
    if ((int)$stmt->fetchColumn() === 0) {
        $defaultConfig = [
            'blocks' => [
                ['id' => 'company', 'label' => 'Datos de Empresa', 'position' => 1, 'enabled' => true, 'options' => ['show_logo' => true, 'show_rut' => true, 'logo_width' => 150, 'logo_x' => 0, 'logo_y' => 0]],
                ['id' => 'client', 'label' => 'Datos del Cliente', 'position' => 2, 'enabled' => true, 'options' => []],
                ['id' => 'header', 'label' => 'Titulo del Documento', 'position' => 3, 'enabled' => true, 'options' => ['show_number' => true, 'show_date' => true]],
                ['id' => 'items', 'label' => 'Tabla de Items', 'position' => 4, 'enabled' => true, 'options' => ['show_sku' => true, 'show_tax' => true]],
                ['id' => 'webpay_payment', 'label' => 'Pago Webpay', 'position' => 5, 'enabled' => false, 'options' => ['text' => 'Pagar con Webpay Plus']],
                ['id' => 'notes', 'label' => 'Notas Adicionales', 'position' => 6, 'enabled' => true, 'options' => ['text' => '']],
                ['id' => 'footer', 'label' => 'Pie de Pagina', 'position' => 7, 'enabled' => true, 'options' => ['text' => 'Generado por Facturador']],
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
