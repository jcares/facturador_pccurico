<?php
namespace Modules\Payments;

use Core\Database;

class Payment
{
    public static function create($data)
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO payments (invoice_id, amount, method) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['invoice_id'],
                $data['amount'],
                $data['method']
            ]);

            // Recalculate invoice status
            $stmtInv = $db->prepare("SELECT total FROM invoices WHERE id = ?");
            $stmtInv->execute([$data['invoice_id']]);
            $invoice = $stmtInv->fetch();

            $stmtPay = $db->prepare("SELECT SUM(amount) as paid FROM payments WHERE invoice_id = ?");
            $stmtPay->execute([$data['invoice_id']]);
            $paid = $stmtPay->fetch()['paid'] ?? 0;

            $status = ($paid >= $invoice['total']) ? 'paid' : 'sent';

            $stmtUpdate = $db->prepare("UPDATE invoices SET status = ? WHERE id = ?");
            $stmtUpdate->execute([$status, $data['invoice_id']]);

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
        $stmt = $db->prepare("SELECT * FROM payments WHERE invoice_id = ? ORDER BY id DESC");
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }
}
