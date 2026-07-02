<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\App;
use App\Core\App as Application;
use App\Core\Session;

// Configuração da aplicação
date_default_timezone_set(App::TIMEZONE);

// Inicializa a sessão
Session::start();

// Inicializa a aplicação
$app = new Application();

$app->run();