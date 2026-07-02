<?php

declare(strict_types=1);

namespace App\Core;

use App\Config\App;

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

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $base = rtrim(App::BASE_URL, '/');

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = $this->normalize($uri);

        if (!isset($this->routes[$method][$uri])) {

            http_response_code(404);

            echo "<h1>404</h1>";

            echo "<p>Página não encontrada.</p>";

            return;
        }

        $action = $this->routes[$method][$uri];

        if (is_callable($action)) {

            $action();

            return;
        }

        [$controller, $controllerMethod] = $action;

        $instance = new $controller();

        $instance->$controllerMethod();
    }

    private function normalize(string $uri): string
    {
        if ($uri === '' || $uri === '/') {
            return '/';
        }

        return '/' . trim($uri, '/');
    }
}