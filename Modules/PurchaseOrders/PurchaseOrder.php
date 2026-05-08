<?php
namespace Modules\PurchaseOrders;

use Core\Database;

class PurchaseOrder
{
    private static $table = 'purchase_orders';

    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM " . self::$table . " ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM " . self::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO " . self::$table . " 
            (supplier_name, supplier_rut, supplier_email, supplier_phone, supplier_address, number, status, subtotal, tax, total, currency, exchange_rate, due_date, notes, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['supplier_name'],
            $data['supplier_rut'] ?? '',
            $data['supplier_email'] ?? '',
            $data['supplier_phone'] ?? '',
            $data['supplier_address'] ?? '',
            $data['number'],
            $data['status'] ?? 'draft',
            $data['subtotal'],
            $data['tax'],
            $data['total'],
            $data['currency'] ?? 'CLP',
            $data['exchange_rate'] ?? 1,
            $data['due_date'] ?? null,
            $data['notes'] ?? '',
            $data['created_by']
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE " . self::$table . " 
            SET supplier_name = ?, supplier_rut = ?, supplier_email = ?, supplier_phone = ?, supplier_address = ?, number = ?, status = ?, subtotal = ?, tax = ?, total = ?, currency = ?, exchange_rate = ?, due_date = ?, notes = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([
            $data['supplier_name'],
            $data['supplier_rut'] ?? '',
            $data['supplier_email'] ?? '',
            $data['supplier_phone'] ?? '',
            $data['supplier_address'] ?? '',
            $data['number'],
            $data['status'],
            $data['subtotal'],
            $data['tax'],
            $data['total'],
            $data['currency'] ?? 'CLP',
            $data['exchange_rate'] ?? 1,
            $data['due_date'] ?? null,
            $data['notes'] ?? '',
            $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        // Delete items first
        $stmt = $db->prepare("DELETE FROM purchase_order_items WHERE purchase_order_id = ?");
        $stmt->execute([$id]);
        
        // Delete order
        $stmt = $db->prepare("DELETE FROM " . self::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public static function getItems($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM purchase_order_items WHERE purchase_order_id = ? ORDER BY id");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public static function addItem($orderId, $data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            INSERT INTO purchase_order_items 
            (purchase_order_id, product_name, description, qty, price, tax_rate, total) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $orderId,
            $data['product_name'],
            $data['description'] ?? '',
            $data['qty'],
            $data['price'],
            $data['tax_rate'] ?? 0.19,
            $data['total']
        ]);
        return $db->lastInsertId();
    }

    public static function updateItem($itemId, $data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE purchase_order_items 
            SET product_name = ?, description = ?, qty = ?, price = ?, tax_rate = ?, total = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $data['product_name'],
            $data['description'] ?? '',
            $data['qty'],
            $data['price'],
            $data['tax_rate'] ?? 0.19,
            $data['total'],
            $itemId
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function deleteItem($itemId)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM purchase_order_items WHERE id = ?");
        $stmt->execute([$itemId]);
        return $stmt->rowCount() > 0;
    }
}