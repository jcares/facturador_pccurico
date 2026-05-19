<?php
// Settings Hub Page - rendered inside settings_layout.php (full-width, no sidebar)

$sections = [
    [
        'key' => 'company',
        'label' => 'Configuración Básica',
        'description' => 'Ajustes generales del sistema, nombre de la empresa, logo, datos de contacto.',
        'icon' => 'sliders-horizontal',
        'color' => 'var(--primary)',
        'href' => 'company.php',
    ],
    [
        'key' => 'user',
        'label' => 'Detalles de Usuario',
        'description' => 'Información del perfil de usuario actual, contraseña y preferencias.',
        'icon' => 'user-cog',
        'color' => '#7c3aed',
        'href' => '#',
    ],
    [
        'key' => 'localization',
        'label' => 'Localización',
        'description' => 'País, idioma, zona horaria y moneda por defecto.',
        'icon' => 'globe-2',
        'color' => '#f59e0b',
        'href' => 'localization.php',
    ],
    [
        'key' => 'payments',
        'label' => 'Configuración de Pagos',
        'description' => 'Configura Webpay Plus para aceptar pagos con tarjetas.',
        'icon' => 'credit-card',
        'color' => '#e0245e',
        'href' => 'settings.php?section=payments',
    ],
    [
        'key' => 'transbank',
        'label' => 'Transbank',
        'description' => 'Credenciales oficiales de Webpay, ambiente y personalización del botón.',
        'icon' => 'landmark',
        'color' => '#e0245e',
        'href' => 'settings.php?section=transbank',
    ],
    [
        'key' => 'taxes',
        'label' => 'Configuración de Impuestos',
        'description' => 'Tasas impositivas por defecto aplicables a productos y servicios.',
        'icon' => 'percent',
        'color' => '#10b981',
        'href' => 'taxes.php',
    ],
    [
        'key' => 'product',
        'label' => 'Configuración del Producto',
        'description' => 'Unidades de medida por defecto y ajustes de inventario.',
        'icon' => 'package',
        'color' => '#3b82f6',
        'href' => 'product_settings.php',
    ],
    [
        'key' => 'email',
        'label' => 'Configuración del Correo',
        'description' => 'Servidor SMTP, credenciales y remitente para correos automáticos.',
        'icon' => 'mail',
        'color' => '#22d3ee',
        'href' => 'email_settings.php',
    ],
    [
        'key' => 'email-templates',
        'label' => 'Plantillas & Recordatorios',
        'description' => 'Personaliza los correos automáticos, asuntos y cuerpo de mensajes.',
        'icon' => 'send',
        'color' => '#a78bfa',
        'href' => 'email_templates.php',
    ],
    [
        'key' => 'invoice-design',
        'label' => 'Diseño de Factura',
        'description' => 'Editor visual de plantillas de facturas y documentos.',
        'icon' => 'palette',
        'color' => '#ec4899',
        'href' => 'templates.php',
    ],
    [
        'key' => 'client-portal',
        'label' => 'Portal de Cliente',
        'description' => 'Configuración del portal público para que clientes vean sus facturas.',
        'icon' => 'monitor',
        'color' => '#f97316',
        'href' => 'client_portal.php',
    ],
    [
        'key' => 'system-logs',
        'label' => 'Registros del Sistema',
        'description' => 'Revisa errores, diagnósticos y logs de actividad del sistema.',
        'icon' => 'scroll',
        'color' => '#ef4444',
        'href' => 'tools.php?action=log',
    ],
    [
        'key' => 'tools',
        'label' => 'Herramientas',
        'description' => 'Diagnóstico, limpieza, permisos, sincronización y exportación de datos.',
        'icon' => 'tool',
        'color' => '#14b8a6',
        'href' => 'tools.php',
    ],
];

$current = basename($_SERVER['PHP_SELF']);
?>

<!-- Settings Hub Header -->
<div style="margin-bottom: 24px;">
    <h2 style="font-weight: 800; margin: 0 0 4px;">Configuración</h2>
    <p style="color: var(--text-muted); margin: 0;">Administra todas las opciones de configuración del sistema desde un solo lugar.</p>
</div>

<!-- Settings Hub Cards -->
<div class="settings-hub-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; margin-bottom: 28px;">
    <?php foreach ($sections as $sec): ?>
        <a href="<?= htmlspecialchars($sec['href']) ?>" class="settings-card-link" style="text-decoration: none;">
            <div class="glass-card settings-card" style="padding: 18px; cursor: pointer; border: 1px solid var(--glass-border); transition: all 0.2s ease; position: relative; overflow: hidden;"
                 onmouseover="this.style.borderColor='<?= $sec['color'] ?>'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(0,0,0,0.2)'"
                 onmouseout="this.style.borderColor='var(--glass-border)'; this.style.transform='none'; this.style.boxShadow='var(--shadow)'">
                <div style="position: absolute; top: 0; right: 0; width: 36px; height: 36px; border-radius: 0 12px 0 12px; opacity: 0.12; background: <?= $sec['color'] ?>;"></div>
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 10px; background: <?= $sec['color'] ?>15; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i data-lucide="<?= htmlspecialchars($sec['icon']) ?>" style="width: 18px; height: 18px; color: <?= $sec['color'] ?>;"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <h3 style="margin: 0 0 3px; font-size: 0.88rem; color: var(--text-main);"><?= htmlspecialchars($sec['label']) ?></h3>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.78rem; line-height: 1.4; opacity: 0.85;"><?= htmlspecialchars($sec['description']) ?></p>
                    </div>
                    <i data-lucide="arrow-right" style="width: 14px; height: 14px; color: var(--text-muted); flex-shrink: 0; margin-top: 4px; opacity: 0.5;"></i>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div class="glass-card" style="padding: 22px;">
    <h3 class="section-heading" style="margin-bottom: 16px;">Acciones rápidas</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px;">
        <a href="settings.php?section=payments" class="quick-action-btn"
           style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.85rem; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); transition: all 0.2s;"
           onmouseover="this.style.borderColor='var(--primary)'; this.style.background='rgba(34,211,238,0.06)'" onmouseout="this.style.borderColor='var(--glass-border)'; this.style.background='rgba(255,255,255,0.03)'">
            <i data-lucide="credit-card" style="width: 16px; height: 16px; color: var(--primary);"></i>
            <span>Configurar Pagos</span>
        </a>
        <a href="settings.php?section=email" class="quick-action-btn"
           style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.85rem; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); transition: all 0.2s;"
           onmouseover="this.style.borderColor='var(--primary)'; this.style.background='rgba(34,211,238,0.06)'" onmouseout="this.style.borderColor='var(--glass-border)'; this.style.background='rgba(255,255,255,0.03)'">
            <i data-lucide="send" style="width: 16px; height: 16px; color: var(--primary);"></i>
            <span>Probar Correo</span>
        </a>
        <a href="tools.php?action=export" class="quick-action-btn"
           style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.85rem; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); transition: all 0.2s;"
           onmouseover="this.style.borderColor='var(--primary)'; this.style.background='rgba(34,211,238,0.06)'" onmouseout="this.style.borderColor='var(--glass-border)'; this.style.background='rgba(255,255,255,0.03)'">
            <i data-lucide="download" style="width: 16px; height: 16px; color: var(--primary);"></i>
            <span>Exportar Datos</span>
        </a>
        <a href="tools.php?action=log" class="quick-action-btn"
           style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.85rem; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); transition: all 0.2s;"
           onmouseover="this.style.borderColor='var(--primary)'; this.style.background='rgba(34,211,238,0.06)'" onmouseout="this.style.borderColor='var(--glass-border)'; this.style.background='rgba(255,255,255,0.03)'">
            <i data-lucide="file-warning" style="width: 16px; height: 16px; color: var(--primary);"></i>
            <span>Ver Logs</span>
        </a>
    </div>
</div>