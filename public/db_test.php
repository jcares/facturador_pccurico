<?php
session_start();
require_once __DIR__ . '/../bootstrap/app.php';

if (!\Core\Auth::check()) {
    header('Location: login.php');
    exit;
}

try {
    $db = \Core\Database::getInstance();
    echo "Connection OK\n";
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . htmlspecialchars(implode(", ", $tables), ENT_QUOTES, 'UTF-8') . "\n";
    echo "Users count: " . (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n";
} catch (Exception $e) {
    http_response_code(500);
    echo "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "\n";
}
