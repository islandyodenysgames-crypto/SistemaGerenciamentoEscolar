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
            ALTER TABLE occurrence_actions

            ADD COLUMN action_type VARCHAR(40)
                NOT NULL
                DEFAULT 'OTHER'
                AFTER occurrence_id
        ");
    }

    public function down(): void
    {
        $db = Connection::getInstance();

        $db->exec("
            ALTER TABLE occurrence_actions

            DROP COLUMN action_type
        ");
    }
};