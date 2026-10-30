<?php

declare(strict_types=1);

use App\Database\Migration;
use App\Database\Schema;
use App\Database\Connection;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();
        if (!Schema::hasColumn('student_occurrences', 'nature')) {
            $db->exec("
                ALTER TABLE student_occurrences
                ADD COLUMN nature VARCHAR(20) NOT NULL DEFAULT 'ADVERSE' AFTER type,
                ADD COLUMN focus_area VARCHAR(30) NULL AFTER nature,
                ADD INDEX idx_occurrence_nature_focus_date (nature, focus_area, occurrence_date)
            ");
        }

        $db->exec("
            UPDATE student_occurrences
            SET nature = 'POSITIVE', focus_area = NULL,
                severity = 'NOT_APPLICABLE', status = 'RESOLVED'
            WHERE type = 'PRAISE'
        ");

        // Registros antigos de encaminhamento permanecem adversos. Um texto
        // como "Infrequência" não comprova, sozinho, que uma providência foi
        // efetivamente realizada pela escola.
    }

    public function down(): void
    {
        $db = Connection::getInstance();
        if (Schema::hasColumn('student_occurrences', 'nature')) {
            $db->exec("
                UPDATE student_occurrences
                SET severity = 'LOW'
                WHERE nature = 'RESPONSE' AND severity = 'NOT_APPLICABLE'
            ");
            $db->exec("
                ALTER TABLE student_occurrences
                DROP INDEX idx_occurrence_nature_focus_date,
                DROP COLUMN focus_area,
                DROP COLUMN nature
            ");
        }
    }
};
