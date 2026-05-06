<?php
namespace Modules\Invoices;

use Core\Database;

class RecurringInvoice
{
    public static function all(): array
    {
        $db = Database::getInstance();
        try {
            $stmt = $db->query("
                SELECT r.*, COALESCE(c.business_name, c.name) as client_name
                FROM recurring_invoices r
                LEFT JOIN clients c ON r.client_id = c.id
                ORDER BY r.id DESC
            ");
        } catch (\PDOException $e) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public static function create(array $data, array $items): int
    {
        $db = Database::getInstance();
        self::ensureSchema();

        $stmt = $db->prepare("
            INSERT INTO recurring_invoices (
                source_invoice_id, client_id, frequency, status, start_date, next_run_date,
                due_days, remaining_cycles, cycles_generated, subtotal, tax, total,
                currency, exchange_rate
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['source_invoice_id'] ?? null,
            $data['client_id'],
            $data['frequency'],
            'active',
            $data['start_date'],
            $data['next_run_date'],
            $data['due_days'],
            $data['remaining_cycles'],
            $data['subtotal'],
            $data['tax'],
            $data['total'],
            $data['currency'] ?? 'CLP',
            $data['exchange_rate'] ?? 1,
        ]);

        $recurringId = (int)$db->lastInsertId();

        $stmtItem = $db->prepare("
            INSERT INTO recurring_invoice_items (
                recurring_invoice_id, product_id, qty, price, original_price,
                original_currency, exchange_rate, total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $stmtItem->execute([
                $recurringId,
                $item['product_id'],
                $item['qty'],
                $item['price'],
                $item['original_price'] ?? $item['price'],
                $item['original_currency'] ?? 'CLP',
                $item['exchange_rate'] ?? 1,
                (float)$item['qty'] * (float)$item['price'],
            ]);
        }

        return $recurringId;
    }

    public static function dueTemplates(): array
    {
        $db = Database::getInstance();
        self::ensureSchema();
        try {
            $stmt = $db->query("
                SELECT *
                FROM recurring_invoices
                WHERE status = 'active'
                  AND next_run_date IS NOT NULL
                  AND next_run_date <= CURDATE()
                  AND (remaining_cycles IS NULL OR remaining_cycles > cycles_generated)
                ORDER BY next_run_date ASC, id ASC
            ");
        } catch (\PDOException $e) {
            return [];
        }

        return $stmt->fetchAll();
    }

    public static function items(int $recurringId): array
    {
        $db = Database::getInstance();
        self::ensureSchema();
        $stmt = $db->prepare("SELECT * FROM recurring_invoice_items WHERE recurring_invoice_id = ?");
        $stmt->execute([$recurringId]);
        return $stmt->fetchAll();
    }

    public static function recordGenerated(int $recurringId, int $invoiceId, string $frequency, ?int $remainingCycles, int $cyclesGenerated, string $currentRunDate): void
    {
        $db = Database::getInstance();
        self::ensureSchema();
        $newCyclesGenerated = $cyclesGenerated + 1;
        $completed = $remainingCycles !== null && $newCyclesGenerated >= $remainingCycles;
        $nextRunDate = $completed ? null : self::nextRunDate($currentRunDate, $frequency);

        $stmt = $db->prepare("
            UPDATE recurring_invoices
            SET cycles_generated = ?,
                last_invoice_id = ?,
                last_run_date = ?,
                next_run_date = ?,
                status = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $newCyclesGenerated,
            $invoiceId,
            $currentRunDate,
            $nextRunDate,
            $completed ? 'completed' : 'active',
            $recurringId,
        ]);
    }

    public static function nextRunDate(string $date, string $frequency): string
    {
        $dt = new \DateTime($date);

        switch ($frequency) {
            case 'weekly':
                $dt->modify('+1 week');
                break;
            case 'quarterly':
                $dt->modify('+3 months');
                break;
            case 'yearly':
                $dt->modify('+1 year');
                break;
            case 'monthly':
            default:
                $dt->modify('+1 month');
                break;
        }

        return $dt->format('Y-m-d');
    }

    private static function ensureSchema(): void
    {
        $db = Database::getInstance();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `recurring_invoices` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `source_invoice_id` INT NULL,
                `last_invoice_id` INT NULL,
                `client_id` INT NOT NULL,
                `frequency` ENUM('weekly', 'monthly', 'quarterly', 'yearly') NOT NULL DEFAULT 'monthly',
                `status` ENUM('active', 'paused', 'completed') NOT NULL DEFAULT 'active',
                `start_date` DATE NOT NULL,
                `next_run_date` DATE NULL,
                `last_run_date` DATE NULL,
                `due_days` INT NOT NULL DEFAULT 30,
                `remaining_cycles` INT NULL,
                `cycles_generated` INT NOT NULL DEFAULT 0,
                `subtotal` DECIMAL(15,2) NOT NULL,
                `tax` DECIMAL(15,2) NOT NULL,
                `total` DECIMAL(15,2) NOT NULL,
                `currency` VARCHAR(10) DEFAULT 'CLP',
                `exchange_rate` DECIMAL(15,4) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS `recurring_invoice_items` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `recurring_invoice_id` INT NOT NULL,
                `product_id` INT NOT NULL,
                `qty` INT NOT NULL,
                `price` DECIMAL(15,2) NOT NULL,
                `original_price` DECIMAL(15,2) NULL,
                `original_currency` VARCHAR(10) NULL,
                `exchange_rate` DECIMAL(15,4) DEFAULT 1,
                `total` DECIMAL(15,2) NOT NULL
            ) ENGINE=InnoDB
        ");
    }
}
