<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\UserController;

/** @var \App\Core\Router $router */

$router->get('/', [DashboardController::class, 'index']);

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/usuarios', [UserController::class, 'index']);
$router->get('/usuarios/novo', [UserController::class, 'create']);
$router->post('/usuarios', [UserController::class, 'store']);

$router->get('/usuarios/editar', [UserController::class, 'edit']);
$router->post('/usuarios/editar', [UserController::class, 'update']);
$router->post('/usuarios/excluir', [UserController::class, 'delete']);