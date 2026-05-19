<?php

namespace Core;

class View
{
    public static function render($name, $data = [])
    {
        extract($data);
        
        // Robust path detection
        if (strpos($name, 'app/views') !== false || strpos($name, ':') !== false || str_starts_with($name, '/')) {
            $contentFile = $name;
            if (!str_ends_with($contentFile, '.php')) $contentFile .= '.php';
        } else {
            $contentFile = ROOT_PATH . "/app/views/{$name}.php";
        }

        if (file_exists($contentFile)) {
            require_once ROOT_PATH . "/app/views/layout.php";
        } else {
            // Last resort for legacy paths
            $legacyPath = ROOT_PATH . '/' . ltrim($name, '/') . '.php';
            if (file_exists($legacyPath)) {
                $contentFile = $legacyPath;
                require_once ROOT_PATH . "/app/views/layout.php";
            } else {
                throw new \Exception("View {$name} not found. Path checked: {$contentFile}");
            }
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

        // Use full-width layout if specified, otherwise default layout
        $layout = $data['_layout'] ?? 'layout';
        require_once ROOT_PATH . "/app/views/{$layout}.php";
    }

    public static function renderSettings($name, $data = [])
    {
        extract($data);
        $contentFile = ROOT_PATH . "/app/views/{$name}.php";

        if (file_exists($contentFile)) {
            require_once ROOT_PATH . "/app/views/settings_layout.php";
        } else {
            throw new \Exception("View {$name} not found.");
        }
    }
}