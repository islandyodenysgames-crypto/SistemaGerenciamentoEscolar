<?php

declare(strict_types=1);

if (!function_exists('view')) {

    function view(string $view, array $data = []): void
    {
        extract($data);

        require dirname(__DIR__) . '/Views/' . $view . '.php';
    }

}