<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App as Application;
use App\Core\Config;
use App\Core\Env;
use App\Core\Session;

Env::load(dirname(__DIR__) . '/.env');

Config::load();

date_default_timezone_set(Config::get('app.timezone', 'America/Fortaleza'));

Session::start();

$app = new Application();

$app->run();