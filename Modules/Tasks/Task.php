<?php
namespace Modules\Tasks;

use Core\Database;

class Task
{
    private static $table = 'tasks';

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
            (title, description, status, priority, due_date, assigned_to, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['status'] ?? 'pending',
            $data['priority'] ?? 'medium',
            $data['due_date'] ?? null,
            $data['assigned_to'] ?? null,
            $data['created_by']
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE " . self::$table . " 
            SET title = ?, description = ?, status = ?, priority = ?, due_date = ?, assigned_to = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['status'],
            $data['priority'],
            $data['due_date'] ?? null,
            $data['assigned_to'] ?? null,
            $id
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM " . self::$table . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}