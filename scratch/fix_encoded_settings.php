<?php
require_once 'bootstrap/app.php';
use Core\Database;

try {
    $db = Database::getInstance();
    $rows = $db->query("SELECT * FROM settings")->fetchAll();
    
    $db->beginTransaction();
    $stmt = $db->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?");
    
    $fixedCount = 0;
    foreach ($rows as $row) {
        $key = $row['key'];
        $originalValue = $row['value'];
        $decodedValue = html_entity_decode($originalValue, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        if ($originalValue !== $decodedValue) {
            echo "Fixing setting '{$key}': '{$originalValue}' -> '{$decodedValue}'\n";
            $stmt->execute([$decodedValue, $key]);
            $fixedCount++;
        }
    }
    
    $db->commit();
    echo "\nTotal settings fixed: $fixedCount\n";
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
