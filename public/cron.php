<?php
/**
 * FACTURADOR-PCCURICO
 * Cron Job Script for Payment Reminders
 * To be executed by cPanel cron: php /home/user/public_html/cron.php
 */

require_once __DIR__ . '/../bootstrap/app.php';

use Core\Database;

// Basic security check: ensure it's run via CLI or has a specific token if run via web
if (php_sapi_name() !== 'cli' && (!isset($_GET['token']) || $_GET['token'] !== 'secret_cron_token_123')) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden";
    exit;
}

echo "Starting Payment Reminders Job...\n";

try {
    $db = Database::getInstance();
    
    // Find invoices that are unpaid and due in 3 days or already overdue
    // We assume 'sent' or 'draft' that have a due_date
    $stmt = $db->query("
        SELECT i.*, c.name, c.email 
        FROM invoices i 
        JOIN clients c ON i.client_id = c.id 
        WHERE i.status != 'paid' AND i.status != 'canceled' 
        AND i.due_date IS NOT NULL 
        AND i.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ");
    
    $invoices = $stmt->fetchAll();
    
    if (empty($invoices)) {
        echo "No pending invoices need reminders today.\n";
    } else {
        foreach ($invoices as $invoice) {
            $dueDate = new \DateTime($invoice['due_date']);
            $today = new \DateTime();
            $interval = $today->diff($dueDate);
            
            $status = ($dueDate < $today) ? 'OVERDUE' : 'UPCOMING';
            $days = $interval->days;
            
            echo "Processing Invoice #{$invoice['number']} ({$status} by {$days} days)\n";
            
            if (!empty($invoice['email'])) {
                // Generar URL pública
                $publicUrl = "https://pccurico.cl/view.php?token=" . $invoice['token']; // Asumiendo URL de producción
                
                $to = $invoice['email'];
                $subject = "Recordatorio de Pago: Factura #{$invoice['number']}";
                $message = "Hola {$invoice['name']},\n\nLe recordamos que la factura #{$invoice['number']} por un total de $" . number_format($invoice['total'], 0, ',', '.') . " vence el {$invoice['due_date']}.\n\nPara revisar el detalle y pagar en línea con Transbank Webpay Plus, ingrese al siguiente enlace seguro:\n\n👉 {$publicUrl}\n\nAdemás, se ha adjuntado la copia en formato PDF de su factura.\n\nSaludos,\nPC Curicó SpA";
                
                // Simulación de Generación de PDF (Requiere DomPDF u otra librería en prod)
                $pdfContent = "Contenido PDF simulado de la factura #{$invoice['number']}";
                $pdfFilename = "factura_{$invoice['number']}.pdf";
                
                // mail($to, $subject, $message); // En producción usar PHPMailer con $mail->addStringAttachment($pdfContent, $pdfFilename);
                echo "-> Email simulated to {$to} with Link: {$publicUrl} and PDF attachment: {$pdfFilename}\n";
            } else {
                echo "-> No email address for client {$invoice['name']}\n";
            }
        }
    }
    
    echo "Job Completed Successfully.\n";

} catch (\Exception $e) {
    echo "Error executing CRON job: " . $e->getMessage() . "\n";
}
