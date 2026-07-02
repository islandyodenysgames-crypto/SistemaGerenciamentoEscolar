<?php

declare(strict_types=1);

namespace App\Database;

use PDO;

class MigrationRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function createTableIfNotExists(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function hasRun(string $migration): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM migrations WHERE migration = :migration"
        );

        $stmt->execute([
            'migration' => $migration
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function log(string $migration): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO migrations (migration) VALUES (:migration)"
        );

        $stmt->execute([
            'migration' => $migration
        ]);
    }
}