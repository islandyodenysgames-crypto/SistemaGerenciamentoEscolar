<?php

declare(strict_types=1);

use App\Controllers\AttendanceController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EnrollmentController;
use App\Controllers\ReportController;
use App\Controllers\SchoolClassController;
use App\Controllers\StudentController;
use App\Controllers\UserController;

/** @var \App\Core\Router $router */

// ======================================================
// Dashboard
// ======================================================

$router->get('/', [DashboardController::class, 'index']);


// ======================================================
// Autenticação
// ======================================================

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->get('/logout', [AuthController::class, 'logout']);


// ======================================================
// Usuários
// ======================================================

$router->get('/usuarios', [UserController::class, 'index']);
$router->get('/usuarios/novo', [UserController::class, 'create']);
$router->post('/usuarios', [UserController::class, 'store']);
$router->get('/usuarios/editar', [UserController::class, 'edit']);
$router->post('/usuarios/editar', [UserController::class, 'update']);
$router->post('/usuarios/excluir', [UserController::class, 'delete']);


// ======================================================
// Turmas
// ======================================================

$router->get('/turmas', [SchoolClassController::class, 'index']);
$router->get('/turmas/novo', [SchoolClassController::class, 'create']);
$router->post('/turmas', [SchoolClassController::class, 'store']);
$router->get('/turmas/editar', [SchoolClassController::class, 'edit']);
$router->post('/turmas/editar', [SchoolClassController::class, 'update']);
$router->post('/turmas/excluir', [SchoolClassController::class, 'delete']);


// ======================================================
// Alunos
// ======================================================

$router->get('/alunos', [StudentController::class, 'index']);
$router->get('/alunos/novo', [StudentController::class, 'create']);
$router->post('/alunos', [StudentController::class, 'store']);
$router->get('/alunos/editar', [StudentController::class, 'edit']);
$router->post('/alunos/editar', [StudentController::class, 'update']);
$router->post('/alunos/excluir', [StudentController::class, 'delete']);


// ======================================================
// Matrículas
// ======================================================

$router->get('/matriculas', [EnrollmentController::class, 'index']);
$router->get('/matriculas/novo', [EnrollmentController::class, 'create']);
$router->post('/matriculas', [EnrollmentController::class, 'store']);
$router->post('/matriculas/cancelar', [EnrollmentController::class, 'cancel']);


// ======================================================
// Frequência
// ======================================================

$router->get('/frequencia', [AttendanceController::class, 'index']);
$router->get('/frequencia/novo', [AttendanceController::class, 'create']);
$router->post('/frequencia', [AttendanceController::class, 'store']);
$router->get('/frequencia/ver', [AttendanceController::class, 'show']);


// ======================================================
// Relatórios
// ======================================================

$router->get('/relatorios', [ReportController::class, 'index']);
$router->get('/relatorios/diario', [ReportController::class, 'daily']);