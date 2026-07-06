<?php

declare(strict_types=1);

if (!function_exists('component')) {

    function component(string $name, array $data = []): void
    {
        $basePath = dirname(__DIR__) . '/Views/components/';

        $component = $basePath . str_replace(
            ['\\', '.'],
            ['/', ''],
            $name
        ) . '.php';

        if (!file_exists($component)) {
            echo "<!-- COMPONENTE NÃO ENCONTRADO: {$name} -->";
            return;
        }

        extract($data, EXTR_SKIP);

        require $component;
    }

}