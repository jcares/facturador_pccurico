<?php
require_once '../bootstrap/app.php';

$user = \Core\Auth::user();
$settings = \Core\Config::getAll();

$defaultEmailSubject = 'Recordatorio de pago: Documento #{invoice_number}';
$defaultEmailBody = "Hola {client_name},\n\nLe recordamos que el documento #{invoice_number} por {invoice_total} vence el {due_date}.\n\nPuede revisar el detalle en este enlace seguro:\n{public_url}\n\nSaludos,\n{business_name}";

$title = 'Plantillas & Recordatorios';
$contentFile = 'settings/email_templates.php';

include '../app/views/layout.php';
?>