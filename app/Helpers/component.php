<?php

declare(strict_types=1);

if (!function_exists('component')) {

    function component(string $componentName, array $data = []): void
    {
        $basePath = dirname(__DIR__) . '/Views/components/';

        $component = $basePath . str_replace(
            ['\\', '.'],
            ['/', ''],
            $componentName
        ) . '.php';

        if (!file_exists($component)) {
            echo "<!-- COMPONENTE NÃO ENCONTRADO: {$componentName} -->";
            return;
        }

        extract($data, EXTR_SKIP);

        require $component;
    }

}