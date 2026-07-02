<?php

declare(strict_types=1);

namespace App\Core;

class Component
{
    public static function render(string $component, array $data = []): void
    {
        extract($data);

        require dirname(__DIR__) . "/Views/components/{$component}.php";
    }
}