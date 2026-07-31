<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Env;
use App\Console\Kernel;

Env::load(__DIR__ . '/.env');

Config::load();

date_default_timezone_set(Config::get('app.timezone', 'America/Fortaleza'));

$kernel = new Kernel();

$kernel->handle($argv);