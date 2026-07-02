<?php

declare(strict_types=1);

namespace App\Core;

class App
{
    public function run(): void
    {
        $router = new Router();

        require dirname(__DIR__, 2) . '/routes/web.php';

        $router->dispatch();
    }
}