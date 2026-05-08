-- ============================================================
-- FACTURADOR-PCCURICO: Script para crear tablas faltantes
-- Seguro para ejecutar en producción (usa IF NOT EXISTS)
-- No elimina datos existentes
-- ============================================================

CREATE TABLE IF NOT EXISTS `document_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('invoice', 'ticket', 'quote') NOT NULL DEFAULT 'invoice',
    `is_default` BOOLEAN DEFAULT FALSE,
    `config_json` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `recurring_invoices` (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `recurring_invoice_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `recurring_invoice_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `qty` DECIMAL(12,2) NOT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `original_price` DECIMAL(15,2) NULL,
    `original_currency` VARCHAR(10) NULL,
    `exchange_rate` DECIMAL(15,4) DEFAULT 1,
    `total` DECIMAL(15,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `tasks` (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `purchase_orders` (
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
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `purchase_order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `purchase_order_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `qty` DECIMAL(12,2) NOT NULL,
    `price` DECIMAL(15,2) NOT NULL,
    `tax_rate` DECIMAL(5,2) DEFAULT 0.19,
    `total` DECIMAL(15,2) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `expenses` (
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
) ENGINE=InnoDB;

-- ============================================================
-- Verificación: Mostrar todas las tablas después de la ejecución
-- ============================================================
-- SHOW TABLES;
