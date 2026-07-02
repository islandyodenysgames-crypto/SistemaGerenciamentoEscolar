<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\MigrateCommand;

class Kernel
{
    public function handle(array $argv): void
    {
        $command = $argv[1] ?? '';

        switch ($command) {

            case 'migrate':

                (new MigrateCommand())->handle();

                break;

            default:

                echo PHP_EOL;

                echo "Sistema de Frequência Escolar" . PHP_EOL;

                echo PHP_EOL;

                echo "Comandos disponíveis:" . PHP_EOL;

                echo PHP_EOL;

                echo "php console.php migrate" . PHP_EOL;

                echo PHP_EOL;

        }
    }
}