<?php
namespace Modules\Invoices;

use Core\Database;

class Invoice
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
            SELECT i.*, COALESCE(c.business_name, c.name) as client_name 
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
            SELECT i.*, COALESCE(c.business_name, c.name) as client_name, c.rut as client_rut, c.address as client_address, c.email as client_email, c.phone as client_phone
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

            if (self::columnExists('invoices', 'currency')) {
                $stmt = $db->prepare("INSERT INTO invoices (client_id, number, status, subtotal, tax, total, currency, exchange_rate, due_date, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $data['client_id'],
                    $data['number'],
                    'sent',
                    $data['subtotal'],
                    $data['tax'],
                    $data['total'],
                    $data['currency'] ?? 'CLP',
                    $data['exchange_rate'] ?? 1,
                    $data['due_date'] ?? null,
                    $data['token']
                ]);
            } else {
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
            }
            
            $invoiceId = $db->lastInsertId();

            $hasItemCurrency = self::columnExists('invoice_items', 'original_currency');
            $stmtItem = $hasItemCurrency
                ? $db->prepare("INSERT INTO invoice_items (invoice_id, product_id, qty, price, original_price, original_currency, exchange_rate, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                : $db->prepare("INSERT INTO invoice_items (invoice_id, product_id, qty, price, total) VALUES (?, ?, ?, ?, ?)");
            $stmtStock = $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($items as $item) {
                $itemTotal = $item['qty'] * $item['price'];
                if ($hasItemCurrency) {
                    $stmtItem->execute([
                        $invoiceId,
                        $item['product_id'],
                        $item['qty'],
                        $item['price'],
                        $item['original_price'] ?? $item['price'],
                        $item['original_currency'] ?? 'CLP',
                        $item['exchange_rate'] ?? 1,
                        $itemTotal
                    ]);
                } else {
                    $stmtItem->execute([
                        $invoiceId,
                        $item['product_id'],
                        $item['qty'],
                        $item['price'],
                        $itemTotal
                    ]);
                }

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

    public static function cancel($id, $reason = '')
    {
        $db = Database::getInstance();
        $id = (int)$id;

        try {
            $db->beginTransaction();

            $invoice = self::find($id);
            if (!$invoice) {
                throw new \Exception('Factura no encontrada.');
            }

            if ($invoice['status'] === 'paid') {
                throw new \Exception('No se puede anular una factura pagada.');
            }

            $stmt = $db->prepare("UPDATE invoices SET status = 'canceled' WHERE id = ?");
            $stmt->execute([$id]);

            $stmtStock = $db->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
            foreach ($invoice['items'] as $item) {
                if (!empty($item['product_id'])) {
                    $stmtStock->execute([(int)$item['qty'], (int)$item['product_id']]);
                }
            }

            $db->exec("
                CREATE TABLE IF NOT EXISTS `credit_notes` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `invoice_id` INT NOT NULL,
                    `reason` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ");

            $stmtNote = $db->prepare("INSERT INTO credit_notes (invoice_id, reason) VALUES (?, ?)");
            $stmtNote->execute([$id, htmlspecialchars(trim($reason))]);

            $db->commit();
            return true;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
