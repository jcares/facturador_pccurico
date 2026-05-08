<?php
namespace Modules\Products;

use Core\Database;

class Product
{
    private static function generateSku($name = '')
    {
        $db = Database::getInstance();
        $base = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', (string)$name));
        $base = substr($base ?: 'PROD', 0, 4);

        do {
            $sku = $base . '-' . date('ymd') . '-' . random_int(1000, 9999);
            $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE sku = ?");
            $stmt->execute([$sku]);
        } while ((int)$stmt->fetchColumn() > 0);

        return $sku;
    }

    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT p.*, c.name as category_name, parent.name as parent_category_name
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN categories parent ON c.parent_id = parent.id
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        if (empty($data['name']) || empty($data['price'])) {
            throw new \Exception('Nombre y precio son requeridos.');
        }
        
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO products (name, sku, price, currency, category_id, price_unit, tax_rate, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $sku = !empty($data['sku']) ? htmlspecialchars(trim($data['sku'])) : self::generateSku($data['name']);
        return $stmt->execute([
            htmlspecialchars(trim($data['name'])),
            $sku,
            floatval($data['price']),
            htmlspecialchars(trim($data['currency'] ?? 'CLP')),
            !empty($data['category_id']) ? intval($data['category_id']) : null,
            self::normalizePriceUnit($data['price_unit'] ?? 'unit'),
            floatval($data['tax_rate'] ?? 0.19),
            floatval($data['stock'] ?? 0)
        ]);
    }

    public static function update($id, $data)
    {
        if (empty($data['name']) || empty($data['price'])) {
            throw new \Exception('Nombre y precio son requeridos.');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE products SET name = ?, sku = ?, price = ?, currency = ?, category_id = ?, price_unit = ?, tax_rate = ?, stock = ? WHERE id = ?");
        $sku = !empty($data['sku']) ? htmlspecialchars(trim($data['sku'])) : self::generateSku($data['name']);

        return $stmt->execute([
            htmlspecialchars(trim($data['name'])),
            $sku,
            floatval($data['price']),
            htmlspecialchars(trim($data['currency'] ?? 'CLP')),
            !empty($data['category_id']) ? intval($data['category_id']) : null,
            self::normalizePriceUnit($data['price_unit'] ?? 'unit'),
            floatval($data['tax_rate'] ?? 0.19),
            floatval($data['stock'] ?? 0),
            (int)$id
        ]);
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        
        // Check if product is used in any invoices
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM invoice_items WHERE product_id = ?");
        $stmt->execute([(int)$id]);
        $usage = $stmt->fetch();
        
        if ($usage['count'] > 0) {
            throw new \Exception('No se puede eliminar el producto porque está siendo usado en facturas existentes.');
        }
        
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    private static function normalizePriceUnit($value)
    {
        $value = (string)$value;
        return in_array($value, ['unit', 'meter'], true) ? $value : 'unit';
    }
}
