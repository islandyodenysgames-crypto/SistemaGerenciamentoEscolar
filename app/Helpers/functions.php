<?php

declare(strict_types=1);

use App\Config\App;
use App\Core\Component;

if (!function_exists('base_url')) {

    function base_url(string $path = ''): string
    {
        return rtrim(App::BASE_URL, '/') . '/' . ltrim($path, '/');
    }

}

if (!function_exists('app_name')) {

    function app_name(): string
    {
        return App::NAME;
    }

}

if (!function_exists('app_version')) {

    function app_version(): string
    {
        return App::VERSION;
    }

}

if (!function_exists('component')) {

    function component(string $name, array $data = []): void
    {
        Component::render($name, $data);
    }

}