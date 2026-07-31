<?php

declare(strict_types=1);

use App\Database\Connection;
use App\Database\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $db = Connection::getInstance();

        /*
         * Remove uma tabela eventualmente criada
         * parcialmente durante uma tentativa anterior.
         */
        $db->exec("
            DROP TABLE IF EXISTS user_subjects
        ");

        $db->exec("
            CREATE TABLE user_subjects (
                id BIGINT
                    AUTO_INCREMENT
                    PRIMARY KEY,

                user_id BIGINT
                    NOT NULL,

                subject_id BIGINT UNSIGNED
                    NOT NULL,

                created_at TIMESTAMP
                    NULL
                    DEFAULT CURRENT_TIMESTAMP,

                updated_at TIMESTAMP
                    NULL
                    DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP,

                UNIQUE KEY uk_user_subjects_user_subject (
                    user_id,
                    subject_id
                ),

                KEY idx_user_subjects_user_id (
                    user_id
                ),

                KEY idx_user_subjects_subject_id (
                    subject_id
                ),

                CONSTRAINT fk_user_subjects_user
                    FOREIGN KEY (
                        user_id
                    )
                    REFERENCES users (
                        id
                    )
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,

                CONSTRAINT fk_user_subjects_subject
                    FOREIGN KEY (
                        subject_id
                    )
                    REFERENCES subjects (
                        id
                    )
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            )
            ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $db = Connection::getInstance();

        $db->exec("
            DROP TABLE IF EXISTS user_subjects
        ");
    }
};