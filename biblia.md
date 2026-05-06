FACTURADOR-PCCURICO (cPanel Edition)
🧠 1. Decisión Arquitectónica (CRÍTICA)
Opción A (recomendada): Laravel

✔ Seguridad integrada
✔ Routing limpio
✔ ORM (Eloquent)
✔ Escalable sin dolor

Opción B: PHP puro (si quieres ultra simple)

✔ Menos dependencias
❌ Más riesgo si no estructuras bien

👉 Si vienes de proyectos grandes: usa Laravel
👉 Si quieres algo rápido tipo “tool personal”: PHP modular

🧱 2. Estructura para cPanel
public_html/
│
├── facturador/
│   ├── app/
│   ├── core/
│   ├── modules/
│   ├── storage/
│   ├── templates/
│   ├── routes/
│   ├── config/
│   └── bootstrap/
│
├── api/
├── assets/
└── index.php

🔐 Importante:

.env fuera de public_html si cPanel lo permite
proteger /storage y /config
⚙️ 3. Wizard Inicial (CLAVE — tu “arma secreta”)

Inspirado en Invoice Ninja, pero más inteligente.

Flujo:
Paso 1: Bienvenida
Verifica:
PHP version
extensiones (pdo, mbstring, openssl)
permisos carpetas
Paso 2: Base de datos

Formulario:

Host
DB Name
User
Password

Acción:

try {
  new PDO(...);
} catch (Exception $e) { ... }
Paso 3: Migraciones
Crear tablas automáticamente
Insertar datos base
Paso 4: Configuración del negocio
Nombre empresa
RUT
Giro
Dirección
Teléfono
Logo
Paso 5: Usuario administrador
Email
Password (bcrypt)
Sesión inicial
Paso 6: Configuración fiscal Chile
IVA: 19%
Tipo documentos:
Factura
Boleta
Nota de crédito
Paso 7: Impresión
Selección:
PDF
Térmica
Plantilla default
Paso 8: Finalización
Genera:
.env
config/app.php
Bloquea wizard (installed.lock)
🗃️ 4. Base de Datos (MySQL)
Tablas núcleo
clients
id
name
rut
email
phone
address
created_at
products
id
name
sku
price
tax_rate
stock
invoices
id
client_id
number
status
subtotal
tax
total
created_at
invoice_items
id
invoice_id
product_id
qty
price
total
payments
id
invoice_id
amount
method
created_at
settings
key
value
🧮 5. Motor de Cálculo (aislado)

Aquí está el corazón:

class InvoiceCalculator {

  public static function calculate($items, $taxRate = 0.19) {
    $subtotal = 0;

    foreach ($items as $item) {
      $subtotal += $item['qty'] * $item['price'];
    }

    $tax = $subtotal * $taxRate;
    $total = $subtotal + $tax;

    return compact('subtotal', 'tax', 'total');
  }
}
🖨️ 6. Sistema de Templates
Render HTML → PDF
$html = view('invoice_template', $data);
$pdf = generatePDF($html);

Plantillas:

templates/
├── invoice_a4.php
├── ticket_80mm.php
🔌 7. Print Bridge (modo cPanel realista)

Como cPanel no accede directo a impresora:

Solución híbrida:
Backend genera JSON
Cliente (navegador o app local) imprime
fetch('/api/print')
  .then(data => sendToPrinter(data))
🔐 8. Seguridad mínima obligatoria
CSRF tokens
Password hash (password_hash)
Sanitización inputs
Prepared statements (PDO)
🚀 9. API Interna
POST /api/invoices/create
GET /api/invoices/list
POST /api/payments/add
🧩 10. Módulos
modules/
├── Clients/
├── Products/
├── Invoices/
├── Payments/
├── Reports/

Cada módulo:

Controller
Model
Views
🎯 11. Diferencias clave con Invoice Ninja
❌ Sin multiempresa
❌ Sin sistema pesado de permisos
✔ Ultra optimizado para 1 negocio
✔ Instalación en 2 minutos (wizard)
✔ Compatible con hosting barato
🧪 12. Build para producción
/build/
├── clean.sql
├── install.php
├── app.zip
🧠 13. Prompt para tu agente (versión PRO)

Úsalo tal cual:

Reconstruye un sistema llamado “facturador-pccurico” basado en el comportamiento funcional de Invoice Ninja pero sin reutilizar código.
Debe estar desarrollado en PHP y MySQL, optimizado para hosting cPanel.
Implementa un wizard de instalación completo con validación de entorno, conexión a base de datos, migraciones, configuración de negocio, usuario admin y settings fiscales de Chile.
El sistema debe incluir módulos de clientes, productos, facturación, pagos y reportes.
El motor de cálculo debe estar desacoplado.
Debe existir sistema de templates HTML para PDF y tickets.
El código debe ser modular, limpio, sin duplicaciones, seguro (PDO, CSRF, hashing) y preparado para expansión futura.

⏱️ 14. Recordatorios de Pago (NUEVO)
El sistema debe incluir una funcionalidad para enviar recordatorios de pago automáticos a los clientes.
- Las facturas deben tener fecha de vencimiento (`due_date`).
- Debe existir un script (`cron.php`) ejecutable vía tareas programadas en cPanel.
- Debe enviar correos notificando vencimientos próximos o deudas atrasadas.