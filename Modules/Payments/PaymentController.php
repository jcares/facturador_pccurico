<?php
namespace Modules\Payments;

use Core\Controller;
use Modules\Invoices\Invoice;

class PaymentController extends Controller
{
    public function create()
    {
        $invoiceId = $_GET['invoice_id'] ?? null;
        if (!$invoiceId) {
            $this->redirect('invoices.php');
        }

        $invoice = Invoice::find($invoiceId);
        $payments = Payment::getByInvoice($invoiceId);

        $totalPaid = array_sum(array_column($payments, 'amount'));
        $balance = $invoice['total'] - $totalPaid;

        $this->view('payments/create', [
            'title' => 'Registrar Pago',
            'invoice' => $invoice,
            'payments' => $payments,
            'balance' => $balance
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                Payment::create($_POST);
            } catch (\Exception $e) {
                // handle error
            }
            $this->redirect('invoices.php');
        }
    }
}
