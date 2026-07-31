<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        $activeId = $db->query('SELECT id FROM school_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1')->fetchColumn();

        if ($activeId !== false) {
            $statement = $db->prepare(<<<'SQL'
UPDATE school_years
SET is_active = 0,
    status = CASE WHEN status = 'ACTIVE' THEN 'PREPARATION' ELSE status END,
    updated_at = NOW()
WHERE is_active = 1 AND id <> :active_id
SQL);
            $statement->execute(['active_id' => (int) $activeId]);
        }

        $db->exec(<<<'SQL'
ALTER TABLE school_years
    ADD COLUMN active_guard TINYINT
        GENERATED ALWAYS AS (CASE WHEN is_active = 1 THEN 1 ELSE NULL END) STORED,
    ADD UNIQUE KEY uq_school_years_single_active (active_guard)
SQL);
    }

    public function down(): void
    {
        Connection::getInstance()->exec(<<<'SQL'
ALTER TABLE school_years
    DROP INDEX uq_school_years_single_active,
    DROP COLUMN active_guard
SQL);
    }
};
