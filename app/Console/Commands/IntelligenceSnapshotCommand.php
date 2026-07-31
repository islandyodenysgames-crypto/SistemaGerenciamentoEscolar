<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Core\AppServiceProvider;
use App\Core\Container;
use App\Services\Intelligence\IntelligenceDailySnapshotService;

final class IntelligenceSnapshotCommand
{
    public function handle(array $argv = []): void
    {
        $force = in_array('--force', $argv, true);
        $container = new Container();
        AppServiceProvider::register($container);

        /** @var IntelligenceDailySnapshotService $service */
        $service = $container->resolve(IntelligenceDailySnapshotService::class);
        $result = $service->captureToday($force);

        echo PHP_EOL;
        echo ($result['message'] ?? 'Processamento concluído.') . PHP_EOL;
        echo 'Data: ' . ($result['date'] ?? date('Y-m-d')) . PHP_EOL;
        echo 'Alunos processados: ' . (int) ($result['students'] ?? 0) . PHP_EOL;
        echo 'Status: ' . ($result['status'] ?? 'UNKNOWN') . PHP_EOL;
        echo PHP_EOL;
    }
}
