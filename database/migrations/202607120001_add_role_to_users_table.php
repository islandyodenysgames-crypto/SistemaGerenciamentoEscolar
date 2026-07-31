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
            ALTER TABLE users

            ADD COLUMN role VARCHAR(30)
            NOT NULL
            DEFAULT 'ADMIN'
            AFTER email
        ");
    }

    public function down(): void
    {
        $db = Connection::getInstance();

        $db->exec("
            ALTER TABLE users
            DROP COLUMN role
        ");
    }
};