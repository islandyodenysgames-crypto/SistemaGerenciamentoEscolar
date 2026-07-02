<?php

declare(strict_types=1);

namespace App\Database;

class MigrationRunner
{
    private string $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__, 2) . '/database/migrations';
    }

    public function getMigrationFiles(): array
    {
        if (!is_dir($this->path)) {
            return [];
        }

        $files = glob($this->path . '/*.php');

        sort($files);

        return $files;
    }
}