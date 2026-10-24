<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\AuthenticatedSessionService;

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

        $this->container->get(AuthenticatedSessionService::class)->revalidateIfDue();

        if ($method === 'POST' && !Csrf::validate(Csrf::requestToken())) {
            $this->rejectInvalidCsrf($uri);
            return;
        }

        if (!isset($this->routes[$method][$uri])) {
            $this->notFound();
            return;
        }

        $action = $this->routes[$method][$uri];

        if (is_callable($action)) {
            $action();
            return;
        }

        [$controller, $controllerMethod] = $action;
        $instance = $this->container->get($controller);
        $instance->$controllerMethod();
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

    private function rejectInvalidCsrf(string $uri): void
    {
        Csrf::rotate();
        http_response_code(419);
        if (Request::expectsJson()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'code' => 'CSRF_TOKEN_MISMATCH', 'message' => 'A sessão do formulário expirou. Atualize a página e tente novamente.']);
            return;
        }
        Session::set('csrf_error', 'A sessão do formulário expirou. Atualize a página e tente novamente.');
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $appUrl = (string) Config::get('app.url', '/');
        $target = $uri === '/login' ? base_url('login') : base_url();
        if ($referer !== '' && self::isSafeLocalUrl($referer, $appUrl)) $target = $referer;
        header('Location: ' . $target);
    }

    private static function isSafeLocalUrl(string $url, string $appUrl): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        return $host === null || $host === false || ($appHost !== null && strcasecmp((string) $host, (string) $appHost) === 0);
    }
}
