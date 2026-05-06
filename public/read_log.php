<?php
$log = realpath(__DIR__ . '/../storage/logs/error.log');
if ($log && file_exists($log)) {
    echo "Log found at: " . $log . "<br>";
    echo nl2br(htmlspecialchars(file_get_contents($log)));
} else {
    echo "Log file NOT found. Checked: " . __DIR__ . '/../storage/logs/error.log';
    echo "<br>Base Dir: " . __DIR__;
}
