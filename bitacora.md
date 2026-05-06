# BITACORA DEL PROYECTO: FACTURADOR-PCCURICO

## 1. ESTADO ACTUAL: MODULOS COMPLETADOS Y FUNCIONALES

El nucleo del sistema operativo tipo "Invoice Ninja" se encuentra desplegado. Las siguientes caracteristicas estan integradas y operativas:

- **Arquitectura Base**: Patron MVC simplificado, conexion PDO Singleton y ruteo centralizado.
- **Asistente de Instalacion (Wizard)**: Flujo de 6 pasos que valida el entorno del servidor, conecta la base de datos, ejecuta migraciones de esquema completas, registra los datos de la empresa, configura credenciales de Transbank y crea el usuario administrador.
- **Modulo de Clientes**: Registro y listado de entidades.
- **Modulo de Productos**: Gestion de inventario, registro de SKU, precios netos e integracion con el calculo del IVA.
- **Modulo de Facturacion (Core B2B)**: Interfaz de creacion de documentos estructurada, permitiendo multiples lineas de detalle, seleccion de fechas de emision y vencimiento.
- **Modulo de Pagos y Cuentas por Cobrar**: 
    - Registro manual de abonos.
    - Actualizacion inteligente del estado del documento (Pendiente -> Pagado).
    - Panel de reportes financieros con desglose estricto de deudas vencidas y saldos pendientes.
- **Portal de Cliente Publico**: Vista web aislada para la visualizacion del documento por parte del cliente final, protegida por tokens de acceso.
- **Integracion Transbank Webpay Plus**: Conexion directa a la API REST (via cURL nativo) para inicializar pagos y procesar los retornos, inyectando el abono en el sistema de pagos automaticamente.
- **Motor de Recordatorios Automatisado (CRON)**: Script de linea de comandos diseñado para buscar facturas por vencer o atrasadas, preparado para despachar correos con el enlace de pago seguro de Transbank.
- **Motor de Impresion**: Plantillas generadas en HTML/CSS optimizadas tanto para formato A4 (corporativo) como para ticket de 80mm (impresora termica).

## 2. AUDITORIA DE SEGURIDAD IMPLEMENTADA

La seguridad y la integridad de los datos han sido la prioridad en la construccion del sistema:

- **Prevencion de Inyeccion SQL**: El 100% de las transacciones hacia la base de datos utilizan Consultas Preparadas (Prepared Statements) a traves de PDO.
- **Proteccion de Acceso a Documentos (Anti-IDOR)**: Las facturas expuestas publicamente al cliente no utilizan su ID secuencial (ej: id=15), sino un token criptografico robusto generado mediante `bin2hex(random_bytes(16))`, mitigando la enumeracion de facturas.
- **Criptografia de Credenciales**: Las contraseñas de los administradores utilizan el estandar de la industria `bcrypt` para su almacenamiento seguro.
- **Proteccion del Instalador**: Una vez completado, el sistema genera el archivo `installed.lock` impidiendo de manera absoluta la sobreescritura de configuraciones o caidas de base de datos no autorizadas.
- **Restriccion de Ejecucion Cron**: El motor automatizado de cobranzas restringe la ejecucion HTTP, requiriendo invocacion por interfaz de comandos (CLI) o un token privado, impidiendo abusos de CPU o SPAM.

## 3. PENDIENTES Y PROXIMOS PASOS ESTRATEGICOS

Para elevar el sistema al nivel de produccion final, se requiere abordar los siguientes puntos:

1. **Tokens CSRF**: Implementar la generacion y validacion estricta de tokens CSRF en todos los formularios POST del sistema para evitar falsificacion de peticiones entre sitios.
2. **Generador PDF (DomPDF)**: Integrar una libreria de renderizado PDF real para reemplazar la simulacion actual, de modo que el motor de correos adjunte un documento fisico.
3. **Motor SMTP Activo**: Reemplazar la funcion nativa de envio de correos por la integracion de PHPMailer o libreria afin, configurando credenciales SMTP para garantizar la entrega de recordatorios y evitar listas negras de spam.
4. **Sanitizacion de Entrada Estricta**: Reforzar todos los controladores para aplicar `filter_var` y escapes HTML a las entradas de usuario antes de ser procesadas por el motor.
5. **Edicion y Anulacion de Registros**: Extender las vistas y controladores actuales (que hoy permiten Creacion y Listado) para agregar flujos de edicion de clientes/productos y permitir la anulacion oficial de facturas (Notas de Credito genericas).
