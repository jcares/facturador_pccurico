<?php
header('Content-Type: text/html; charset=utf-8');
$current = basename($_SERVER['PHP_SELF'] ?? '');
$isActive = function (array $files) use ($current) {
    return in_array($current, $files, true) ? 'active' : '';
};
$user = \Core\Auth::user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Resumen General') ?> | FACTURADOR-PCCURICO</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <style>
        :root {
            color-scheme: dark;
            --bg: #050812;
            --surface: rgba(10, 16, 32, 0.96);
            --panel: rgba(10, 16, 34, 0.94);
            --panel-soft: rgba(15, 23, 42, 0.92);
            --panel-border: rgba(71, 85, 105, 0.18);
            --glass-border: rgba(56, 189, 248, 0.14);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --text-subtle: #cbd5e1;
            --primary: #22d3ee;
            --primary-strong: #7c3aed;
            --accent: #ec4899;
            --success: #22c55e;
            --warning: #facc15;
            --danger: #f43f5e;
            --radius: 20px;
            --shadow: 0 24px 70px rgba(0,0,0,0.35);
        }

        body {
            background: radial-gradient(circle at 14% 14%, rgba(34, 211, 238, 0.11), transparent 18%),
                        radial-gradient(circle at 86% 20%, rgba(124, 58, 237, 0.12), transparent 17%),
                        radial-gradient(circle at 50% 100%, rgba(59, 130, 246, 0.08), transparent 26%),
                        #050812;
            color: var(--text-main);
        }

        .app-layout {
            min-height: 100vh;
            background: transparent;
        }

        .sidebar {
            background: rgba(7, 11, 21, 0.94);
            border-right: 1px solid rgba(56, 189, 248, 0.12);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.02);
        }

        .brand-block {
            padding: 24px 20px 20px;
            border-bottom: 1px solid rgba(56, 189, 248, 0.08);
            margin-bottom: 22px;
        }

        .brand-caption {
            margin-top: 10px;
            color: var(--text-muted);
            font-size: 0.92rem;
            letter-spacing: 0.03em;
        }

        .nav-menu a,
        .nav-menu button {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 10px;
            width: 100%;
            padding: 14px 18px;
            border-radius: 16px;
            margin-bottom: 6px;
            color: var(--text-muted);
            font-weight: 600;
            background: transparent;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .nav-link i,
        .nav-parent i,
        .nav-sub-link i {
            width: 18px;
            min-width: 18px;
        }

        .nav-link:hover,
        .nav-parent:hover,
        .nav-sub-link:hover {
            background: rgba(56, 189, 248, 0.08);
            color: #fff;
        }

        .nav-link.active,
        .nav-sub-link.active,
        .nav-parent.open {
            color: var(--primary);
            background: rgba(15, 23, 42, 0.76);
            box-shadow: inset 0 0 0 1px rgba(34, 211, 238, 0.16);
        }

        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.24s ease, opacity 0.24s ease;
            margin-bottom: 8px;
        }

        .nav-submenu.open {
            max-height: 360px;
            opacity: 1;
        }

        .nav-sub-link {
            padding-left: 44px;
            color: var(--text-subtle);
            font-size: 0.95rem;
            background: rgba(255,255,255,0.03);
            border-radius: 14px;
        }

        .nav-sub-link.active {
            color: #7dd3fc;
        }

        .nav-section.nav-bottom {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid rgba(148, 163, 184, 0.12);
        }

        .nav-link.logout-link {
            color: #f43f5e;
        }

        .main-content {
            background: transparent;
        }

        .main-header {
            padding: 24px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .header-branding {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .page-title {
            margin: 0;
            font-size: 1.55rem;
            letter-spacing: 0.02em;
        }

        .header-date {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .header-actions {
            display: flex;
            gap: 14px;
            align-items: center;
            flex-wrap: wrap;
        }

        .header-quick-action,
        .btn-primary,
        .btn-secondary,
        .link-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border-radius: 999px;
            padding: 12px 18px;
            font-weight: 700;
            text-decoration: none;
            border: 1px solid transparent;
            transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        .header-quick-action {
            background: linear-gradient(135deg, #22d3ee 0%, #7c3aed 100%);
            color: #020617;
            box-shadow: 0 18px 35px rgba(34, 211, 238, 0.16);
        }

        .btn-primary {
            background: linear-gradient(135deg, #22d3ee 0%, #7c3aed 100%);
            color: #020617;
            box-shadow: 0 18px 35px rgba(34, 211, 238, 0.16);
        }

        .btn-secondary {
            background: rgba(15, 23, 42, 0.85);
            color: #fff;
            border-color: rgba(148, 163, 184, 0.18);
        }

        .btn-primary:hover,
        .btn-secondary:hover,
        .link-button:hover,
        .header-quick-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 24px 45px rgba(34, 211, 238, 0.18);
        }

        .user-info {
            text-align: right;
            min-width: 140px;
        }

        .user-name {
            font-weight: 700;
            color: #f8fafc;
        }

        .user-role {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: linear-gradient(180deg, rgba(34, 211, 238, 0.18), rgba(124, 58, 237, 0.18));
            color: #fff;
            font-weight: 700;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.08);
        }

        .content-wrapper {
            padding-bottom: 50px;
        }

        .glass-card {
            background: rgba(9, 14, 28, 0.94);
            border: 1px solid rgba(56, 189, 248, 0.08);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            padding: 24px;
        }

        .section-heading {
            color: #f8fafc;
            font-size: 1.05rem;
            margin-bottom: 16px;
            letter-spacing: 0.01em;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            position: relative;
            background: rgba(15, 23, 42, 0.94);
            border: 1px solid rgba(255,255,255,0.04);
            border-radius: 22px;
            padding: 22px;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(34, 211, 238, 0.16), transparent 22%), radial-gradient(circle at bottom left, rgba(124, 58, 237, 0.14), transparent 18%);
            pointer-events: none;
        }

        .stat-card > * {
            position: relative;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #fff;
        }

        .icon-badge {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: rgba(15, 23, 42, 0.88);
            color: #fff;
        }

        .icon-badge.green { background: rgba(16, 185, 129, 0.22); color: #22c55e; }
        .icon-badge.blue { background: rgba(59, 130, 246, 0.18); color: #38bdf8; }
        .icon-badge.yellow { background: rgba(250, 204, 21, 0.18); color: #facc15; }

        .table-container {
            overflow-x: auto;
        }

        .table-clean {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
            border-radius: 18px;
            overflow: hidden;
        }

        .table-clean th,
        .table-clean td {
            padding: 16px 14px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .table-clean thead tr {
            background: rgba(15, 23, 42, 0.9);
        }

        .table-clean tbody tr:hover {
            background: rgba(255,255,255,0.04);
        }

        .text-center {
            text-align: center;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .summary-card {
            background: rgba(12, 18, 38, 0.9);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 22px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.02);
        }

        .summary-title {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-bottom: 10px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .summary-value {
            font-size: 1.85rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .summary-small {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .progress-bar {
            height: 8px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(255,255,255,0.06);
            margin-top: 14px;
        }

        .progress-bar span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #22d3ee 0%, #7c3aed 100%);
        }

        @media (max-width: 900px) {
            .stat-grid,
            .summary-grid,
            .main-grid {
                grid-template-columns: 1fr;
            }
            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
            .user-info {
                text-align: left;
                width: 100%;
            }
            .mobile-nav {
                display: flex;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 20;
                padding: 8px 10px;
                background: rgba(7, 11, 21, 0.96);
                border-top: 1px solid rgba(56, 189, 248, 0.12);
                backdrop-filter: blur(16px);
                gap: 6px;
                justify-content: space-between;
            }
            .mobile-nav-link {
                flex: 1;
                color: var(--text-muted);
                text-decoration: none;
                font-size: 0.75rem;
                display: inline-flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 4px;
                padding: 8px 6px;
                border-radius: 14px;
                transition: background 0.2s ease, color 0.2s ease;
            }
            .mobile-nav-link.active {
                background: rgba(15, 23, 42, 0.88);
                color: #7dd3fc;
            }
            .mobile-nav-link i {
                width: 18px;
                height: 18px;
            }
            .content-wrapper {
                padding-bottom: 100px;
            }
        }
    </style>
    <script src="assets/js/lucide.min.js?v=20260506-charset"></script>
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar">
            <div class="brand-block">
                <img src="assets/img/logo.png" alt="PC Curico" class="brand-logo">
                <p class="brand-caption">Facturador PCCurico</p>
            </div>

            <nav class="nav-menu">
                <a href="index.php" class="nav-link <?= $isActive(['index.php']) ?>">
                    <i data-lucide="home"></i> Inicio
                </a>
                <a href="clients.php" class="nav-link <?= $isActive(['clients.php']) ?>">
                    <i data-lucide="users"></i> Clientes
                </a>
                <button class="nav-parent <?= in_array($current, ['products.php', 'categories.php'], true) ? 'open' : '' ?>" type="button" data-submenu="products-menu">
                    <span><i data-lucide="package"></i> Productos</span>
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="nav-submenu" id="products-menu">
                    <a href="products.php" class="nav-sub-link <?= $isActive(['products.php']) ?>">Productos</a>
                    <a href="categories.php" class="nav-sub-link <?= $isActive(['categories.php']) ?>">Categorías</a>
                </div>
                <button class="nav-parent <?= in_array($current, ['invoices.php','recurring_invoices.php','credit_notes.php'], true) ? 'open' : '' ?>" type="button" data-submenu="invoices-menu">
                    <span><i data-lucide="file-text"></i> Facturas</span>
                    <i data-lucide="chevron-down"></i>
                </button>
                <div class="nav-submenu <?= in_array($current, ['invoices.php','recurring_invoices.php','credit_notes.php'], true) ? 'open' : '' ?>" id="invoices-menu">
                    <a href="invoices.php?action=create" class="nav-sub-link <?= $current === 'invoices.php' && ($_GET['action'] ?? '') === 'create' ? 'active' : '' ?>">Crear Factura</a>
                    <a href="invoices.php" class="nav-sub-link <?= $current === 'invoices.php' && ($_GET['action'] ?? '') !== 'create' ? 'active' : '' ?>">Ver Facturas</a>
                    <a href="recurring_invoices.php" class="nav-sub-link <?= $isActive(['recurring_invoices.php']) ?>">Facturas Recurrentes</a>
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
                    <i data-lucide="shopping-bag"></i> Órdenes de compra
                </a>
                <a href="expenses.php" class="nav-link <?= $isActive(['expenses.php']) ?>">
                    <i data-lucide="dollar-sign"></i> Gastos
                </a>
                <a href="reports.php" class="nav-link <?= $isActive(['reports.php']) ?>">
                    <i data-lucide="bar-chart"></i> Informes
                </a>
                <a href="company.php" class="nav-link <?= $isActive(['company.php', 'settings.php', 'localization.php', 'taxes.php', 'product_settings.php', 'email_settings.php', 'email_templates.php', 'templates.php', 'client_portal.php', 'tools.php']) ?>">
                    <i data-lucide="settings"></i> Configuración
                </a>

                <div class="nav-section nav-bottom">
                    <a href="logout.php" class="nav-link logout-link">
                        <i data-lucide="log-out"></i> Cerrar Sesión
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-branding">
                    <img src="assets/img/favicon.png" alt="" class="mobile-only-logo">
                    <div>
                        <h2 class="page-title"><?= htmlspecialchars($title ?? 'Resumen General') ?></h2>
                        <p class="header-date"><?php
                            $dias = ['Dom','Lun','Mar','Mie','Jue','Vie','Sab'];
                            $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                            echo $dias[(int)date('w')] . ', ' . date('d') . ' ' . $meses[(int)date('n')];
                        ?></p>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="invoices.php?action=create" class="header-quick-action">
                        <i data-lucide="scan-barcode"></i>
                        Nueva venta
                    </a>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></div>
                        <div class="user-role">Administrador</div>
                    </div>
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                <?php include $contentFile; ?>
            </div>
        </main>
    </div>

    <script>

        // Toast Notification System
        class Toast {
            static show(message, type = 'info', duration = 3000) {
                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                toast.innerHTML = `
                    <div class="toast-content">
                        <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}"></i>
                        <span>${message}</span>
                    </div>
                    <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
                `;
                document.body.appendChild(toast);
                
                if (typeof lucide !== 'undefined') lucide.createIcons();
                
                setTimeout(() => {
                    toast.classList.add('show');
                }, 10);
                
                if (duration > 0) {
                    setTimeout(() => {
                        toast.classList.remove('show');
                        setTimeout(() => toast.remove(), 300);
                    }, duration);
                }
                
                return toast;
            }
        }

        // Form Validation
        function validateForm(form) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });
            
            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
                    field.classList.add('error');
                    isValid = false;
                }
            });
            
            // Number validation
            const numberFields = form.querySelectorAll('input[type="number"]');
            numberFields.forEach(field => {
                if (field.value && isNaN(field.value)) {
                    field.classList.add('error');
                    isValid = false;
                }
            });
            
            return isValid;
        }

        // Form submission with loading states
        function handleFormSubmit(form, submitBtn) {
            if (!validateForm(form)) {
                Toast.show('Por favor complete todos los campos requeridos correctamente.', 'error');
                return false;
            }

            if (!submitBtn) {
                return true;
            }
            
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span> Procesando...';
            
            // Re-enable after 10 seconds as fallback
            setTimeout(() => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || 'Guardar';
            }, 10000);
            
            return true;
        }

        // Mobile Optimizations
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation and loading states
            document.querySelectorAll('form').forEach(form => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.setAttribute('data-original-text', submitBtn.innerHTML);
                }
                
                form.addEventListener('submit', function(e) {
                    if (!handleFormSubmit(this, submitBtn)) {
                        e.preventDefault();
                        return false;
                    }
                });
            });
            
            // Auto-hide alerts after 5 seconds
            document.querySelectorAll('.alert').forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
            
            // Check URL parameters for messages
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success')) {
                const messages = {
                    'created': 'Registro creado exitosamente',
                    'updated': 'Registro actualizado exitosamente', 
                    'deleted': 'Registro eliminado exitosamente',
                    'payment_registered': 'Pago registrado exitosamente',
                    'canceled': 'Documento anulado exitosamente'
                };
                const message = messages[urlParams.get('success')] || 'Operacion completada';
                Toast.show(message, 'success');
            }
            
            if (urlParams.get('error')) {
                const messages = {
                    'invalid_data': 'Datos invalidos',
                    'invalid_client': 'Selecciona un cliente valido',
                    'invalid_invoice': 'Documento invalido',
                    'no_items': 'Agrega al menos un producto con cantidad',
                    'db_error': 'Error en la base de datos',
                    'not_found': 'Registro no encontrado',
                    'validation': 'Error de validacion',
                    'cancel_failed': 'No se pudo anular el documento'
                };
                const message = messages[urlParams.get('error')] || 'Ha ocurrido un error';
                Toast.show(message, 'error');
            }
        });
    </script>

    <!-- Toast Styles -->
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            z-index: 10000;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            max-width: 300px;
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .toast-content i {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        
        .toast-success .toast-content i { color: #10b981; }
        .toast-error .toast-content i { color: #ef4444; }
        .toast-info .toast-content i { color: var(--primary); }
        
        .toast-close {
            position: absolute;
            top: 8px;
            right: 8px;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 18px;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .toast-close:hover {
            color: var(--text-main);
        }
        
        input.error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }
        
        @media (max-width: 575px) {
            .toast {
                left: 16px;
                right: 16px;
                max-width: none;
            }
        }
    </style>

    <!-- Mobile Bottom Navigation -->
    <nav class="mobile-nav">
        <a href="index.php" class="mobile-nav-link <?= $isActive(['index.php']) ? 'active' : '' ?>">
            <i data-lucide="home"></i>
            <span>Inicio</span>
        </a>
        <a href="invoices.php?action=create" class="mobile-nav-link <?= $current === 'invoices.php' && ($_GET['action'] ?? '') === 'create' ? 'active' : '' ?>">
            <i data-lucide="shopping-cart"></i>
            <span>Nueva Factura</span>
        </a>
        <a href="invoices.php" class="mobile-nav-link <?= in_array($current, ['invoices.php', 'recurring_invoices.php', 'quotes.php', 'credit_notes.php', 'payments.php'], true) && ($_GET['action'] ?? '') !== 'create' ? 'active' : '' ?>">
            <i data-lucide="file-text"></i>
            <span>Facturas</span>
        </a>
        <a href="recurring_invoices.php" class="mobile-nav-link <?= $isActive(['recurring_invoices.php']) ? 'active' : '' ?>">
            <i data-lucide="repeat"></i>
            <span>Recurrentes</span>
        </a>
        <a href="products.php" class="mobile-nav-link <?= $isActive(['products.php']) ? 'active' : '' ?>">
            <i data-lucide="package"></i>
            <span>Productos</span>
        </a>
        <a href="clients.php" class="mobile-nav-link <?= $isActive(['clients.php']) ? 'active' : '' ?>">
            <i data-lucide="users"></i>
            <span>Clientes</span>
        </a>
        <a href="settings.php" class="mobile-nav-link <?= $isActive(['settings.php', 'tools.php', 'templates.php']) ? 'active' : '' ?>">
            <i data-lucide="settings"></i>
            <span>Config</span>
        </a>
    </nav>

    <script>
        // Menu Management - Submenus closed by default (per BITACORA.md)
        document.querySelectorAll('[data-submenu]').forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const menuId = button.dataset.submenu;
                const menu = document.getElementById(menuId);
                
                if (!menu) return;
                
                const isCurrentlyOpen = menu.classList.contains('open');
                
                // Close all other submenus when opening a new one
                document.querySelectorAll('[data-submenu]').forEach((otherButton) => {
                    const otherMenu = document.getElementById(otherButton.dataset.submenu);
                    if (otherMenu && otherMenu !== menu) {
                        otherMenu.classList.remove('open');
                        otherButton.classList.remove('open');
                    }
                });
                
                // Toggle current menu and button state
                if (isCurrentlyOpen) {
                    menu.classList.remove('open');
                    button.classList.remove('open');
                } else {
                    menu.classList.add('open');
                    button.classList.add('open');
                }
            });
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();
    </script>
</body>
</html>
