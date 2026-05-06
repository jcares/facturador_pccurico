<?php
/**
 * FACTURADOR-PCCURICO
 * Cron Job Script for Payment Reminders
 * cPanel: php /home/user/public_html/cron.php
 */

require_once __DIR__ . '/../bootstrap/app.php';

use Core\Database;
use Core\Mailer;
use Core\PdfService;
use Modules\Invoices\Invoice;
use Modules\Invoices\RecurringInvoice;

$cronToken = getenv('CRON_TOKEN') ?: '';
if (php_sapi_name() !== 'cli' && ($cronToken === '' || !isset($_GET['token']) || !hash_equals($cronToken, $_GET['token']))) {
    header('HTTP/1.0 403 Forbidden');
    echo "Forbidden";
    exit;
}

echo "Starting Facturador Cron Job...\n";

try {
    $db = Database::getInstance();

    echo "Processing recurring invoices...\n";
    foreach (RecurringInvoice::dueTemplates() as $template) {
        $items = RecurringInvoice::items((int)$template['id']);
        if (empty($items)) {
            echo "-> Recurring template #{$template['id']} has no items, skipped.\n";
            continue;
        }

        $runDate = $template['next_run_date'];
        $dueDate = (new \DateTime($runDate))->modify('+' . (int)$template['due_days'] . ' days')->format('Y-m-d');
        $invoiceId = Invoice::create([
            'client_id' => (int)$template['client_id'],
            'number' => 'REC-' . date('Ymd-His') . '-' . (int)$template['id'],
            'subtotal' => (float)$template['subtotal'],
            'tax' => (float)$template['tax'],
            'total' => (float)$template['total'],
            'currency' => $template['currency'] ?? 'CLP',
            'exchange_rate' => $template['exchange_rate'] ?? 1,
            'due_date' => $dueDate,
            'token' => bin2hex(random_bytes(16)),
        ], $items);

        RecurringInvoice::recordGenerated(
            (int)$template['id'],
            (int)$invoiceId,
            $template['frequency'],
            $template['remaining_cycles'] !== null ? (int)$template['remaining_cycles'] : null,
            (int)$template['cycles_generated'],
            $runDate
        );

        echo "-> Generated invoice #{$invoiceId} from recurring template #{$template['id']}.\n";
    }

    $stmt = $db->query("
        SELECT i.*, COALESCE(c.business_name, c.name) as client_name, c.email
        FROM invoices i
        JOIN clients c ON i.client_id = c.id
        WHERE i.status != 'paid'
          AND i.status != 'canceled'
          AND i.due_date IS NOT NULL
          AND i.due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
    ");

    $invoices = $stmt->fetchAll();

    if (empty($invoices)) {
        echo "No pending invoices need reminders today.\n";
        echo "Job Completed Successfully.\n";
        exit;
    }

    $settingsRows = $db->query("SELECT * FROM settings")->fetchAll();
    $settings = [];
    foreach ($settingsRows as $row) {
        $settings[$row['key']] = $row['value'];
    }

    foreach ($invoices as $invoice) {
        $dueDate = new \DateTime($invoice['due_date']);
        $today = new \DateTime();
        $interval = $today->diff($dueDate);

        $status = ($dueDate < $today) ? 'OVERDUE' : 'UPCOMING';
        $days = $interval->days;

        echo "Processing Invoice #{$invoice['number']} ({$status} by {$days} days)\n";

        if (empty($invoice['email'])) {
            echo "-> No email address for client {$invoice['client_name']}\n";
            continue;
        }

        $publicBaseUrl = rtrim($settings['public_base_url'] ?? 'https://pccurico.cl', '/');
        $publicUrl = $publicBaseUrl . "/view.php?token=" . urlencode($invoice['token']);
        $to = $invoice['email'];
        $subject = "Recordatorio de Pago: Factura #{$invoice['number']}";
        $message = "Hola {$invoice['client_name']},\n\n";
        $message .= "Le recordamos que la factura #{$invoice['number']} por un total de $" . number_format($invoice['total'], 0, ',', '.') . " vence el {$invoice['due_date']}.\n\n";
        $message .= "Puede revisar el detalle y pagar en linea con Transbank Webpay Plus en este enlace seguro:\n\n{$publicUrl}\n\n";
        $message .= "Se adjunta una copia de la factura.\n\nSaludos,\n" . ($settings['biz_name'] ?? 'PC Curico SpA');

        $fullInvoice = Invoice::find((int)$invoice['id']);
        $pdfContent = PdfService::renderInvoice($fullInvoice ?: $invoice, $settings);
        $pdfFilename = PdfService::filename($invoice);

        if (Mailer::sendReminder($to, $subject, $message, $pdfContent, $pdfFilename)) {
            echo "-> Email sent to {$to} with Link: {$publicUrl} and PDF attachment: {$pdfFilename}\n";
        } else {
            echo "-> Email failed for {$to}\n";
        }
    }

    echo "Job Completed Successfully.\n";
} catch (\Exception $e) {
    echo "Error executing CRON job: " . $e->getMessage() . "\n";
}
