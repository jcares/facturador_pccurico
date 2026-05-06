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
    <title><?= htmlspecialchars($title ?? 'Dashboard') ?> | FACTURADOR-PCCURICO</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <script src="assets/js/lucide.min.js?v=20260506-charset"></script>
</head>
<body>
    <div class="app-layout">
        <aside class="sidebar">
            <div class="brand-block">
                <img src="assets/img/logo.png" alt="PC Curico" class="brand-logo">
                <p class="brand-caption">POS Facturador</p>
            </div>

            <nav class="nav-menu">
                <div class="nav-section">
                    <div class="nav-section-title">Operacion</div>
                    <a href="index.php" class="nav-link <?= $isActive(['index.php']) ?>">
                        <i data-lucide="layout-dashboard"></i> Dashboard
                    </a>
                    <a href="invoices.php?action=create" class="nav-link <?= $current === 'invoices.php' && ($_GET['action'] ?? '') === 'create' ? 'active' : '' ?>">
                        <i data-lucide="shopping-cart"></i> Punto de Venta
                    </a>
                    <button class="nav-parent" type="button" data-submenu="sales-menu">
                        <span><i data-lucide="receipt-text"></i> Ventas</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="nav-submenu" id="sales-menu">
                        <a href="invoices.php" class="nav-sub-link <?= $current === 'invoices.php' && ($_GET['action'] ?? '') !== 'create' ? 'active' : '' ?>">Facturas y boletas</a>
                        <a href="recurring_invoices.php" class="nav-sub-link <?= $isActive(['recurring_invoices.php']) ?>">Recurrentes</a>
                        <a href="quotes.php" class="nav-sub-link <?= $isActive(['quotes.php']) ?>">Cotizaciones</a>
                        <a href="credit_notes.php" class="nav-sub-link <?= $isActive(['credit_notes.php']) ?>">Notas de credito</a>
                        <a href="payments.php" class="nav-sub-link <?= $isActive(['payments.php']) ?>">Pagos</a>
                    </div>
                </div>

                <div class="nav-section">
                    <button class="nav-parent" type="button" data-submenu="catalog-menu">
                        <span><i data-lucide="archive"></i> Catalogo</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="nav-submenu" id="catalog-menu">
                        <a href="products.php" class="nav-sub-link <?= $isActive(['products.php']) ?>">Productos e inventario</a>
                        <a href="categories.php" class="nav-sub-link <?= $isActive(['categories.php']) ?>">Categorias</a>
                        <a href="clients.php" class="nav-sub-link <?= $isActive(['clients.php']) ?>">Clientes</a>
                    </div>
                </div>

                <div class="nav-section">
                    <button class="nav-parent" type="button" data-submenu="admin-menu">
                        <span><i data-lucide="sliders-horizontal"></i> Administracion</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="nav-submenu" id="admin-menu">
                        <a href="reports.php" class="nav-sub-link <?= $isActive(['reports.php']) ?>">Reportes</a>
                        <a href="templates.php" class="nav-sub-link <?= $isActive(['templates.php']) ?>">Plantillas</a>
                        <a href="client_portal.php" class="nav-sub-link <?= $isActive(['client_portal.php']) ?>">Portal clientes</a>
                        <a href="settings.php" class="nav-sub-link <?= $isActive(['settings.php']) ?>">Configuracion</a>
                    </div>
                </div>

                <div class="nav-section">
                    <button class="nav-parent" type="button" data-submenu="tools-menu">
                        <span><i data-lucide="wrench"></i> Herramientas</span>
                        <i data-lucide="chevron-down"></i>
                    </button>
                    <div class="nav-submenu" id="tools-menu">
                        <a href="tools.php" class="nav-sub-link <?= $isActive(['tools.php']) ?>">Panel de herramientas</a>
                        <a href="tools.php?action=sync" class="nav-sub-link">Sincronizar BD</a>
                        <a href="tools.php?action=log" class="nav-sub-link">Logs</a>
                    </div>
                </div>

                <div class="nav-section nav-bottom">
                    <a href="logout.php" class="nav-link logout-link">
                        <i data-lucide="log-out"></i> Cerrar Sesion
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
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </a>
        <a href="invoices.php?action=create" class="mobile-nav-link <?= $current === 'invoices.php' && ($_GET['action'] ?? '') === 'create' ? 'active' : '' ?>">
            <i data-lucide="shopping-cart"></i>
            <span>Punto Venta</span>
        </a>
        <a href="invoices.php" class="mobile-nav-link <?= in_array($current, ['invoices.php', 'recurring_invoices.php', 'quotes.php', 'credit_notes.php', 'payments.php'], true) && ($_GET['action'] ?? '') !== 'create' ? 'active' : '' ?>">
            <i data-lucide="receipt-text"></i>
            <span>Ventas</span>
        </a>
        <a href="products.php" class="mobile-nav-link <?= $isActive(['products.php']) ? 'active' : '' ?>">
            <i data-lucide="package"></i>
            <span>Productos</span>
        </a>
        <a href="clients.php" class="mobile-nav-link <?= $isActive(['clients.php']) ? 'active' : '' ?>">
            <i data-lucide="users"></i>
            <span>Clientes</span>
        </a>
        <a href="tools.php" class="mobile-nav-link <?= $isActive(['tools.php']) ? 'active' : '' ?>">
            <i data-lucide="wrench"></i>
            <span>Herramientas</span>
        </a>
    </nav>
</body>
</html>
