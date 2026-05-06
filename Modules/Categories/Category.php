<?php
namespace Modules\Categories;

use Core\Database;

class Category
{
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        if (empty($data['name'])) {
            throw new \Exception('El nombre de la categoría es requerido.');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([htmlspecialchars(trim($data['name']))]);
        return $db->lastInsertId();
    }

    public static function update($id, $data)
    {
        if (empty($data['name'])) {
            throw new \Exception('El nombre de la categoría es requerido.');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([htmlspecialchars(trim($data['name'])), (int)$id]);
        return $stmt->rowCount() > 0;
    }

    public static function delete($id)
    {
        $db = Database::getInstance();

        // Check if category is being used by products
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([(int)$id]);
        $usage = $stmt->fetch();

        if ($usage['count'] > 0) {
            throw new \Exception('No se puede eliminar la categoría porque está siendo usada por productos.');
        }

        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public static function getWithProductCount()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    }
}