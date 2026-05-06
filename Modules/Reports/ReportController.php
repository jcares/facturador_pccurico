<?php
namespace Modules\Reports;

use Core\Controller;
use Core\Database;

class ReportController extends Controller
{
    public function index()
    {
        $db = Database::getInstance();

        // Ingresos Totales vs Pendientes
        $stmtTotal = $db->query("SELECT SUM(total) as total_billed FROM invoices WHERE status != 'canceled'");
        $totalBilled = $stmtTotal->fetch()['total_billed'] ?? 0;

        $stmtPaid = $db->query("SELECT SUM(amount) as total_paid FROM payments");
        $totalPaid = $stmtPaid->fetch()['total_paid'] ?? 0;

        $totalPending = $totalBilled - $totalPaid;

        // Cuentas por cobrar (Facturas no pagadas)
        $stmtAccounts = $db->query("
            SELECT i.*, c.name as client_name 
            FROM invoices i 
            JOIN clients c ON i.client_id = c.id 
            WHERE i.status != 'paid' AND i.status != 'canceled'
            ORDER BY i.due_date ASC
        ");
        $receivables = $stmtAccounts->fetchAll();

        // Add calculated balance to each receivable
        foreach ($receivables as &$rec) {
            $stmtPay = $db->prepare("SELECT SUM(amount) as paid FROM payments WHERE invoice_id = ?");
            $stmtPay->execute([$rec['id']]);
            $paid = $stmtPay->fetch()['paid'] ?? 0;
            $rec['balance'] = $rec['total'] - $paid;
        }

        $this->view('reports/index', [
            'title' => 'Reportes Financieros',
            'totalBilled' => $totalBilled,
            'totalPaid' => $totalPaid,
            'totalPending' => $totalPending,
            'receivables' => $receivables
        ]);
    }
}
