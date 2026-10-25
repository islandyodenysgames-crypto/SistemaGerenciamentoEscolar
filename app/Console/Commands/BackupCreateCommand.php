<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SchoolDataMaintenanceService;

final class BackupCreateCommand
{
    public function handle(): void
    {
        $path = (new SchoolDataMaintenanceService())->createBackup(null, 'cli-local', 'scheduled-external');
        fwrite(STDOUT, $path . PHP_EOL);
    }
}
