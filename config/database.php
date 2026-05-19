<?php

/**
 * Database connection settings.
 * Loaded after bootstrap parses .env into $_ENV; falls back to getenv() for CLI/cron.
 */
$dbEnv = static function (string $key, string $default = ''): string {
    if (array_key_exists($key, $_ENV)) {
        return (string) $_ENV[$key];
    }
    $v = getenv($key);
    return $v !== false ? (string) $v : $default;
};

$portRaw = trim($dbEnv('DB_PORT', ''));
$port = $portRaw !== '' ? (int) $portRaw : null;
if ($port !== null && $port <= 0) {
    $port = null;
}

$socket = trim($dbEnv('DB_SOCKET', ''));

return [
    'host' => $dbEnv('DB_HOST', 'localhost'),
    'name' => $dbEnv('DB_NAME', ''),
    'user' => $dbEnv('DB_USER', ''),
    'pass' => $dbEnv('DB_PASS', ''),
    'port' => $port,
    'charset' => $dbEnv('DB_CHARSET', 'utf8mb4'),
    'socket' => $socket,
];
