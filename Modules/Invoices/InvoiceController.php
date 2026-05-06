<?php
namespace Modules\Invoices;

use Core\Controller;
use Core\Calculator;
use Core\CurrencyService;
use Core\Logger;
use Core\Security;
use Modules\Clients\Client;
use Modules\Products\Product;
use Modules\Invoices\RecurringInvoice;

class InvoiceController extends Controller
{
    public function index()
    {
        $id = Security::cleanInt($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->show($id);
            return;
        }

        $invoices = Invoice::all();
        $this->view('invoices/index', [
            'title' => 'Facturas y Boletas',
            'invoices' => $invoices,
            'recurringInvoices' => RecurringInvoice::all()
        ]);
    }

    public function show($id = null)
    {
        $id = Security::cleanInt($id ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            $this->redirect('invoices.php?error=invalid_invoice');
        }

        $invoice = Invoice::find($id);
        if (!$invoice) {
            $this->redirect('invoices.php?error=not_found');
        }

        $this->view('invoices/show', [
            'title' => 'Detalle de Documento',
            'invoice' => $invoice,
            'items' => $invoice['items'] ?? []
        ]);
    }

    public function create()
    {
        $clients = Client::all();
        $products = Product::all();
        $rates = ['CLP' => 1.0, 'USD' => 1.0, 'UF' => 1.0];
        try {
            $rates = array_merge($rates, CurrencyService::getRates());
        } catch (\Exception $e) {
            Logger::error("Currency Rates Load Failed: " . $e->getMessage());
        }
        
        $this->view('invoices/create', [
            'title' => 'Nueva Venta (POS)',
            'clients' => $clients,
            'products' => $products,
            'rates' => $rates
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $clientId = Security::cleanInt($_POST['client_id'] ?? 0);
            $invoiceCurrency = strtoupper(Security::cleanString($_POST['currency'] ?? 'CLP', 10));
            $allowedCurrencies = ['CLP', 'USD', 'UF'];
            if (!in_array($invoiceCurrency, $allowedCurrencies, true)) {
                $invoiceCurrency = 'CLP';
            }

            $rates = ['CLP' => 1.0, 'USD' => 1.0, 'UF' => 1.0];
            try {
                $rates = array_merge($rates, CurrencyService::getRates());
            } catch (\Exception $e) {
                Logger::error("Currency Rates Load Failed on Store: " . $e->getMessage());
            }

            $items = [];
            
            if ($clientId <= 0) {
                $this->redirect('invoices.php?action=create&error=invalid_client');
            }
            
            if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
                foreach ($_POST['product_id'] as $index => $pid) {
                    $productId = Security::cleanInt($pid);
                    $product = $productId > 0 ? Product::find($productId) : null;
                    $qty = max(0, Security::cleanInt($_POST['qty'][$index] ?? 0));

                    if (!$product || $qty <= 0) {
                        continue;
                    }

                    $productCurrency = strtoupper($product['currency'] ?? 'CLP');
                    $productPrice = (float)$product['price'];
                    $rateToClp = (float)($rates[$productCurrency] ?? 1.0);
                    $rateFromClp = (float)($rates[$invoiceCurrency] ?? 1.0);
                    $convertedPrice = $rateFromClp > 0 ? ($productPrice * $rateToClp) / $rateFromClp : $productPrice;
                    $postedPrice = Security::cleanDecimal($_POST['price'][$index] ?? 0);
                    $linePrice = $postedPrice > 0 ? $postedPrice : $convertedPrice;

                    $items[] = [
                        'product_id' => $productId,
                        'qty' => $qty,
                        'price' => max(0, round($linePrice, $invoiceCurrency === 'CLP' ? 0 : 2)),
                        'original_price' => $productPrice,
                        'original_currency' => $productCurrency,
                        'exchange_rate' => $rateToClp
                    ];
                }
            }

            $items = array_filter($items, fn($item) => $item['product_id'] > 0 && $item['qty'] > 0 && $item['price'] > 0);

            if (empty($items)) {
                $this->redirect('invoices.php?action=create&error=no_items');
            }

            $calculation = Calculator::calculate($items, 0.19, $invoiceCurrency === 'CLP' ? 0 : 2);

            $invoiceData = [
                'client_id' => $clientId,
                'number' => 'INV-' . date('Ymd-His') . '-' . random_int(100, 999),
                'subtotal' => $calculation['subtotal'],
                'tax' => $calculation['tax'],
                'total' => $calculation['total'],
                'currency' => $invoiceCurrency,
                'exchange_rate' => $rates[$invoiceCurrency] ?? 1.0,
                'due_date' => !empty($_POST['due_date']) ? Security::cleanString($_POST['due_date'], 10) : null,
                'token' => bin2hex(random_bytes(16))
            ];

            try {
                $invoiceId = Invoice::create($invoiceData, $items);
                if (!empty($_POST['make_recurring'])) {
                    $frequency = Security::cleanString($_POST['recurring_frequency'] ?? 'monthly', 20);
                    if (!in_array($frequency, ['weekly', 'monthly', 'quarterly', 'yearly'], true)) {
                        $frequency = 'monthly';
                    }

                    $startDate = Security::cleanString($_POST['recurring_start_date'] ?? '', 10);
                    if (!$this->isValidDate($startDate)) {
                        $startDate = RecurringInvoice::nextRunDate(date('Y-m-d'), $frequency);
                    }

                    $dueDays = min(365, max(0, Security::cleanInt($_POST['recurring_due_days'] ?? 30)));
                    $remainingCycles = Security::cleanInt($_POST['recurring_remaining_cycles'] ?? 0);

                    RecurringInvoice::create([
                        'source_invoice_id' => $invoiceId,
                        'client_id' => $clientId,
                        'frequency' => $frequency,
                        'start_date' => $startDate,
                        'next_run_date' => $startDate,
                        'due_days' => $dueDays,
                        'remaining_cycles' => $remainingCycles > 0 ? $remainingCycles : null,
                        'subtotal' => $calculation['subtotal'],
                        'tax' => $calculation['tax'],
                        'total' => $calculation['total'],
                        'currency' => $invoiceCurrency,
                        'exchange_rate' => $rates[$invoiceCurrency] ?? 1.0,
                    ], $items);
                }
                $this->redirect('invoices.php?id=' . $invoiceId);
            } catch (\Exception $e) {
                Logger::error("Invoice Creation Failed: " . $e->getMessage());
                $this->redirect('invoices.php?action=create&error=db_error');
            }
        }
    }

    private function isValidDate($date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d', (string)$date);
        return $dt && $dt->format('Y-m-d') === $date;
    }

    public function print()
    {
        $id = intval($_GET['id'] ?? 0);
        $format = $_GET['format'] ?? 'a4';
        
        $allowedFormats = ['a4', 'ticket_80mm'];
        if (!in_array($format, $allowedFormats)) {
            http_response_code(400);
            echo 'Formato de plantilla invalido.';
            return;
        }

        if ($id <= 0) {
            http_response_code(400);
            echo 'Factura no especificada.';
            return;
        }

        $invoice = Invoice::find($id);

        if (!$invoice) {
            http_response_code(404);
            echo 'Factura no encontrada.';
            return;
        }
        
        $db = \Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM settings");
        $settingsRaw = $stmt->fetchAll();
        $settings = [];
        foreach($settingsRaw as $s) {
            $settings[$s['key']] = $s['value'];
        }

        $templateFile = \ROOT_PATH . "/templates/invoice_{$format}.php";

        if (!file_exists($templateFile)) {
            http_response_code(404);
            echo 'Plantilla no encontrada.';
            return;
        }
        
        include $templateFile;
    }

    public function cancel()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Metodo no permitido.';
            return;
        }

        $id = Security::cleanInt($_POST['id'] ?? 0);
        $reason = Security::cleanString($_POST['reason'] ?? '', 500);

        if ($id <= 0) {
            $this->redirect('invoices.php?error=invalid_invoice');
        }

        try {
            Invoice::cancel($id, $reason);
            $this->redirect('invoices.php?success=canceled');
        } catch (\Exception $e) {
            Logger::error("Invoice Cancel Failed: " . $e->getMessage());
            $this->redirect('invoices.php?error=cancel_failed');
        }
    }
}
