<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();

        if (!$this->hasColumn('school_classes', 'photo_path')) {
            $db->exec("ALTER TABLE school_classes ADD COLUMN photo_path VARCHAR(500) NULL AFTER active");
        }

        if (!$this->hasColumn('school_classes', 'photo_updated_at')) {
            $db->exec("ALTER TABLE school_classes ADD COLUMN photo_updated_at DATETIME NULL AFTER photo_path");
        }
    }

    public function down(): void
    {
        $db = Connection::getInstance();

        if ($this->hasColumn('school_classes', 'photo_updated_at')) {
            $db->exec("ALTER TABLE school_classes DROP COLUMN photo_updated_at");
        }

        if ($this->hasColumn('school_classes', 'photo_path')) {
            $db->exec("ALTER TABLE school_classes DROP COLUMN photo_path");
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        $stmt = Connection::getInstance()->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name AND COLUMN_NAME = :column_name'
        );
        $stmt->execute(['table_name' => $table, 'column_name' => $column]);

        return (int) $stmt->fetchColumn() > 0;
    }
};
