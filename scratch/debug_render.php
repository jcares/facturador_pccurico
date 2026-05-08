<?php
require_once 'bootstrap/app.php';
use Core\Database;
use Core\Config;
use Modules\Invoices\Invoice;
use Core\VisualTemplateRenderer;

try {
    $db = Database::getInstance();
    $invoice = Invoice::find(1);
    $settings = Config::getAll();
    $stmt = $db->prepare("SELECT config_json FROM document_templates WHERE id = 1 LIMIT 1");
    $stmt->execute();
    $template = $stmt->fetch();
    
    $data = [
        'settings' => $settings,
        'client' => [
            'name' => $invoice['client_name'] ?? 'Cliente',
            'rut' => $invoice['client_rut'] ?? '',
            'address' => $invoice['client_address'] ?? ''
        ],
        'invoice' => [
            'number' => $invoice['number'] ?? '000',
            'created_at' => $invoice['created_at'] ?? date('Y-m-d'),
            'subtotal' => $invoice['subtotal'] ?? 0,
            'tax' => $invoice['tax'] ?? 0,
            'total' => $invoice['total'] ?? 0,
            'notes' => $invoice['notes'] ?? '',
            'public_url' => ($settings['app_url'] ?? '') . '/view.php?token=' . ($invoice['token'] ?? '')
        ],
        'items' => array_map(function($item) {
            return [
                'sku' => $item['product_sku'] ?? '',
                'name' => $item['product_name'] ?? 'Producto',
                'quantity' => $item['qty'] ?? 1,
                'price' => $item['price'] ?? 0,
                'subtotal' => $item['total'] ?? 0
            ];
        }, $invoice['items'] ?? [])
    ];

    echo VisualTemplateRenderer::render($template['config_json'], $data);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
