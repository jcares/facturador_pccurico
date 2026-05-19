<?php

namespace Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static $instance = null;
    private $connection;

    /**
     * Build a MySQL PDO DSN from config (used by the app and install wizard).
     * Supports host, optional port, optional unix_socket, charset.
     *
     * @param array{host?:string,name?:string,user?:string,pass?:string,port?:int|null,charset?:string,socket?:string} $config
     */
    public static function dsnFromConfig(array $config): string
    {
        $charset = isset($config['charset']) && (string) $config['charset'] !== ''
            ? (string) $config['charset']
            : 'utf8mb4';

        $name = (string) ($config['name'] ?? '');

        if (!empty($config['socket'])) {
            return sprintf(
                'mysql:unix_socket=%s;dbname=%s;charset=%s',
                $config['socket'],
                $name,
                $charset
            );
        }

        $host = (string) ($config['host'] ?? 'localhost');
        $dsn = 'mysql:host=' . $host;

        $port = isset($config['port']) ? (int) $config['port'] : 0;
        if ($port > 0) {
            $dsn .= ';port=' . $port;
        }

        $dsn .= ';dbname=' . $name . ';charset=' . $charset;

        return $dsn;
    }

    private function __construct()
    {
        $config = Config::get('database');

        if (!$config || !is_array($config)) {
            throw new Exception('Database configuration not found.');
        }

        $name = trim((string) ($config['name'] ?? ''));
        $user = trim((string) ($config['user'] ?? ''));
        if ($name === '' || $user === '') {
            throw new Exception(
                'Database name and user must be set. Configure DB_NAME and DB_USER in .env or config/database.php.'
            );
        }

        $pass = (string) ($config['pass'] ?? '');
        $dsn = self::dsnFromConfig($config);

        try {
            $this->connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30,
            ]);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
}
