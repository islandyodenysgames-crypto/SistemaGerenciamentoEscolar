<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Database\Connection;
use App\Database\MigrationRepository;

class MigrateCommand
{
    public function handle(): void
    {
        $path = dirname(__DIR__, 3) . '/database/migrations';

        $files = glob($path . '/*.php');

        sort($files);

        $db = Connection::getInstance();

        $repository = new MigrationRepository($db);

        $repository->createTableIfNotExists();

        if (empty($files)) {
            echo "Nenhuma migração encontrada." . PHP_EOL;
            return;
        }

        foreach ($files as $file) {
            $migrationName = basename($file);

            if ($repository->hasRun($migrationName)) {
                echo "Ignorada: {$migrationName}" . PHP_EOL;
                continue;
            }

            $migration = require $file;

            $migration->up();

            $repository->log($migrationName);

            echo "Executada: {$migrationName}" . PHP_EOL;
        }
    }
}