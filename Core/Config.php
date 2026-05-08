<?php

namespace Core;

class Config
{
    // Global configuration helper
    private static $settings = [];

    public static function load($settings)
    {
        self::$settings = $settings;
    }

    public static function get($key, $default = null)
    {
        if (strpos($key, '.') !== false) {
            $segments = explode('.', $key);
            $value = self::$settings;
            foreach ($segments as $segment) {
                if (!is_array($value) || !array_key_exists($segment, $value)) {
                    return $default;
                }
                $value = $value[$segment];
            }
            return $value;
        }
        return self::$settings[$key] ?? $default;
    }

    /**
     * Get all settings from the database as an associative array.
     * Used by pages that need the full settings context (company, email, product settings).
     */
    public static function getAll()
    {
        try {
            $db = Database::getInstance();
            $rows = $db->query("SELECT `key`, `value` FROM settings")->fetchAll(\PDO::FETCH_ASSOC);
            $result = [];
            foreach ($rows as $row) {
                $result[$row['key']] = $row['value'];
            }
            return $result;
        } catch (\Exception $e) {
            Logger::error("Config::getAll() failed: " . $e->getMessage());
            return [];
        }
    }
}
