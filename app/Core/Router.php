<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class Router
{
    private array $routes = [];

    private Container $container;

    public function __construct(?Container $container = null)
    {
        $this->container = $container ?? new Container();

        AppServiceProvider::register($this->container);
    }

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

        if (
            $basePath !== ''
            && str_starts_with($uri, $basePath)
        ) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = $this->normalize($uri);

        if (!isset($this->routes[$method][$uri])) {
            $this->notFound();
            return;
        }

        $action = $this->routes[$method][$uri];

        try {

            if (is_callable($action)) {
                $action();
                return;
            }

            [$controller, $controllerMethod] = $action;

            $instance = $this->container->get($controller);

            $instance->$controllerMethod();

        } catch (Throwable $e) {

            throw $e;

            /*
             * Futuramente:
             *
             * ErrorHandler::render($e);
             */

        }
    }

    private function normalize(string $uri): string
    {
        if ($uri === '' || $uri === '/') {
            return '/';
        }

        return '/' . trim($uri, '/');
    }

    private function notFound(): void
    {
        http_response_code(404);

        echo '<h1>404</h1>';
        echo '<p>Página não encontrada.</p>';
    }
}