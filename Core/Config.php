<?php

namespace Core;

class Config
{
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
}
