<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\MigrateCommand;
use App\Console\Commands\SeedCommand;
use App\Console\Commands\IntelligenceSnapshotCommand;

class Kernel
{
    public function handle(array $argv): void
    {
        $command = $argv[1] ?? '';

        switch ($command) {
            case 'migrate':
                (new MigrateCommand())->handle();
                break;

            case 'seed':
                (new SeedCommand())->handle();
                break;

            case 'intelligence:snapshot':
                (new IntelligenceSnapshotCommand())->handle($argv);
                break;

            default:
                echo PHP_EOL;
                echo "Sistema de Frequência Escolar" . PHP_EOL;
                echo PHP_EOL;
                echo "Comandos disponíveis:" . PHP_EOL;
                echo "php console.php migrate" . PHP_EOL;
                echo "php console.php seed" . PHP_EOL;
                echo "php console.php intelligence:snapshot" . PHP_EOL;
                echo "php console.php intelligence:snapshot --force" . PHP_EOL;
                echo PHP_EOL;
        }
    }
}