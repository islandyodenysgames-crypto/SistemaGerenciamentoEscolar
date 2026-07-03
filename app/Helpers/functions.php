<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Nome da aplicação
|--------------------------------------------------------------------------
*/

if (!function_exists('app_name')) {

    function app_name(): string
    {
        return 'Sistema de Frequência Escolar';
    }

}

/*
|--------------------------------------------------------------------------
| Views
|--------------------------------------------------------------------------
*/

if (!function_exists('view')) {

    function view(string $view, array $data = []): void
    {
        extract($data);

        require dirname(__DIR__) . '/Views/' . $view . '.php';
    }

}

/*
|--------------------------------------------------------------------------
| Componentes
|--------------------------------------------------------------------------
*/

if (!function_exists('component')) {

    function component(string $name, array $data = []): void
    {
        $basePath = dirname(__DIR__) . '/Views/components/';

        $component = $basePath . str_replace('\\', '/', $name) . '.php';

        if (!file_exists($component)) {

            echo "<!-- COMPONENTE NÃO ENCONTRADO: {$name} -->";

            return;
        }

        extract($data);

        require $component;
    }

}

/*
|--------------------------------------------------------------------------
| Redirecionamento
|--------------------------------------------------------------------------
*/

if (!function_exists('redirect')) {

    function redirect(string $path): never
    {
        header('Location: ' . base_url($path));
        exit;
    }

}

/*
|--------------------------------------------------------------------------
| URL Base
|--------------------------------------------------------------------------
*/

if (!function_exists('base_url')) {

    function base_url(string $path = ''): string
    {
        $base = '/SistemaFrequenciaEscolar/public';

        return $base . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }

}

/*
|--------------------------------------------------------------------------
| Assets
|--------------------------------------------------------------------------
*/

if (!function_exists('asset')) {

    function asset(string $path): string
    {
        return base_url($path);
    }

}

/*
|--------------------------------------------------------------------------
| Formulários
|--------------------------------------------------------------------------
*/

if (!function_exists('old')) {

    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['old'][$key] ?? $default;
    }

}

if (!function_exists('selected')) {

    function selected(mixed $value, mixed $current): string
    {
        return $value == $current ? 'selected' : '';
    }

}

if (!function_exists('checked')) {

    function checked(mixed $value, mixed $current): string
    {
        return $value == $current ? 'checked' : '';
    }

}

/*
|--------------------------------------------------------------------------
| Flash Messages
|--------------------------------------------------------------------------
*/

if (!function_exists('flash')) {

    function flash(string $key): mixed
    {
        if (!isset($_SESSION[$key])) {
            return null;
        }

        $value = $_SESSION[$key];

        unset($_SESSION[$key]);

        return $value;
    }

}

/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

if (!function_exists('e')) {

    function e(?string $value): string
    {
        return htmlspecialchars(
            $value ?? '',
            ENT_QUOTES,
            'UTF-8'
        );
    }

}

/*
|--------------------------------------------------------------------------
| Menu Ativo
|--------------------------------------------------------------------------
*/

if (!function_exists('is_active')) {

    function is_active(string $route): string
    {
        $uri = parse_url(
            $_SERVER['REQUEST_URI'],
            PHP_URL_PATH
        );

        return str_contains($uri, $route)
            ? 'active'
            : '';
    }

}