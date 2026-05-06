<?php
namespace Modules\Payments;

use Core\Database;

class Payment
{
    private static function columnExists($table, $column)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT p.*, i.number as invoice_number, i.currency as invoice_currency, COALESCE(c.business_name, c.name) as client_name 
            FROM payments p 
            LEFT JOIN invoices i ON p.invoice_id = i.id 
            LEFT JOIN clients c ON i.client_id = c.id 
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT p.*, i.number as invoice_number, i.currency as invoice_currency, COALESCE(c.business_name, c.name) as client_name 
            FROM payments p 
            LEFT JOIN invoices i ON p.invoice_id = i.id 
            LEFT JOIN clients c ON i.client_id = c.id 
            WHERE p.id = ?
        ");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $db = Database::getInstance();
        
        if (empty($data['invoice_id']) || empty($data['amount'])) {
            throw new \Exception('Datos incompletos para crear pago.');
        }

        $invoiceId = intval($data['invoice_id']);
        $amount = floatval($data['amount']);
        $method = htmlspecialchars(trim($data['method'] ?? 'Transferencia'));

        if ($invoiceId <= 0 || $amount <= 0) {
            throw new \Exception('Datos inválidos para crear pago.');
        }

        try {
            $db->beginTransaction();

            $stmtInv = $db->prepare("SELECT total, currency, exchange_rate FROM invoices WHERE id = ?");
            $stmtInv->execute([$invoiceId]);
            $invoice = $stmtInv->fetch();

            if (!$invoice) {
                throw new \Exception('Factura no encontrada.');
            }

            if (self::columnExists('payments', 'currency')) {
                $stmt = $db->prepare("INSERT INTO payments (invoice_id, amount, currency, exchange_rate, method) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $invoiceId,
                    $amount,
                    $invoice['currency'] ?? 'CLP',
                    $invoice['exchange_rate'] ?? 1,
                    $method
                ]);
            } else {
                $stmt = $db->prepare("INSERT INTO payments (invoice_id, amount, method) VALUES (?, ?, ?)");
                $stmt->execute([$invoiceId, $amount, $method]);
            }

            $stmtPay = $db->prepare("SELECT SUM(amount) as paid FROM payments WHERE invoice_id = ?");
            $stmtPay->execute([$invoiceId]);
            $paid = $stmtPay->fetch()['paid'] ?? 0;

            $status = ($paid >= $invoice['total']) ? 'paid' : 'sent';

            $stmtUpdate = $db->prepare("UPDATE invoices SET status = ? WHERE id = ?");
            $stmtUpdate->execute([$status, $invoiceId]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getByInvoice($invoiceId)
    {
        $db = Database::getInstance();
        $invoiceId = intval($invoiceId);
        
        $stmt = $db->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY id DESC");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }
}
