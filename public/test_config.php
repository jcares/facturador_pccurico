<?php
require_once '../bootstrap/app.php';
header('Content-Type: text/plain');

if (method_exists('\Core\Config', 'getAll')) {
    echo "OK: getAll() exists\n";
    print_r(\Core\Config::getAll());
} else {
    echo "ERROR: getAll() does NOT exist in \Core\Config\n";
    $rc = new ReflectionClass('\Core\Config');
    echo "File: " . $rc->getFileName() . "\n";
    echo "Methods: " . implode(', ', array_map(fn($m) => $m->name, $rc->getMethods())) . "\n";
}
