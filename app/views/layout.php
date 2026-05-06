<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Dashboard'; ?> | FACTURADOR-PCCURICO</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800&display=swap" rel="stylesheet">
    <script src="assets/js/lucide.min.js"></script>
</head>
<body>
    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-nav">
        <a href="index.php" class="mobile-nav-link <?= strpos($_SERVER['PHP_SELF'], 'index.php') !== false ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard"></i>
            <span>Inicio</span>
        </a>
        <a href="invoices.php" class="mobile-nav-link <?= strpos($_SERVER['PHP_SELF'], 'invoices.php') !== false ? 'active' : '' ?>">
            <i data-lucide="file-text"></i>
            <span>Ventas</span>
        </a>
        <a href="products.php" class="mobile-nav-link <?= strpos($_SERVER['PHP_SELF'], 'products.php') !== false ? 'active' : '' ?>">
            <i data-lucide="package"></i>
            <span>Stock</span>
        </a>
        <a href="settings.php" class="mobile-nav-link <?= strpos($_SERVER['PHP_SELF'], 'settings.php') !== false ? 'active' : '' ?>">
            <i data-lucide="more-horizontal"></i>
            <span>Más</span>
        </a>
    </nav>

    <div class="app-layout">
        <aside class="sidebar">
            <div style="text-align: center; margin-bottom: 40px;">
                <img src="assets/img/logo.png" alt="PC Curico" class="brand-logo">
                <p class="brand-caption">Facturador Pro</p>
            </div>
            
            <nav class="nav-menu">
                <div class="nav-item">
                    <a href="index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'index.php') !== false ? 'active' : '' ?>">
                        <i data-lucide="layout-dashboard"></i> Dashboard
                    </a>
                </div>
                <div class="nav-item">
                    <a href="invoices.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'invoices.php') !== false ? 'active' : '' ?>">
                        <i data-lucide="file-text"></i> Facturación
                    </a>
                </div>
                <div class="nav-item">
                    <a href="products.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'products.php') !== false ? 'active' : '' ?>">
                        <i data-lucide="package"></i> Productos
                    </a>
                </div>
                <div class="nav-item">
                    <a href="clients.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'clients.php') !== false ? 'active' : '' ?>">
                        <i data-lucide="users"></i> Clientes
                    </a>
                </div>
                <div class="nav-item">
                    <a href="templates.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'templates.php') !== false ? 'active' : '' ?>">
                        <i data-lucide="palette"></i> Plantillas
                    </a>
                </div>
                <div class="nav-item">
                    <a href="settings.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'settings.php') !== false ? 'active' : '' ?>">
                        <i data-lucide="settings"></i> Configuración
                    </a>
                </div>
                
                <div class="nav-item" style="margin-top: auto; padding-top: 30px;">
                    <a href="logout.php" class="nav-link logout-link">
                        <i data-lucide="log-out"></i> Cerrar Sesión
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-branding">
                    <img src="assets/img/favicon.png" alt="" class="mobile-only-logo" style="display:none; width:30px; height:30px;">
                    <div>
                        <h2 class="page-title"><?php echo $title ?? 'Resumen General'; ?></h2>
                        <p class="header-date"><?php
                            $dias = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
                            $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                            echo $dias[date('w')] . ', ' . date('d') . ' ' . $meses[(int)date('n')];
                        ?></p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-name"><?php echo \Core\Auth::user()['name']; ?></div>
                        <div class="user-role">Administrador</div>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr(\Core\Auth::user()['name'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <?php include $contentFile; ?>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
