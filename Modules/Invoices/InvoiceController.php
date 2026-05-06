<?php
namespace Modules\Invoices;

use Core\Controller;
use Core\Calculator;
use Modules\Clients\Client;
use Modules\Products\Product;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::all();
        $this->view('invoices/index', [
            'title' => 'Facturas y Boletas',
            'invoices' => $invoices
        ]);
    }

    public function create()
    {
        $clients = Client::all();
        $products = Product::all();
        
        $this->view('invoices/create', [
            'title' => 'Nueva Venta (POS)',
            'clients' => $clients,
            'products' => $products
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $clientId = $_POST['client_id'];
            $items = [];
            
            // Reconstruct items array from POST data
            if (isset($_POST['product_id']) && is_array($_POST['product_id'])) {
                foreach ($_POST['product_id'] as $index => $pid) {
                    $items[] = [
                        'product_id' => $pid,
                        'qty' => $_POST['qty'][$index],
                        'price' => $_POST['price'][$index]
                    ];
                }
            }

            if (empty($items)) {
                $this->redirect('invoices.php?action=create&error=no_items');
            }

            // Use the isolated Core Calculator
            $calculation = Calculator::calculate($items);

            $invoiceData = [
                'client_id' => $clientId,
                'number' => 'INV-' . time(), // Simple unique generation
                'subtotal' => $calculation['subtotal'],
                'tax' => $calculation['tax'],
                'total' => $calculation['total'],
                'due_date' => !empty($_POST['due_date']) ? $_POST['due_date'] : null,
                'token' => bin2hex(random_bytes(16))
            ];

            try {
                $invoiceId = Invoice::create($invoiceData, $items);
                $this->redirect('invoices.php');
            } catch (\Exception $e) {
                // Redirect back with error
                $this->redirect('invoices.php?action=create&error=db_error');
            }
        }
    }

    public function print()
    {
        $id = $_GET['id'] ?? null;
        $format = $_GET['format'] ?? 'a4';

        if (!$id) {
            die("Factura no especificada.");
        }

        $invoice = Invoice::find($id);

        if (!$invoice) {
            die("Factura no encontrada.");
        }

        // For a real app, business config would come from the database settings table.
        // I will just mock it or fetch it quickly if needed, but for templates we can use static for now or fetch settings.
        
        $db = \Core\Database::getInstance();
        $stmt = $db->query("SELECT * FROM settings");
        $settingsRaw = $stmt->fetchAll();
        $settings = [];
        foreach($settingsRaw as $s) {
            $settings[$s['key']] = $s['value'];
        }

        $templateFile = \ROOT_PATH . "/templates/invoice_{$format}.php";

        if (file_exists($templateFile)) {
            // Render without layout
            extract(['invoice' => $invoice, 'settings' => $settings]);
            require $templateFile;
        } else {
            die("Plantilla no encontrada.");
        }
    }
}
