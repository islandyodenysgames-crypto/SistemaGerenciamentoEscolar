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

    public function post(string $uri, callable|array $action): void
    {
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    public function dispatch(): void
    {
        $method = Request::method();

        $uri = Request::uri();

        $baseUrl = Config::get('app.url', '');

        $basePath = parse_url($baseUrl, PHP_URL_PATH) ?: '';

        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = $this->normalize($uri);

        if (!isset($this->routes[$method][$uri])) {
            http_response_code(404);

            echo '<h1>404</h1>';
            echo '<p>Página não encontrada.</p>';

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