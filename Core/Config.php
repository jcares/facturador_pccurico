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
        return self::$settings[$key] ?? $default;
    }
}
