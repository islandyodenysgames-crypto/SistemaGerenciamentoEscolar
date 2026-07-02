<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Database\MigrationRunner;

class MigrateCommand
{
    public function handle(): void
    {
        echo PHP_EOL;

        echo "======================================" . PHP_EOL;

        echo " Sistema de Frequência Escolar" . PHP_EOL;

        echo " Executando migrações" . PHP_EOL;

        echo "======================================" . PHP_EOL;

        echo PHP_EOL;

        $runner = new MigrationRunner();

        $files = $runner->getMigrationFiles();

        if (empty($files)) {

            echo "Nenhuma migração encontrada." . PHP_EOL;

            echo PHP_EOL;

            return;

        }

        echo "Migrações encontradas:" . PHP_EOL;

        echo PHP_EOL;

        foreach ($files as $file) {

            echo " • " . basename($file) . PHP_EOL;

        }

        echo PHP_EOL;
    }
}