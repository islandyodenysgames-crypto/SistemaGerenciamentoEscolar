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
            CREATE TABLE IF NOT EXISTS subjects (
                id BIGINT UNSIGNED
                    AUTO_INCREMENT
                    PRIMARY KEY,

                name VARCHAR(120)
                    NOT NULL,

                code VARCHAR(30)
                    NOT NULL,

                description TEXT
                    NULL,

                active TINYINT(1)
                    NOT NULL
                    DEFAULT 1,

                created_at TIMESTAMP
                    NULL
                    DEFAULT CURRENT_TIMESTAMP,

                updated_at TIMESTAMP
                    NULL
                    DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,

                UNIQUE KEY uk_subjects_name (
                    name
                ),

                UNIQUE KEY uk_subjects_code (
                    code
                )
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");

        $stmt = $db->prepare("
            INSERT INTO subjects (
                name,
                code,
                description,
                active
            )
            SELECT
                :name,
                :code,
                :description,
                1

            WHERE NOT EXISTS (
                SELECT 1

                FROM subjects

                WHERE code = :existing_code
            )
        ");

        $stmt->execute([
            'name' =>
                'Geral da Escola',

            'code' =>
                'GERAL',

            'description' =>
                'Ocorrências não relacionadas a uma disciplina específica.',

            'existing_code' =>
                'GERAL',
        ]);
    }

    public function down(): void
    {
        $db = Connection::getInstance();

        $db->exec("
            DROP TABLE IF EXISTS subjects
        ");
    }
};