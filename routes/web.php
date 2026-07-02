<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;

/** @var \App\Core\Router $router */

$router->get('/', [DashboardController::class, 'index']);

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);

$router->get('/logout', [AuthController::class, 'logout']);