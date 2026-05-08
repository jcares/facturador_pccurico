<?php
require_once 'bootstrap/app.php';
use Core\Database;

try {
    $db = Database::getInstance();
    $rows = $db->query('SELECT id, name, type, is_default FROM document_templates')->fetchAll();
    foreach($rows as $row) {
        echo $row['id'] . ': ' . $row['name'] . ' (type: ' . $row['type'] . ', default: ' . $row['is_default'] . ')' . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
