<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\App;
use App\Core\App as Application;

// Configura o fuso horário da aplicação
date_default_timezone_set(App::TIMEZONE);

// Inicializa a aplicação
$app = new Application();

$app->run();