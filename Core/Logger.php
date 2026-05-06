<?php
namespace Core;

class Logger
{
    private static $logFile;

    public static function init()
    {
        self::$logFile = \ROOT_PATH . '/storage/logs/error.log';
        if (!file_exists(dirname(self::$logFile))) {
            mkdir(dirname(self::$logFile), 0755, true);
        }
        // die("LOG FILE PATH: " . self::$logFile);
    }

    public static function error($message, array $context = [])
    {
        self::log('ERROR', $message, $context);
    }

    public static function info($message, array $context = [])
    {
        self::log('INFO', $message, $context);
    }

    private static function log($level, $message, array $context = [])
    {
        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logMessage = "[{$date}] {$level}: {$message} {$contextStr}" . PHP_EOL;
        
        @file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
}
