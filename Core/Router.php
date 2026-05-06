<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function add(string $path, callable $handler, string $method = 'GET')
    {
        $this->routes[] = [
            'path' => trim($path, '/'),
            'method' => strtoupper($method),
            'handler' => $handler,
        ];
    }

    public function dispatch(string $path, string $method = 'GET')
    {
        $path = trim($path, '/');
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['path'] === $path && $route['method'] === $method) {
                return call_user_func($route['handler']);
            }
        }

        http_response_code(404);
        throw new \Exception('Ruta no encontrada', 404);
    }
}
