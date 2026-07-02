<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App as Application;
use App\Core\Config;
use App\Core\Env;
use App\Core\Session;

// Carrega as variáveis de ambiente
Env::load(dirname(__DIR__) . '/.env');

// Carrega as configurações
Config::load();

// Define o fuso horário
date_default_timezone_set(Config::get('app.timezone', 'America/Sao_Paulo'));

// Inicializa a sessão
Session::start();

// Inicializa a aplicação
$app = new Application();

$app->run();