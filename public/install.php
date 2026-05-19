<?php
/**
 * FACTURADOR-PCCURICO
 * Installation Wizard - Full Logic
 */

ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
$installHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
if ($installHttps) {
    ini_set('session.cookie_secure', '1');
}
session_start();
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/Core/Database.php';

// Crear directorio storage si no existe (instalación limpia)
if (!is_dir(ROOT_PATH . '/storage')) {
    @mkdir(ROOT_PATH . '/storage', 0755, true);
}
if (!is_dir(ROOT_PATH . '/storage/logs')) {
    @mkdir(ROOT_PATH . '/storage/logs', 0755, true);
}
if (!is_dir(ROOT_PATH . '/storage/cache')) {
    @mkdir(ROOT_PATH . '/storage/cache', 0755, true);
}
if (!is_dir(ROOT_PATH . '/storage/backups')) {
    @mkdir(ROOT_PATH . '/storage/backups', 0755, true);
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = null;
$success = null;

if (file_exists(ROOT_PATH . '/storage/installed.lock') && $step !== 6) {
    header('Location: login.php');
    exit;
}

// Requisitos del sistema
$requirements = [
    'php_version' => ['name' => 'PHP Version (>= 8.0)', 'check' => version_compare(PHP_VERSION, '8.0.0', '>='), 'value' => PHP_VERSION],
    'pdo' => ['name' => 'PDO Extension', 'check' => extension_loaded('pdo'), 'value' => extension_loaded('pdo') ? 'Installed' : 'Missing'],
    'mbstring' => ['name' => 'Mbstring Extension', 'check' => extension_loaded('mbstring'), 'value' => extension_loaded('mbstring') ? 'Installed' : 'Missing'],
    'storage_writable' => ['name' => 'Storage Writable', 'check' => is_writable(ROOT_PATH . '/storage'), 'value' => is_writable(ROOT_PATH . '/storage') ? 'Yes' : 'No'],
    'config_writable' => ['name' => 'Config Writable', 'check' => is_writable(ROOT_PATH . '/config'), 'value' => is_writable(ROOT_PATH . '/config') ? 'Yes' : 'No']
];

// Procesamiento de pasos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2 && isset($_POST['db_host'])) {
        // Guardar datos de DB en sesión temporalmente
        $_SESSION['db_config'] = [
            'host' => $_POST['db_host'],
            'name' => $_POST['db_name'],
            'user' => $_POST['db_user'],
            'pass' => $_POST['db_pass'],
        ];
        $dbPort = isset($_POST['db_port']) ? trim((string) $_POST['db_port']) : '';
        if ($dbPort !== '' && ctype_digit($dbPort)) {
            $_SESSION['db_config']['port'] = (int) $dbPort;
        }

        // Probar conexión a la base de datos pre-creada (ej. en cPanel)
        try {
            $dsn = \Core\Database::dsnFromConfig($_SESSION['db_config']);
            $pdo = new PDO($dsn, $_POST['db_user'], $_POST['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            header('Location: install.php?step=3');
            exit;
        } catch (PDOException $e) {
            $error = "Error de conexión: " . $e->getMessage();
            $step = 2; // Volver al paso 2
        }
    }

    if ($step === 3) {
        // Ejecutar Migraciones
        try {
            $db = $_SESSION['db_config'];
            $dsn = \Core\Database::dsnFromConfig($db);
            $pdo = new PDO($dsn, $db['user'], $db['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "
                CREATE TABLE IF NOT EXISTS `clients` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NULL,
                    `business_name` VARCHAR(255) NULL,
                    `contact_name` VARCHAR(255) NULL,
                    `rut` VARCHAR(20) UNIQUE NOT NULL,
                    `email` VARCHAR(255),
                    `phone` VARCHAR(50),
                    `address` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `products` (
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
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `categories` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `name` VARCHAR(255) NOT NULL UNIQUE,
                    `parent_id` INT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `invoices` (
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
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`)
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `invoice_items` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `invoice_id` INT,
                    `product_id` INT,
                    `qty` DECIMAL(12,2) NOT NULL,
                    `price` DECIMAL(15,2) NOT NULL,
                    `original_price` DECIMAL(15,2) NULL,
                    `original_currency` VARCHAR(10) NULL,
                    `exchange_rate` DECIMAL(15,4) DEFAULT 1,
                    `total` DECIMAL(15,2) NOT NULL,
                    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
                    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `payments` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `invoice_id` INT,
                    `amount` DECIMAL(15,2) NOT NULL,
                    `currency` VARCHAR(10) DEFAULT 'CLP',
                    `exchange_rate` DECIMAL(15,4) DEFAULT 1,
                    `method` VARCHAR(50) NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `settings` (
                    `key` VARCHAR(100) PRIMARY KEY,
                    `value` TEXT,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `exchange_rates` (
                    `currency` VARCHAR(10) PRIMARY KEY,
                    `value` DECIMAL(15,4) NOT NULL,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `credit_notes` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `invoice_id` INT NOT NULL,
                    `reason` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB;

                CREATE TABLE IF NOT EXISTS `users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `email` VARCHAR(255) UNIQUE NOT NULL,
                    `password` VARCHAR(255) NOT NULL,
                    `name` VARCHAR(255),
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;
            ";

            $pdo->exec($sql);

            // Create additional tables missing from original migration
            $extraTables = [
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
                ) ENGINE=InnoDB"
            ];
            foreach ($extraTables as $tblSql) {
                $pdo->exec($tblSql);
            }
            $seedParent = $pdo->prepare("INSERT INTO categories (name, parent_id) SELECT ?, NULL WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = ?)");
            $seedCategory = $pdo->prepare("INSERT INTO categories (name, parent_id) SELECT ?, ? WHERE NOT EXISTS (SELECT 1 FROM categories WHERE name = ?)");
            $findCategory = $pdo->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
            foreach (['Servicios', 'Hardware', 'Software'] as $categoryName) {
                $seedParent->execute([$categoryName, $categoryName]);
                $findCategory->execute([$categoryName]);
                $parentId = (int)$findCategory->fetchColumn();
                $childName = $categoryName . ' General';
                $seedCategory->execute([$childName, $parentId, $childName]);
            }
            header('Location: install.php?step=4');
            exit;
        } catch (Exception $e) {
            $error = "Error al crear tablas: " . $e->getMessage();
            $step = 3;
        }
    }

    if ($step === 4 && isset($_POST['biz_name'])) {
        // Guardar configuración de negocio en sesión
        $_SESSION['business_config'] = $_POST;

        // Handle logo upload if present
        if (isset($_FILES['biz_logo']) && $_FILES['biz_logo']['error'] === UPLOAD_ERR_OK) {
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['biz_logo']['tmp_name']);
            finfo_close($finfo);
            if (in_array($mime, $allowedMimes, true) && $_FILES['biz_logo']['size'] <= 5 * 1024 * 1024) {
                $ext = strtolower(pathinfo($_FILES['biz_logo']['name'], PATHINFO_EXTENSION));
                $uploadDir = ROOT_PATH . '/public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $logoName = 'logo_' . bin2hex(random_bytes(8)) . '.' . $ext;
                if (move_uploaded_file($_FILES['biz_logo']['tmp_name'], $uploadDir . $logoName)) {
                    $_SESSION['business_config']['biz_logo'] = $logoName;
                }
            }
        }
        // Clean raw file array from session if upload failed
        if (isset($_SESSION['business_config']['biz_logo']) && is_array($_SESSION['business_config']['biz_logo'])) {
            unset($_SESSION['business_config']['biz_logo']);
        }

        header('Location: install.php?step=5');
        exit;
    }

    if ($step === 5 && isset($_POST['admin_email'])) {
        // Crear usuario admin y finalizar
        try {
            $db = $_SESSION['db_config'];
            $biz = $_SESSION['business_config'];
            $dsn = \Core\Database::dsnFromConfig($db);
            $pdo = new PDO($dsn, $db['user'], $db['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Insertar admin
            $passHash = password_hash($_POST['admin_pass'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, name)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    password = VALUES(password),
                    name = VALUES(name)
            ");
            $stmt->execute([$_POST['admin_email'], $passHash, $_POST['admin_name']]);

            // Insertar settings de negocio
            $stmt = $pdo->prepare("
                INSERT INTO settings (`key`, `value`)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
            ");
            foreach ($biz as $key => $val) {
                $stmt->execute([$key, $val]);
            }

            // Crear archivo lock
            file_put_contents(ROOT_PATH . '/storage/installed.lock', date('Y-m-d H:i:s'));

            // Crear archivo config/database.php
            $dbConfigContent = "<?php\nreturn " . var_export($db, true) . ";";
            file_put_contents(ROOT_PATH . '/config/database.php', $dbConfigContent);

            $success = "Instalación completada exitosamente.";
            $step = 6;
        } catch (Exception $e) {
            $error = "Error final: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación | FACTURADOR-PCCURICO</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="wizard-container">
        <div class="glass-card">
            <h1>Facturador PCCurico</h1>
            <p class="subtitle">Asistente de configuración del sistema</p>
            
            <div class="step-indicator">
                <?php for($i=1; $i<=6; $i++): ?>
                    <div class="step-dot <?php echo $i <= $step ? 'active' : ''; ?>"></div>
                <?php endfor; ?>
            </div>

            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; padding: 15px; border-radius: 12px; margin-bottom: 20px; color: #fca5a5; font-size: 0.9rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($step === 1): ?>
                <div>
                    <h2>Paso 1: Requisitos</h2>
                    <ul class="check-list" style="margin-top: 20px;">
                        <?php foreach ($requirements as $req): ?>
                            <li class="check-item">
                                <div class="check-icon <?php echo $req['check'] ? 'ok' : 'fail'; ?>"><?php echo $req['check'] ? '✓' : '✗'; ?></div>
                                <div style="flex: 1;"><strong><?php echo $req['name']; ?></strong><br><small><?php echo $req['value']; ?></small></div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <form action="install.php?step=2" method="POST">
                        <button type="submit" class="btn-primary">Continuar a Base de Datos</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($step === 2): ?>
                <div>
                    <h2>Paso 2: Base de Datos</h2>
                    <form action="install.php?step=2" method="POST">
                        <div class="form-group"><label>Host</label><input type="text" name="db_host" value="localhost"></div>
                        <div class="form-group"><label>Puerto (opcional)</label><input type="text" name="db_port" placeholder="3306" pattern="[0-9]*" inputmode="numeric" autocomplete="off"></div>
                        <div class="form-group"><label>Nombre DB</label><input type="text" name="db_name" required></div>
                        <div class="form-group"><label>Usuario</label><input type="text" name="db_user" required></div>
                        <div class="form-group"><label>Contraseña</label><input type="password" name="db_pass"></div>
                        <button type="submit" class="btn-primary">Conectar y Crear DB</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($step === 3): ?>
                <div style="text-align: center;">
                    <h2>Paso 3: Tablas y Migraciones</h2>
                    <p style="margin: 20px 0; color: var(--text-muted);">Estamos listos para preparar la estructura de datos para clientes, productos y facturación.</p>
                    <form action="install.php?step=3" method="POST">
                        <button type="submit" class="btn-primary">Ejecutar Migraciones Ahora</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($step === 4): ?>
                <div>
                    <h2>Paso 4: Datos del Negocio & Webpay</h2>
                    <form action="install.php?step=4" method="POST" enctype="multipart/form-data">
                        <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px; margin-top: 10px;">Perfil de la Empresa</h3>
                        <div class="form-group"><label>Nombre Empresa</label><input type="text" name="biz_name" required></div>
                        <div class="form-group"><label>RUT Empresa</label><input type="text" name="biz_rut" placeholder="76.123.456-7"></div>
                        <div class="form-group"><label>Dirección</label><input type="text" name="biz_address"></div>
                        <div class="form-group"><label>Giro</label><input type="text" name="biz_giro"></div>
                        <div class="form-group"><label>Logo de la Empresa (opcional)</label><input type="file" name="biz_logo" accept="image/jpeg,image/png,image/gif,image/webp"><small style="color: var(--text-muted); display:block; margin-top:5px;">Formato: PNG o JPG, máximo 5MB</small></div>
                        
                        <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 15px; margin-top: 30px;">Pasarela de Pago (Transbank Webpay Plus REST)</h3>
                        <div class="form-group">
                            <label>Entorno</label>
                            <select name="webpay_env" class="form-control" style="width: 100%; padding: 12px; background: rgba(15, 23, 42, 0.5); color: white; border: 1px solid var(--glass-border); border-radius: 8px;">
                                <option value="integration">Integración (Pruebas)</option>
                                <option value="production">Producción</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Código de Comercio (Commerce Code)</label><input type="text" name="webpay_cc" placeholder="597055555532"></div>
                        <div class="form-group"><label>API Key (Secret)</label><input type="text" name="webpay_key" placeholder="579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C"></div>
                        
                        <button type="submit" class="btn-primary" style="margin-top: 20px;">Siguiente</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($step === 5): ?>
                <div>
                    <h2>Paso 5: Usuario Administrador</h2>
                    <form action="install.php?step=5" method="POST">
                        <div class="form-group"><label>Nombre Completo</label><input type="text" name="admin_name" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="admin_email" required></div>
                        <div class="form-group"><label>Contraseña</label><input type="password" name="admin_pass" required></div>
                        <button type="submit" class="btn-primary">Finalizar Instalación</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($step === 6): ?>
                <div style="text-align: center;">
                    <div class="check-icon ok" style="width: 60px; height: 60px; margin: 0 auto 20px; font-size: 2rem;">✓</div>
                    <h2>¡Instalación Completada!</h2>
                    <p style="margin: 20px 0; color: var(--text-muted);">El sistema se ha configurado correctamente. Por seguridad, el instalador ha sido bloqueado.</p>
                    <a href="index.php" class="btn-primary" style="text-decoration: none; display: block;">Ir al Dashboard</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
