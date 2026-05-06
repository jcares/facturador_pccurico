<?php
namespace Modules\Clients;

use Core\Database;

class Client
{
    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM clients ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public static function create($data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO clients (name, rut, email, phone, address) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['rut'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null
        ]);
    }
}
