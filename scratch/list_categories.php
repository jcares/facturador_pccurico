<?php
require_once 'bootstrap/app.php';
use Core\Database;

try {
    $db = Database::getInstance();
    $rows = $db->query('SELECT * FROM categories')->fetchAll();
    foreach($rows as $row) {
        echo $row['id'] . ': ' . $row['name'] . ' (parent: ' . ($row['parent_id'] ?? 'NULL') . ')' . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
