<?php
namespace Modules\Expenses;

use Core\Database;

class Expense
{
    private static $table = 'expenses';

    public static function all()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM " . self::$table . " ORDER BY date DESC");
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
            (title, description, category, amount, currency, exchange_rate, date, receipt_file, supplier, payment_method, tax_deductible, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['category'] ?? '',
            $data['amount'],
            $data['currency'] ?? 'CLP',
            $data['exchange_rate'] ?? 1,
            $data['date'],
            $data['receipt_file'] ?? '',
            $data['supplier'] ?? '',
            $data['payment_method'] ?? '',
            $data['tax_deductible'] ?? false,
            $data['created_by']
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data)
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            UPDATE " . self::$table . " 
            SET title = ?, description = ?, category = ?, amount = ?, currency = ?, exchange_rate = ?, date = ?, receipt_file = ?, supplier = ?, payment_method = ?, tax_deductible = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([
            $data['title'],
            $data['description'] ?? '',
            $data['category'] ?? '',
            $data['amount'],
            $data['currency'] ?? 'CLP',
            $data['exchange_rate'] ?? 1,
            $data['date'],
            $data['receipt_file'] ?? '',
            $data['supplier'] ?? '',
            $data['payment_method'] ?? '',
            $data['tax_deductible'] ?? false,
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

    public static function getTotalByMonth($month = null, $year = null)
    {
        $db = Database::getInstance();
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $stmt = $db->prepare("
            SELECT SUM(amount * exchange_rate) as total 
            FROM " . self::$table . " 
            WHERE MONTH(date) = ? AND YEAR(date) = ?
        ");
        $stmt->execute([$month, $year]);
        return $stmt->fetch()['total'] ?? 0;
    }

    public static function getByCategory()
    {
        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT category, SUM(amount * exchange_rate) as total 
            FROM " . self::$table . " 
            WHERE category IS NOT NULL AND category != ''
            GROUP BY category 
            ORDER BY total DESC
        ");
        return $stmt->fetchAll();
    }
}