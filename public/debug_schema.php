<?php
require_once __DIR__ . '/../bootstrap/app.php';
try {
    $db = \Core\Database::getInstance();
    $tables = ['products', 'categories', 'invoices', 'invoice_items', 'exchange_rates'];
    foreach ($tables as $t) {
        echo "<h3>Table: $t</h3>";
        try {
            $stmt = $db->query("DESCRIBE `$t` ");
            echo "<pre>";
            print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
            echo "</pre>";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
