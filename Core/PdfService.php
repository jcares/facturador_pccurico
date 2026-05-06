<?php
namespace Core;

class PdfService
{
    public static function renderInvoice(array $invoice, array $settings = [])
    {
        $html = self::invoiceHtml($invoice, $settings);

        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return $dompdf->output();
        }

        Logger::error('DomPDF no esta instalado; se genera adjunto HTML como fallback.');
        return $html;
    }

    public static function filename(array $invoice)
    {
        $number = preg_replace('/[^A-Za-z0-9_-]/', '_', $invoice['number'] ?? 'documento');
        return "factura_{$number}.pdf";
    }

    private static function invoiceHtml(array $invoice, array $settings)
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
                            <td class="right"><?= (int)($item['qty'] ?? 0) ?></td>
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
