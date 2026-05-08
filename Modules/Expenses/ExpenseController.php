<?php
namespace Modules\Expenses;

use Core\Controller;
use Core\Security;
use Core\Auth;
use Modules\Expenses\Expense;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::all();
        $totalMonth = Expense::getTotalByMonth();
        $byCategory = Expense::getByCategory();
        
        $this->view('expenses/index', [
            'title' => 'Gestión de Gastos',
            'expenses' => $expenses,
            'totalMonth' => $totalMonth,
            'byCategory' => $byCategory
        ]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => Security::cleanString($_POST['title'] ?? ''),
                'description' => Security::cleanString($_POST['description'] ?? ''),
                'category' => Security::cleanString($_POST['category'] ?? ''),
                'amount' => Security::cleanFloat($_POST['amount'] ?? 0),
                'currency' => Security::cleanString($_POST['currency'] ?? 'CLP'),
                'exchange_rate' => Security::cleanFloat($_POST['exchange_rate'] ?? 1),
                'date' => Security::cleanString($_POST['date'] ?? date('Y-m-d')),
                'supplier' => Security::cleanString($_POST['supplier'] ?? ''),
                'payment_method' => Security::cleanString($_POST['payment_method'] ?? ''),
                'tax_deductible' => isset($_POST['tax_deductible']) ? 1 : 0,
                'created_by' => Auth::id()
            ];

            if (empty($data['title']) || $data['amount'] <= 0) {
                $this->redirect('expenses.php?error=empty_fields');
            }

            // Handle file upload for receipt
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../storage/uploads/receipts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['receipt']['name']);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $filePath)) {
                    $data['receipt_file'] = $fileName;
                }
            }

            $id = Expense::create($data);
            $this->redirect('expenses.php?success=created');
        }

        $this->view('expenses/create', [
            'title' => 'Registrar Nuevo Gasto'
        ]);
    }

    public function edit($id = null)
    {
        $id = Security::cleanInt($id ?? ($_GET['id'] ?? 0));
        if ($id <= 0) {
            $this->redirect('expenses.php?error=invalid_id');
        }

        $expense = Expense::find($id);
        if (!$expense) {
            $this->redirect('expenses.php?error=not_found');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title' => Security::cleanString($_POST['title'] ?? ''),
                'description' => Security::cleanString($_POST['description'] ?? ''),
                'category' => Security::cleanString($_POST['category'] ?? ''),
                'amount' => Security::cleanFloat($_POST['amount'] ?? 0),
                'currency' => Security::cleanString($_POST['currency'] ?? 'CLP'),
                'exchange_rate' => Security::cleanFloat($_POST['exchange_rate'] ?? 1),
                'date' => Security::cleanString($_POST['date'] ?? date('Y-m-d')),
                'supplier' => Security::cleanString($_POST['supplier'] ?? ''),
                'payment_method' => Security::cleanString($_POST['payment_method'] ?? ''),
                'tax_deductible' => isset($_POST['tax_deductible']) ? 1 : 0
            ];

            if (empty($data['title']) || $data['amount'] <= 0) {
                $this->redirect('expenses.php?error=empty_fields');
            }

            // Handle file upload for receipt
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../storage/uploads/receipts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $fileName = uniqid() . '_' . basename($_FILES['receipt']['name']);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $filePath)) {
                    $data['receipt_file'] = $fileName;
                }
            }

            Expense::update($id, $data);
            $this->redirect('expenses.php?success=updated');
        }

        $this->view('expenses/edit', [
            'title' => 'Editar Gasto',
            'expense' => $expense
        ]);
    }

    public function delete()
    {
        $id = Security::cleanInt($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('expenses.php?error=invalid_id');
        }

        Expense::delete($id);
        $this->redirect('expenses.php?success=deleted');
    }
}