<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    public static function render(
        string $view,
        array $data = [],
        string $layout = 'app'
    ): void {
        extract($data);

        ob_start();

        require dirname(__DIR__) . "/Views/{$view}.php";

        $content = ob_get_clean();

        require dirname(__DIR__) . "/Views/layouts/{$layout}.php";
    }
}