# Bitacora del Proyecto Facturador PCCurico

Este archivo debe actualizarse en cada cambio funcional, de seguridad, base de datos o despliegue. Sirve para retomar el proyecto sin perder contexto y mantener la linea tecnica del sistema.

## Objetivo del Sistema

Construir un facturador/POS inspirado en Invoice Ninja: clientes, productos, categorias, documentos, pagos, reportes, plantillas y enlaces publicos seguros de cobranza, con extras propios como monedas CLP/USD/UF, conversion a CLP, Webpay, plantillas visuales y herramientas de mantenimiento.

## Reglas de Trabajo

- La raiz local de trabajo es `C:\Users\JCares\Documents\facturador-pccurico`.
- Produccion se despliega copiando archivos al FTP montado en `Y:\`.
- WAMP (`C:\wamp64\bin\php`) se usa solo para validacion local de sintaxis.
- Antes de desplegar, respaldar archivos modificados de `Y:\` en `C:\tmp\facturador-prod-backup-*`.
- Ejecutar `Herramientas > Sincronizar BD` despues de cambios de esquema.
- No exponer archivos de diagnostico, configuracion, logs, backups ni instalacion sin login.

## Contrato Actual de Base de Datos

- `users`: usuarios administradores.
- `clients`: clientes con `business_name`, `contact_name`, `rut`, `email`, `phone`, `address`; `name` queda como compatibilidad legado.
- `categories`: categorias de productos.
- `products`: catalogo con `sku` unico, precio base, `currency`, categoria, IVA y stock.
- `invoices`: documentos con cliente, numero unico, estado, totales, moneda de cobro, tipo de cambio, vencimiento y token publico.
- `invoice_items`: lineas del documento, producto, cantidad, precio cobrado, precio/moneda original y tasa usada.
- `payments`: pagos aplicados a facturas; el monto se interpreta en la moneda de la factura.
- `settings`: configuracion de negocio, correo, Webpay y valores publicos.
- `exchange_rates`: cache de tasas CLP para USD y UF.
- `document_templates`: configuracion visual de documentos.
- `credit_notes`: anulaciones/notas asociadas a facturas.
- `recurring_invoices`: plantillas recurrentes con cliente, frecuencia, fechas, ciclos, totales y ultimo documento generado.
- `recurring_invoice_items`: lineas copiadas de la venta para generar facturas recurrentes futuras.

## Cambios Recientes

### 2026-05-08

- Se corrigio el fallo critico donde el wizard de instalacion nunca se iniciaba en una instalacion nueva: `index.php` iba directo al dashboard, `login.php` cargaba bootstrap con credenciales invalidas y provocaba un error fatal.
- Se agrego guard de instalacion centralizado en `bootstrap/app.php`: si `storage/installed.lock` no existe y el script actual no es `install.php`, redirige automaticamente al wizard. Esto protege los 26+ entry points publicos sin parchear cada uno.
- Se agregaron guards adicionales en `index.php` y `login.php` como defensa en profundidad (se ejecutan antes del bootstrap).
- Se eliminaron credenciales hardcodeadas de `config/database.php`; los defaults ahora son strings vacios para evitar errores de conexion fatales en servidores nuevos sin `.env`.
- Se agrego creacion automatica de `storage/`, `storage/logs/`, `storage/cache/` y `storage/backups/` en `install.php` para instalaciones limpias donde la carpeta no existe.
- Se refactorizó completamente `style.css`: eliminadas reglas duplicadas (`.nav-parent`, `.nav-submenu`, `.nav-sub-link`, `.nav-link`, `.card-lg` definidos 2 veces con estrategias opuestas), organizado en secciones lógicas, reducido abuso de `!important`, unificado submenu a estrategia `display:flex` toggle.
- Se corrigió tag `</script>` faltante en `layout.php` que causaba errores de parsing JS.
- Se añadieron gráficos interactivos al Dashboard utilizando Chart.js (respetando la política Zero-CDN, incluyendo la librería de forma local). Se implementó un gráfico de líneas para las ventas de los últimos 6 meses y un gráfico tipo "doughnut" para el estado de las facturas.
- Se restauró el enlace al mantenedor de Categorías en el menú lateral principal, debajo de Productos.
- Se refactorizó el módulo de configuración de correos electrónicos (`email_settings.php` y su vista), añadiendo la funcionalidad de "Enviar Correo de Prueba" para validar la configuración SMTP.

### 2026-05-06

- Se implementó completamente el módulo de Tareas: tabla `tasks` en base de datos, modelo Task.php, controlador TaskController.php, vistas index/create/edit, archivo público tasks.php y enlace activo en el menú lateral.
- Se implementó completamente el módulo de Órdenes de Compra: tablas `purchase_orders` y `purchase_order_items` en base de datos, modelo PurchaseOrder.php, controlador PurchaseOrderController.php, vistas index/create/edit/show, archivo público purchase_orders.php y enlace activo en el menú lateral. Incluye gestión de proveedores, artículos dinámicos con cálculo automático de totales e IVA.
- Se implementó completamente el módulo de Gastos: tabla `expenses` en base de datos, modelo Expense.php, controlador ExpenseController.php, vistas index/create/edit, archivo público expenses.php y enlace activo en el menú lateral. Incluye categorías, métodos de pago, deducibilidad fiscal, subida de comprobantes y estadísticas mensuales.
- Se actualizó el menú lateral para marcar activos los enlaces de Tareas, Órdenes de Compra y Gastos cuando se accede a sus respectivas páginas.
- Se agregó validación de seguridad y sanitización de datos en todos los nuevos controladores.
- Se integraron todos los módulos con el sistema de autenticación y layout existente.
- Se actualizó run_migrations.php con las nuevas tablas para tasks, purchase_orders, purchase_order_items y expenses.
- Se corrigió el menú de configuración: eliminó scripts JavaScript duplicados que causaban conflictos, agregó estilos CSS faltantes para submenús (.nav-submenu, .nav-sub-link, .nav-parent), y unificó la lógica de apertura/cierre de submenús con cierre automático de otros menús abiertos.

- Se agrego SKU automatico para productos cuando el campo queda vacio.
- Se ajusto el modulo de categorias para manejar categorias padre e hijas; los productos se asignan a categorias hijas.
- Se simplifico el formulario de categorias: si `Categoria padre` queda vacia es categoria superior; si se selecciona una, queda como hija.
- Se agrego tipo de precio en productos: por unidad o por metro, con cantidades/stock decimales para ventas por metro.
- Se reorganizo Configuracion en secciones tipo menu: basica y avanzada, reutilizando paginas existentes para plantillas, portal, herramientas y registros.
- Se agrego editor de asunto/cuerpo para correos de recordatorio, con PDF adjunto opcional y boton Webpay opcional.
- Se agrego bloque opcional de pago Webpay en el editor visual de plantillas y marca Transbank/Webpay en botones de pago.
- Se refactorizo por completo `invoices.php?action=create`: una sola fuente de productos en JSON, primera linea renderizada por PHP, filas dinamicas por event delegation, sin `select` oculto duplicado ni handlers inline fragiles.
- Se agregaron funciones globales de compatibilidad en POS (`updatePrice`, `calculateRow`, `refreshAllPrices`, `addRow`) para evitar errores si el navegador/OPcache conserva HTML antiguo con handlers inline.
- Se alineo el menu movil con las entradas principales del desktop: Dashboard, Punto Venta, Ventas, Productos, Clientes y Herramientas.
- Se agregaron mensajes explicitos para errores de ventas (`invalid_client`, `no_items`, `invalid_invoice`, `cancel_failed`) en los toast del layout.
- Se endurecio `run_migrations.php` para consolidar categorias duplicadas, reasignar productos, crear indice unico en `categories.name` y sembrar categorias base sin depender de `INSERT IGNORE`.
- El instalador tambien siembra categorias base con verificacion por nombre para mantener el mismo comportamiento idempotente.
- Se ajusto el layout desktop para que el contenido de Punto de Venta no invada ni bloquee el menu lateral: sidebar sin shrink, con z-index y scroll propio; main-content con min-width 0 y overflow-x controlado.
- Se forzo charset UTF-8 desde `.htaccess` para HTML/CSS/JS/JSON y se versiono `lucide.min.js` para romper cache del navegador.
- Se dejo una primera linea de venta renderizada por PHP en `invoices.php?action=create` para que el formulario no dependa del JS inicial; el JS solo agrega/calcula lineas adicionales.
- Se agrego vista de detalle para `invoices.php?id=*`, con todas las lineas/productos del documento, y el historial ahora enlaza al detalle.
- Se normalizaron a ASCII los textos visibles de `invoices.php?action=create` y mensajes toast del layout para evitar caracteres erroneos por interpretacion de charset en produccion.
- Se corrigio `+ Anadir Linea` en `invoices.php?action=create`.
- Se corrigio la tabla de productos en ventas para que no quede oculta en moviles.
- Se agrego opcion de crear una venta como factura recurrente, con frecuencia, primera recurrencia, dias de vencimiento y ciclos.
- Se agregaron tablas y cron de facturas recurrentes inspirado en Invoice Ninja: la recurrencia queda como plantilla y el cron genera nuevas facturas desde sus lineas.
- Se creo copia local de pruebas en `C:\wamp64\www\facturador-pccurico-codex`.
- Se creo/uso BD local `pccurico_facturador` con usuario `pccurico_facturador` para pruebas en WAMP.
- Se corrigio `.htaccess` para permitir ejecucion en subdirectorios locales sin romper produccion en raiz.
- Se agregaron menus y pantallas base para Recurrentes, Cotizaciones, Notas de credito y Portal clientes.
- Se desplegaron a `Y:\` los cambios de facturas recurrentes, menu organizado, pantallas base y correccion de ventas.
- Se corrigieron labels sin asociacion en `invoices.php?action=create` y se agregaron etiquetas accesibles a lineas dinamicas.
- Se reemplazo optional chaining del validador JS de ventas por JS compatible para evitar avisos de funcion no disponible en navegadores mas estrictos.
- `products.php` muestra precio definido y equivalente CLP.
- Facturas y pagos respetan moneda CLP/USD/UF.
- Se agrego acceso a Categorias en menu y submenus cerrados por defecto.
- Herramientas se integraron al layout (`tools.php?action=*`) y dejaron de mostrar paginas crudas.
- Se endurecieron migraciones para no duplicar datos y crear columnas faltantes.
- Se creo esta bitacora para continuidad del proyecto.
- Se protegieron diagnosticos publicos (`info.php`, `db_test.php`, `session_test.php`) con login.
- Se endurecio `.htaccess` para bloquear acceso directo a codigo, configuracion, storage, logs, backups y artefactos.
- Se elimino el token CRON por defecto; por web solo funciona si `CRON_TOKEN` esta configurado.
- Se normalizaron consultas de clientes a `COALESCE(business_name, name)`.
- Se impidio eliminar clientes con documentos asociados.
- Se agrego moneda/tipo de cambio a pagos en migraciones y registro.
- Webpay cobra en CLP usando equivalente de facturas USD/UF y registra el pago en la moneda de la factura.
- Se actualizo instalador para nuevas columnas de moneda/tipo de cambio.
- Se reorganizo el menu principal a una lista plana en el orden: Inicio, Clientes, Productos, Facturas (con submenus: Crear Factura, Ver Facturas, Notas de Credito), Facturas Recurrentes, Pagos, Cotizaciones, Tareas, Ordenes de compra, Gastos, Informes, Configuracion (con submenus para todas las secciones de ajustes).
- Se corrigieron textos a español chileno: titulo por defecto a "Resumen General", "Ordenes de compra" a "Órdenes de compra", "Cerrar Sesion" a "Cerrar Sesión".
- Se desplegaron a `Y:\` los cambios del menu reorganizado y correcciones de español chileno.
- Se mejoraron los textos de ayuda en formularios de configuración, agregando explicaciones detalladas y placeholders para guiar al usuario, elevando el nivel profesional del sistema.
- Se unificó el menú móvil con el desktop: ahora en móvil se muestra el mismo sidebar lateral con toggle hamburguesa, eliminando la barra inferior limitada y asegurando contenido igual en ambas versiones; se agregó overlay para mejor UX.
- Se restauró la barra de navegación inferior en móvil para optimizar el uso del ancho completo del teléfono y no perder espacio, actualizando los enlaces para mayor coherencia con el menú desktop.

## Pendientes Recomendados

- Implementar numeracion configurable tipo Invoice Ninja por serie/documento.
- Agregar presupuestos/cotizaciones y conversion a factura.
- Agregar impuestos por linea y descuentos.
- Agregar roles/permisos de usuario.
- Agregar auditoria de cambios por usuario.
- Agregar pruebas automatizadas de rutas publicas y formularios criticos.

## Roadmap para acercarse a Invoice Ninja

### Prioridad Alta

- Cotizaciones/presupuestos con estados `draft`, `sent`, `approved`, `converted`, PDF, envio por correo y conversion directa a factura.
- Numeracion configurable para facturas, recurrentes, cotizaciones, pagos y notas de credito, con prefijos y contador por documento.
- Notas de credito completas: lineas, PDF, estado, credito disponible y aplicacion como pago a facturas.
- Portal de cliente mejorado: historial de facturas, pagos, cotizaciones, recurrentes, creditos y descarga de documentos desde enlace seguro.
- Impuestos por linea, impuestos globales, descuentos por linea/globales e impuestos inclusivos/exclusivos.
- Facturas recurrentes fase 2: pausar/reanudar, editar plantilla, historial de facturas generadas, actualizar precios desde catalogo y aumento porcentual.

### Prioridad Media

- Contactos multiples por cliente y seleccion de destinatarios por factura/cotizacion.
- Emails transaccionales: enviar factura/cotizacion, reenviar, registrar historial de emails y programar envio.
- Pagos avanzados: pagos parciales, pagos no aplicados, sobrepago como credito, metodos configurables y recibo PDF.
- Adjuntos/documentos en clientes, productos, facturas, cotizaciones y creditos.
- Panel lateral/detalle de documento con resumen, historial, actividad y pagos asociados.
- Reportes exportables CSV/PDF para ventas, productos, pagos, clientes, recurrentes, impuestos y cuentas por cobrar.

### Prioridad Baja

- Proyectos, tareas y control de tiempo para facturar servicios por horas.
- Gastos/proveedores y gastos facturables a clientes.
- Grupos de clientes con configuraciones propias de moneda, impuestos, plantillas y emails.
- Campos personalizados para clientes, productos y documentos.
- Webhooks/eventos internos para cambios de estado, pagos, aprobaciones y vencimientos.
- API interna autenticada para futuras integraciones.
