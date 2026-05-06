<?php
namespace Modules\Products;

use Core\Database;

class Product
{
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.id DESC
        ");
        return $stmt->fetchAll();
    }

    public static function create($data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO products (name, sku, price, currency, category_id, tax_rate, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['sku'] ?? null,
            $data['price'],
            $data['currency'] ?? 'CLP',
            !empty($data['category_id']) ? $data['category_id'] : null,
            $data['tax_rate'] ?? 0.19,
            $data['stock'] ?? 0
        ]);
    }
}
