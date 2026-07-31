<?php

declare(strict_types=1);

use App\Database\Migration;
use App\Database\Connection;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();

        $columns = [
            'inep_code' => "ALTER TABLE schools ADD COLUMN inep_code VARCHAR(30) NULL",
            'vice_principal' => "ALTER TABLE schools ADD COLUMN vice_principal VARCHAR(120) NULL",
            'zip_code' => "ALTER TABLE schools ADD COLUMN zip_code VARCHAR(20) NULL",
            'instagram' => "ALTER TABLE schools ADD COLUMN instagram VARCHAR(180) NULL",
            'facebook' => "ALTER TABLE schools ADD COLUMN facebook VARCHAR(180) NULL",
            'youtube' => "ALTER TABLE schools ADD COLUMN youtube VARCHAR(180) NULL",
        ];

        foreach ($columns as $column => $sql) {
            $exists = $db->query("SHOW COLUMNS FROM schools LIKE '{$column}'")
                ->fetch();

            if (!$exists) {
                $db->exec($sql);
            }
        }
    }

    public function down(): void
    {
    }
};