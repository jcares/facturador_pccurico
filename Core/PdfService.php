<?php
namespace Core;

class PdfService
{
    public static function renderInvoice(array $invoice, array $settings = [])
    {
        $html = self::invoiceHtml($invoice, $settings);

        // Try Dompdf first
        if (class_exists('\Dompdf\Dompdf')) {
            Logger::info('Usando Dompdf para generar PDF.');
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }

        // Try TCPDF as second option
        $tcpdfPath = ROOT_PATH . '/TCPDF/tcpdf.php';
        if (file_exists($tcpdfPath)) {
            Logger::info('Usando TCPDF para generar PDF.');
            if (!class_exists('\TCPDF')) {
                require_once($tcpdfPath);
            }
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('Facturador');
            $pdf->SetAuthor($settings['biz_name'] ?? 'Facturador');
            $pdf->SetTitle('Factura ' . ($invoice['number'] ?? ''));
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();
            $pdf->writeHTML($html, true, false, true, false, '');
            return $pdf->Output('', 'S');
        }

        Logger::error('Ni DomPDF ni TCPDF estan instalados; se genera adjunto HTML como fallback.');
        return $html;
    }

    public static function hasPdfGenerator()
    {
        return class_exists('\Dompdf\Dompdf') || file_exists(ROOT_PATH . '/TCPDF/tcpdf.php');
    }

    public static function filename(array $invoice)
    {
        $number = preg_replace('/[^A-Za-z0-9_-]/', '_', $invoice['number'] ?? 'documento');
        $extension = self::hasPdfGenerator() ? 'pdf' : 'html';
        return "factura_{$number}.{$extension}";
    }

    private static function invoiceHtml(array $invoice, array $settings)
    {
        $db = \Core\Database::getInstance();
        // Use the default template for invoices
        $stmt = $db->prepare("SELECT config_json FROM document_templates WHERE type = 'invoice' AND is_default = 1 LIMIT 1");
        $stmt->execute();
        $template = $stmt->fetch();

        if (!$template) {
            // Fallback to legacy hardcoded template if no dynamic template is found
            return self::legacyInvoiceHtml($invoice, $settings);
        }

        $data = [
            'settings' => $settings,
            'client' => [
                'name' => $invoice['client_name'] ?? 'Cliente',
                'rut' => $invoice['client_rut'] ?? '',
                'address' => $invoice['client_address'] ?? ''
            ],
            'invoice' => array_merge($invoice, [
                'public_url' => ($settings['app_url'] ?? '') . '/view.php?token=' . ($invoice['token'] ?? '')
            ]),
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

        require_once ROOT_PATH . '/Core/VisualTemplateRenderer.php';
        $renderedBody = \Core\VisualTemplateRenderer::render($template['config_json'], $data);

        // Fix image paths for PDF engines (convert relative to absolute)
        $renderedBody = str_replace('src="uploads/', 'src="' . ROOT_PATH . '/public/uploads/', $renderedBody);
        $renderedBody = str_replace('src="assets/', 'src="' . ROOT_PATH . '/public/assets/', $renderedBody);

        // Wrap in a full HTML document for PDF engines
        ob_start();
        ?>
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: helvetica, Arial, sans-serif; color: #333; font-size: 11px; margin: 0; padding: 0; }
                .visual-document { border: none !important; max-width: 100% !important; padding: 0 !important; }
            </style>
        </head>
        <body>
            <?= $renderedBody ?>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private static function legacyInvoiceHtml(array $invoice, array $settings)
    {
        $items = $invoice['items'] ?? [];
        ob_start();
        ?>
        <!doctype html>
        <html lang="es">
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; font-size: 12px; }
                h1 { font-size: 22px; margin-bottom: 4px; }
                table { width: 100%; border-collapse: collapse; margin-top: 18px; }
                th, td { border-bottom: 1px solid #e5e7eb; padding: 8px; text-align: left; }
                th { background: #f3f4f6; }
                .right { text-align: right; }
                .total { font-size: 16px; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1><?= htmlspecialchars($settings['biz_name'] ?? 'FACTURADOR-PCCURICO') ?></h1>
            <p><?= htmlspecialchars($settings['biz_rut'] ?? '') ?> <?= htmlspecialchars($settings['biz_address'] ?? '') ?></p>
            <h2>Documento <?= htmlspecialchars($invoice['number'] ?? '') ?></h2>
            <p>Cliente: <?= htmlspecialchars($invoice['client_name'] ?? '') ?> | RUT: <?= htmlspecialchars($invoice['client_rut'] ?? '') ?></p>
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="right">Cantidad</th>
                        <th class="right">Precio</th>
                        <th class="right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name'] ?? '') ?></td>
                            <td class="right"><?= number_format((float)($item['qty'] ?? 0), floor((float)($item['qty'] ?? 0)) == (float)($item['qty'] ?? 0) ? 0 : 2, ',', '.') ?></td>
                            <td class="right">$<?= number_format((float)($item['price'] ?? 0), 0, ',', '.') ?></td>
                            <td class="right">$<?= number_format((float)($item['total'] ?? 0), 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="right">Subtotal: $<?= number_format((float)($invoice['subtotal'] ?? 0), 0, ',', '.') ?></p>
            <p class="right">IVA: $<?= number_format((float)($invoice['tax'] ?? 0), 0, ',', '.') ?></p>
            <p class="right total">Total: $<?= number_format((float)($invoice['total'] ?? 0), 0, ',', '.') ?></p>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
