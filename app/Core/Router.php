<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $uri, callable|array $action): void
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->normalize(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        $base = '/SistemaFrequenciaEscolar/public';

        if (str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = $this->normalize($uri);

        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);
            echo "Página não encontrada";
            return;
        }

        $action = $this->routes[$method][$uri];

        if (is_callable($action)) {
            $action();
            return;
        }

        [$controller, $method] = $action;

        $instance = new $controller();

        $instance->$method();
    }

    private function normalize(string $uri): string
    {
        if ($uri === '') {
            return '/';
        }

        return '/' . trim($uri, '/');
    }
}