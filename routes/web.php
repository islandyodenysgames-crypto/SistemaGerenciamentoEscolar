<?php

declare(strict_types=1);

use App\Controllers\DashboardController;

/** @var \App\Core\Router $router */

$router->get('/', [DashboardController::class, 'index']);