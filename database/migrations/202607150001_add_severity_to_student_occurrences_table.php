<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();

        $db->exec("
            ALTER TABLE student_occurrences

            ADD COLUMN severity VARCHAR(20)
                NOT NULL
                DEFAULT 'LOW'
                AFTER type
        ");
    }

    public function down(): void
    {
        $db = Connection::getInstance();

        $db->exec("
            ALTER TABLE student_occurrences

            DROP COLUMN severity
        ");
    }
};