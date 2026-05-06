<?php

namespace Core;

class View
{
    public static function render($name, $data = [])
    {
        extract($data);
        $contentFile = ROOT_PATH . "/app/views/{$name}.php";
        
        if (file_exists($contentFile)) {
            require_once ROOT_PATH . "/app/views/layout.php";
        } else {
            throw new \Exception("View {$name} not found.");
        }
    }

    public static function renderRaw($rawContent, $data = [])
    {
        extract($data);
        // Ensure cache dir exists
        $cacheDir = ROOT_PATH . '/storage/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $tmpFile = $cacheDir . '/_raw_view.php';
        file_put_contents($tmpFile, $rawContent);
        $contentFile = $tmpFile;
        require_once ROOT_PATH . "/app/views/layout.php";
    }
}
