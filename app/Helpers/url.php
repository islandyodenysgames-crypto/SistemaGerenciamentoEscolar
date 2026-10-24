<?php

declare(strict_types=1);

if (!function_exists('base_url')) {

    function base_url(string $path = ''): string
    {
        $base = rtrim((string) \App\Core\Config::get('app.url', '/'), '/');
        if ($base === '') {
            $base = '/';
        }

        if ($path === '') {
            return $base;
        }

        return ($base === '/' ? '' : rtrim($base, '/')) . '/' . ltrim($path, '/');
    }

}

if (!function_exists('redirect')) {

    function redirect(string $path): never
    {
        header('Location: ' . base_url($path));
        exit;
    }

}
