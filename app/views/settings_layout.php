<?php
ob_start();

$current = basename($_SERVER['PHP_SELF'] ?? '');
$currentSection = $_GET['section'] ?? '';
$isActive = function (array $files, ?string $section = null) use ($current, $currentSection) {
    if ($section !== null) {
        return ($current === 'settings.php' && $currentSection === $section) ? 'active' : '';
    }
    return in_array($current, $files, true) ? 'active' : '';
};
$user = \Core\Auth::user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Configuración') ?> | FACTURADOR-PCCURICO</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/assets/css/style_mobile.css?v=<?= time() ?>">
    <script src="/assets/js/lucide.min.js"></script>
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar">
            <div class="brand-block">
                <a href="index.php">
                    <img src="/assets/img/logo.png" alt="PC Curico" class="brand-logo">
                </a>
            </div>

            <nav class="nav-menu">
                <a href="index.php" class="nav-link <?= $isActive(['index.php']) ?>">
                    <i data-lucide="home"></i> Inicio
                </a>
                <a href="clients.php" class="nav-link <?= $isActive(['clients.php']) ?>">
                    <i data-lucide="users"></i> Clientes
                </a>
                
                <button class="nav-parent <?= in_array($current, ['products.php', 'categories.php']) ? 'open' : '' ?>" type="button" data-submenu="products-menu">
                    <span><i data-lucide="package"></i> Productos</span>
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="nav-submenu <?= in_array($current, ['products.php', 'categories.php']) ? 'open' : '' ?>" id="products-menu">
                    <a href="products.php" class="nav-sub-link <?= $isActive(['products.php']) ?>">Gestionar Productos</a>
                    <a href="categories.php" class="nav-sub-link <?= $isActive(['categories.php']) ?>">Categorías</a>
                </div>

                <button class="nav-parent <?= in_array($current, ['invoices.php','recurring_invoices.php','credit_notes.php']) ? 'open' : '' ?>" type="button" data-submenu="invoices-menu">
                    <span><i data-lucide="file-text"></i> Facturas</span>
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="nav-submenu <?= in_array($current, ['invoices.php','recurring_invoices.php','credit_notes.php']) ? 'open' : '' ?>" id="invoices-menu">
                    <a href="invoices.php?action=create" class="nav-sub-link <?= $isActive(['invoices.php'], 'create') ?>">Nueva Factura</a>
                    <a href="invoices.php" class="nav-sub-link <?= $current === 'invoices.php' && $currentAction !== 'create' ? 'active' : '' ?>">Ver Facturas</a>
                    <a href="recurring_invoices.php" class="nav-sub-link <?= $isActive(['recurring_invoices.php']) ?>">Recurrentes</a>
                    <a href="credit_notes.php" class="nav-sub-link <?= $isActive(['credit_notes.php']) ?>">Notas de Crédito</a>
                </div>

                <a href="payments.php" class="nav-link <?= $isActive(['payments.php']) ?>">
                    <i data-lucide="credit-card"></i> Pagos
                </a>
                <a href="quotes.php" class="nav-link <?= $isActive(['quotes.php']) ?>">
                    <i data-lucide="clipboard-list"></i> Cotizaciones
                </a>
                <a href="tasks.php" class="nav-link <?= $isActive(['tasks.php']) ?>">
                    <i data-lucide="check-square"></i> Tareas
                </a>
                <a href="purchase_orders.php" class="nav-link <?= $isActive(['purchase_orders.php']) ?>">
                    <i data-lucide="shopping-bag"></i> Órdenes de Compra
                </a>
                <a href="expenses.php" class="nav-link <?= $isActive(['expenses.php']) ?>">
                    <i data-lucide="dollar-sign"></i> Gastos
                </a>
                <a href="reports.php" class="nav-link <?= $isActive(['reports.php']) ?>">
                    <i data-lucide="bar-chart"></i> Informes
                </a>

                <button class="nav-parent <?= in_array($current, ['settings.php', 'templates.php', 'tools.php']) ? 'open' : '' ?>" type="button" data-submenu="settings-menu">
                    <span><i data-lucide="settings"></i> Configuración</span>
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="nav-submenu <?= in_array($current, ['settings.php', 'templates.php', 'tools.php']) ? 'open' : '' ?>" id="settings-menu">
                    <a href="settings.php?section=company" class="nav-sub-link <?= $isActive(['settings.php'], 'company') ?>">Empresa</a>
                    <a href="settings.php?section=transbank" class="nav-sub-link <?= $isActive(['settings.php'], 'transbank') ?>">Transbank (Webpay)</a>
                    <a href="settings.php?section=email" class="nav-sub-link <?= $isActive(['settings.php'], 'email') ?>">Correo</a>
                    <a href="templates.php" class="nav-sub-link <?= $isActive(['templates.php']) ?>">Plantilla Factura</a>
                    <a href="tools.php?action=log" class="nav-sub-link <?= $current === 'tools.php' && ($_GET['action'] ?? '') === 'log' ? 'active' : '' ?>">Logs de Sistema</a>
                    <a href="tools.php" class="nav-sub-link <?= $current === 'tools.php' && !isset($_GET['action']) ? 'active' : '' ?>">Importar/Exportar</a>
                </div>

                <div class="nav-section nav-bottom">
                    <a href="logout.php" class="nav-link logout-link">
                        <i data-lucide="log-out"></i> Salir
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-branding">
                    <h2 class="page-title"><?= htmlspecialchars($title ?? 'Configuración') ?></h2>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></div>
                        <div class="user-role">Administrador</div>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <?php 
                if (!empty($contentFile)) {
                    include $contentFile; 
                } else if (!empty($body)) {
                    echo $body;
                }
                ?>
            </div>
        </main>
    </div>

    <nav class="mobile-nav">
        <button class="mobile-nav-toggle" id="mobile-nav-toggle">
            <i data-lucide="menu"></i>
        </button>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobile-menu-overlay">
        <div class="mobile-menu-header">
            <h3>Menú</h3>
            <button class="mobile-menu-close" id="mobile-menu-close">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="mobile-menu-content">
            <a href="index.php" class="mobile-menu-link <?= $isActive(['index.php']) ?>">
                <i data-lucide="home"></i> Inicio
            </a>
            <a href="clients.php" class="mobile-menu-link <?= $isActive(['clients.php']) ?>">
                <i data-lucide="users"></i> Clientes
            </a>
            
            <button class="mobile-menu-parent <?= in_array($current, ['products.php', 'categories.php']) ? 'open' : '' ?>" type="button" data-submenu="mobile-products-menu">
                <span><i data-lucide="package"></i> Productos</span>
                <i data-lucide="chevron-down"></i>
            </button>
            <div class="mobile-menu-submenu <?= in_array($current, ['products.php', 'categories.php']) ? 'open' : '' ?>" id="mobile-products-menu">
                <a href="products.php" class="mobile-menu-sub-link <?= $isActive(['products.php']) ?>">Gestionar Productos</a>
                <a href="categories.php" class="mobile-menu-sub-link <?= $isActive(['categories.php']) ?>">Categorías</a>
            </div>

            <button class="mobile-menu-parent <?= in_array($current, ['invoices.php','recurring_invoices.php','credit_notes.php']) ? 'open' : '' ?>" type="button" data-submenu="mobile-invoices-menu">
                <span><i data-lucide="file-text"></i> Facturas</span>
                <i data-lucide="chevron-down"></i>
            </button>
            <div class="mobile-menu-submenu <?= in_array($current, ['invoices.php','recurring_invoices.php','credit_notes.php']) ? 'open' : '' ?>" id="mobile-invoices-menu">
                <a href="invoices.php?action=create" class="mobile-menu-sub-link <?= $isActive(['invoices.php'], 'create') ?>">Nueva Factura</a>
                <a href="invoices.php" class="mobile-menu-sub-link <?= $current === 'invoices.php' && $currentAction !== 'create' ? 'active' : '' ?>">Ver Facturas</a>
                <a href="recurring_invoices.php" class="mobile-menu-sub-link <?= $isActive(['recurring_invoices.php']) ?>">Recurrentes</a>
                <a href="credit_notes.php" class="mobile-menu-sub-link <?= $isActive(['credit_notes.php']) ?>">Notas de Crédito</a>
            </div>

            <a href="payments.php" class="mobile-menu-link <?= $isActive(['payments.php']) ?>">
                <i data-lucide="credit-card"></i> Pagos
            </a>
            <a href="quotes.php" class="mobile-menu-link <?= $isActive(['quotes.php']) ?>">
                <i data-lucide="clipboard-list"></i> Cotizaciones
            </a>
            <a href="tasks.php" class="mobile-menu-link <?= $isActive(['tasks.php']) ?>">
                <i data-lucide="check-square"></i> Tareas
            </a>
            <a href="purchase_orders.php" class="mobile-menu-link <?= $isActive(['purchase_orders.php']) ?>">
                <i data-lucide="shopping-bag"></i> Órdenes de Compra
            </a>
            <a href="expenses.php" class="mobile-menu-link <?= $isActive(['expenses.php']) ?>">
                <i data-lucide="dollar-sign"></i> Gastos
            </a>
            <a href="reports.php" class="mobile-menu-link <?= $isActive(['reports.php']) ?>">
                <i data-lucide="bar-chart"></i> Informes
            </a>

            <button class="mobile-menu-parent <?= in_array($current, ['settings.php', 'templates.php', 'tools.php']) ? 'open' : '' ?>" type="button" data-submenu="mobile-settings-menu">
                <span><i data-lucide="settings"></i> Configuración</span>
                <i data-lucide="chevron-down"></i>
            </button>
            <div class="mobile-menu-submenu <?= in_array($current, ['settings.php', 'templates.php', 'tools.php']) ? 'open' : '' ?>" id="mobile-settings-menu">
                <a href="settings.php?section=company" class="mobile-menu-sub-link <?= $isActive(['settings.php'], 'company') ?>">Empresa</a>
                <a href="settings.php?section=transbank" class="mobile-menu-sub-link <?= $isActive(['settings.php'], 'transbank') ?>">Transbank (Webpay)</a>
                <a href="settings.php?section=email" class="mobile-menu-sub-link <?= $isActive(['settings.php'], 'email') ?>">Correo</a>
                <a href="templates.php" class="mobile-menu-sub-link <?= $isActive(['templates.php']) ?>">Plantilla Factura</a>
                <a href="tools.php?action=log" class="mobile-menu-sub-link <?= $current === 'tools.php' && ($_GET['action'] ?? '') === 'log' ? 'active' : '' ?>">Logs de Sistema</a>
                <a href="tools.php" class="mobile-menu-sub-link <?= $current === 'tools.php' && !isset($_GET['action']) ? 'active' : '' ?>">Importar/Exportar</a>
            </div>

            <div class="mobile-menu-bottom">
                <a href="logout.php" class="mobile-menu-link logout-link">
                    <i data-lucide="log-out"></i> Salir
                </a>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-submenu]').forEach(button => {
            button.addEventListener('click', () => {
                const sub = document.getElementById(button.dataset.submenu);
                if (!sub) return;
                const isOpen = sub.classList.contains('open');
                sub.classList.toggle('open', !isOpen);
                button.classList.toggle('open', !isOpen);
            });
        });

        // Mobile menu toggle
        const mobileNavToggle = document.getElementById('mobile-nav-toggle');
        const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
        const mobileMenuClose = document.getElementById('mobile-menu-close');

        if (mobileNavToggle && mobileMenuOverlay) {
            mobileNavToggle.addEventListener('click', () => {
                mobileMenuOverlay.classList.add('open');
            });
        }

        if (mobileMenuClose && mobileMenuOverlay) {
            mobileMenuClose.addEventListener('click', () => {
                mobileMenuOverlay.classList.remove('open');
            });
        }

        if (mobileMenuOverlay) {
            mobileMenuOverlay.addEventListener('click', (e) => {
                if (e.target === mobileMenuOverlay) {
                    mobileMenuOverlay.classList.remove('open');
                }
            });
        }

        if (window.lucide) {
            lucide.createIcons();
            setTimeout(() => lucide.createIcons(), 500);
            setTimeout(() => lucide.createIcons(), 2000);
        }

        // Global Toast Notification Helper
        class Toast {
            static show(message, type = 'info', duration = 4000) {
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = `<div class="toast-content"><i data-lucide="${type === 'success' ? 'check-circle' : (type === 'error' ? 'alert-octagon' : 'info')}"></i><span>${message}</span></div>`;
                document.body.appendChild(toast);
                if (window.lucide) lucide.createIcons();
                
                requestAnimationFrame(() => {
                    toast.classList.add('show');
                });

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 500);
                }, duration);
            }
        }
    </script>
</body>
</html>
<?php ob_end_flush(); ?>
