<?php

declare(strict_types=1);

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