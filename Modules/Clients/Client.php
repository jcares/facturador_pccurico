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

    public static function find($id)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        if (empty($data['business_name']) || empty($data['rut'])) {
            throw new \Exception('Razón Social y RUT son requeridos.');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO clients (business_name, contact_name, rut, email, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            htmlspecialchars(trim($data['business_name'])),
            !empty($data['contact_name']) ? htmlspecialchars(trim($data['contact_name'])) : null,
            htmlspecialchars(trim($data['rut'])),
            !empty($data['email']) ? filter_var($data['email'], FILTER_VALIDATE_EMAIL) : null,
            htmlspecialchars(trim($data['phone'] ?? '')),
            htmlspecialchars(trim($data['address'] ?? ''))
        ]);
    }

    public static function update($id, $data)
    {
        if (empty($data['business_name']) || empty($data['rut'])) {
            throw new \Exception('Razón Social y RUT son requeridos.');
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE clients SET business_name = ?, contact_name = ?, rut = ?, email = ?, phone = ?, address = ? WHERE id = ?");

        return $stmt->execute([
            htmlspecialchars(trim($data['business_name'])),
            !empty($data['contact_name']) ? htmlspecialchars(trim($data['contact_name'])) : null,
            htmlspecialchars(trim($data['rut'])),
            !empty($data['email']) ? filter_var($data['email'], FILTER_VALIDATE_EMAIL) : null,
            htmlspecialchars(trim($data['phone'] ?? '')),
            htmlspecialchars(trim($data['address'] ?? '')),
            (int)$id
        ]);
    }

    public static function delete($id)
    {
        $db = Database::getInstance();

        $stmt = $db->prepare("SELECT COUNT(*) as count FROM invoices WHERE client_id = ?");
        $stmt->execute([(int)$id]);
        $usage = $stmt->fetch();

        if ((int)($usage['count'] ?? 0) > 0) {
            throw new \Exception('No se puede eliminar el cliente porque tiene documentos asociados.');
        }

        $stmt = $db->prepare("DELETE FROM clients WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }
}
