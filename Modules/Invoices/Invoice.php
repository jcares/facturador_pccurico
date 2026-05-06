<?php
namespace Modules\Invoices;

use Core\Database;

class Invoice
{
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT i.*, c.name as client_name 
            FROM invoices i 
            LEFT JOIN clients c ON i.client_id = c.id 
            ORDER BY i.id DESC
        ");
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        
        $stmt = $db->prepare("
            SELECT i.*, c.name as client_name, c.rut as client_rut, c.address as client_address, c.email as client_email, c.phone as client_phone
            FROM invoices i 
            LEFT JOIN clients c ON i.client_id = c.id 
            WHERE i.id = ?
        ");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if ($invoice) {
            $stmtItems = $db->prepare("
                SELECT it.*, p.name as product_name, p.sku as product_sku 
                FROM invoice_items it 
                LEFT JOIN products p ON it.product_id = p.id 
                WHERE it.invoice_id = ?
            ");
            $stmtItems->execute([$id]);
            $invoice['items'] = $stmtItems->fetchAll();
        }

        return $invoice;
    }

    public static function create($data, $items)
    {
        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO invoices (client_id, number, status, subtotal, tax, total, due_date, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['client_id'],
                $data['number'],
                'sent',
                $data['subtotal'],
                $data['tax'],
                $data['total'],
                $data['due_date'] ?? null,
                $data['token']
            ]);
            
            $invoiceId = $db->lastInsertId();

            $stmtItem = $db->prepare("INSERT INTO invoice_items (invoice_id, product_id, qty, price, total) VALUES (?, ?, ?, ?, ?)");
            $stmtStock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($items as $item) {
                $itemTotal = $item['qty'] * $item['price'];
                $stmtItem->execute([
                    $invoiceId,
                    $item['product_id'],
                    $item['qty'],
                    $item['price'],
                    $itemTotal
                ]);

                // Reduce stock
                $stmtStock->execute([$item['qty'], $item['product_id']]);
            }

            $db->commit();
            return $invoiceId;

        } catch (\Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
