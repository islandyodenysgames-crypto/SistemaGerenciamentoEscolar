<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);

        require dirname(__DIR__) . "/Views/{$view}.php";
    }
}