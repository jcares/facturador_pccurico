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

function renderEmailTemplate(string $template, array $vars): string
{
    foreach ($vars as $key => $value) {
        $template = str_replace('{' . $key . '}', (string)$value, $template);
    }

    return $template;
}

function renderWebpayEmailButton(string $publicUrl, array $settings): string
{
    $safeUrl = htmlspecialchars($publicUrl, ENT_QUOTES, 'UTF-8');
    $buttonText = htmlspecialchars($settings['webpay_button_text'] ?? 'Pagar con Webpay Plus');
    $baseUrl = rtrim($settings['public_base_url'] ?? '', '/');
    
    $buttonImage = !empty($settings['webpay_button_image'])
        ? $baseUrl . '/uploads/' . htmlspecialchars($settings['webpay_button_image'])
        : $baseUrl . '/assets/img/transbank-webpay.svg';

    return '
        <div style="margin: 24px 0; padding: 20px; border: 1px solid #f3c2d2; background: #fff7fb; border-radius: 10px; text-align: center;">
            <img src="' . $buttonImage . '" alt="Transbank Webpay Plus" style="max-height: 50px; background: #fff; border-radius: 8px; padding: 4px 10px; margin-bottom: 12px;">
            <br>
            <a href="' . $safeUrl . '" style="display: inline-block; background: #e0245e; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 12px 24px; font-weight: 800; font-family: sans-serif;">
                ' . $buttonText . '
            </a>
        </div>
    ';
}

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
        $money = (($invoice['currency'] ?? 'CLP') === 'CLP' ? '$' : ($invoice['currency'] ?? 'CLP') . ' ') . number_format((float)$invoice['total'], ($invoice['currency'] ?? 'CLP') === 'CLP' ? 0 : 2, ',', '.');
        $vars = [
            'client_name' => $invoice['client_name'],
            'invoice_number' => $invoice['number'],
            'invoice_total' => $money,
            'due_date' => $invoice['due_date'],
            'public_url' => $publicUrl,
            'business_name' => $settings['biz_name'] ?? 'PC Curico SpA',
        ];

        $subjectTemplate = $settings['email_subject_template'] ?? 'Recordatorio de pago: Documento #{invoice_number}';
        $bodyTemplate = $settings['email_body_template'] ?? "Hola {client_name},\n\nLe recordamos que el documento #{invoice_number} por {invoice_total} vence el {due_date}.\n\nPuede revisar el detalle en este enlace seguro:\n{public_url}\n\nSaludos,\n{business_name}";
        $subject = renderEmailTemplate($subjectTemplate, $vars);
        $plainMessage = renderEmailTemplate($bodyTemplate, $vars);
        $message = nl2br(htmlspecialchars($plainMessage, ENT_QUOTES, 'UTF-8'));

        if (($settings['email_include_webpay_button'] ?? '1') === '1') {
            $message .= renderWebpayEmailButton($publicUrl, $settings);
        }

        $pdfContent = null;
        $pdfFilename = null;
        if (($settings['email_attach_pdf'] ?? '0') === '1') {
            $fullInvoice = Invoice::find((int)$invoice['id']);
            $pdfContent = PdfService::renderInvoice($fullInvoice ?: $invoice, $settings);
            $pdfFilename = PdfService::filename($invoice);
        }

        if (Mailer::sendReminder($to, $subject, $message, $pdfContent, $pdfFilename)) {
            echo "-> Email sent to {$to} with Link: {$publicUrl}" . ($pdfFilename ? " and PDF attachment: {$pdfFilename}" : " without PDF attachment") . "\n";
        } else {
            echo "-> Email failed for {$to}\n";
        }
    }

    echo "Job Completed Successfully.\n";
} catch (\Exception $e) {
    echo "Error executing CRON job: " . $e->getMessage() . "\n";
}
