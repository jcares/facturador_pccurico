<?php
namespace Modules\Payments;

use Core\Controller;
use Core\Logger;
use Core\Validator;
use Modules\Invoices\Invoice;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::all();
        $this->view('payments/index', [
            'title' => 'Historial de Pagos',
            'payments' => $payments
        ]);
    }

    public function create()
    {
        $invoiceId = intval($_GET['invoice_id'] ?? 0);
        if ($invoiceId <= 0) {
            $this->redirect('invoices.php');
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            $this->redirect('invoices.php?error=not_found');
        }

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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Método no permitido.';
            return;
        }

        $invoiceId = intval($_POST['invoice_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $method = trim($_POST['method'] ?? '');

        $validator = new Validator();
        $validator->validate(
            ['invoice_id' => $invoiceId, 'amount' => $amount, 'method' => $method],
            [
                'invoice_id' => 'required|integer|min:1',
                'amount' => 'required|numeric|min:1',
                'method' => 'required'
            ]
        );

        if ($validator->fails()) {
            Logger::error("Payment Validation Failed: " . json_encode($validator->errors()));
            $this->redirect('payments.php?invoice_id=' . $invoiceId . '&error=validation');
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            $this->redirect('invoices.php?error=not_found');
        }

        if ($amount <= 0) {
            $this->redirect('payments.php?invoice_id=' . $invoiceId . '&error=invalid_amount');
        }

        $payments = Payment::getByInvoice($invoiceId);
        $totalPaid = array_sum(array_column($payments, 'amount'));
        $balance = $invoice['total'] - $totalPaid;

        if ($amount > $balance) {
            $this->redirect('payments.php?invoice_id=' . $invoiceId . '&error=amount_exceeds');
        }

        try {
            Payment::create([
                'invoice_id' => $invoiceId,
                'amount' => $amount,
                'method' => htmlspecialchars($method)
            ]);
            $this->redirect('invoices.php?success=payment_registered');
        } catch (\Exception $e) {
            Logger::error("Payment Creation Error: " . $e->getMessage());
            $this->redirect('payments.php?invoice_id=' . $invoiceId . '&error=system');
        }
    }
}
