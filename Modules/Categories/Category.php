<?php
namespace Modules\Categories;

use Core\Database;

class Category
{
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT c.*, p.name as parent_name
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            ORDER BY COALESCE(p.name, c.name) ASC, c.parent_id IS NOT NULL ASC, c.name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function parents()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public static function children()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT c.*, p.name as parent_name
            FROM categories c
            INNER JOIN categories p ON c.parent_id = p.id
            ORDER BY p.name ASC, c.name ASC
        ");
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
            throw new \Exception('El nombre de la categoria es requerido.');
        }

        $db = Database::getInstance();
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        if ($parentId !== null && !self::isParent($parentId)) {
            throw new \Exception('La categoria padre no es valida.');
        }

        $stmt = $db->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
        $stmt->execute([htmlspecialchars(trim($data['name'])), $parentId]);
        return $db->lastInsertId();
    }

    public static function update($id, $data)
    {
        if (empty($data['name'])) {
            throw new \Exception('El nombre de la categoria es requerido.');
        }

        $db = Database::getInstance();
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        if ($parentId === (int)$id) {
            throw new \Exception('Una categoria no puede ser padre de si misma.');
        }

        if ($parentId !== null && self::hasChildren((int)$id)) {
            throw new \Exception('Una categoria padre con hijas no puede convertirse en hija.');
        }

        if ($parentId !== null && !self::isParent($parentId)) {
            throw new \Exception('La categoria padre no es valida.');
        }

        $stmt = $db->prepare("UPDATE categories SET name = ?, parent_id = ? WHERE id = ?");
        $stmt->execute([htmlspecialchars(trim($data['name'])), $parentId, (int)$id]);
        return $stmt->rowCount() > 0;
    }

    public static function delete($id)
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([(int)$id]);
        $usage = $stmt->fetch();

        if ($usage['count'] > 0) {
            throw new \Exception('No se puede eliminar la categoria porque esta siendo usada por productos.');
        }

        $stmt = $db->prepare("SELECT COUNT(*) as count FROM categories WHERE parent_id = ?");
        $stmt->execute([(int)$id]);
        $children = $stmt->fetch();

        if ($children['count'] > 0) {
            throw new \Exception('No se puede eliminar la categoria porque tiene categorias hijas.');
        }

        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public static function getWithProductCount()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT c.*, parent.name as parent_name, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN categories parent ON c.parent_id = parent.id
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY COALESCE(parent.name, c.name) ASC, c.parent_id IS NOT NULL ASC, c.name ASC
        ");
        return $stmt->fetchAll();
    }

    public static function isParent($id)
    {
        $category = self::find((int)$id);
        return $category && empty($category['parent_id']);
    }

    public static function isChild($id)
    {
        $category = self::find((int)$id);
        return $category && !empty($category['parent_id']);
    }

    public static function hasChildren($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
        $stmt->execute([(int)$id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
