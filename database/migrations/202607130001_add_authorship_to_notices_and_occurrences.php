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
         * Avisos da gestão.
         *
         * created_by já existe em school_notices.
         * Adicionamos a fotografia do nome e do perfil.
         */
        $db->exec("
            ALTER TABLE school_notices

            ADD COLUMN created_by_name VARCHAR(150)
                NULL
                AFTER created_by,

            ADD COLUMN created_by_role VARCHAR(60)
                NULL
                AFTER created_by_name
        ");

        /*
         * Ocorrências escolares.
         *
         * Adicionamos o usuário responsável e também
         * a fotografia do nome e do perfil no momento
         * do registro.
         */
        $db->exec("
            ALTER TABLE student_occurrences

            ADD COLUMN created_by INT
                NULL
                AFTER status,

            ADD COLUMN created_by_name VARCHAR(150)
                NULL
                AFTER created_by,

            ADD COLUMN created_by_role VARCHAR(60)
                NULL
                AFTER created_by_name,

            ADD INDEX idx_student_occurrences_created_by (
                created_by
            )
        ");
    }

    public function down(): void
    {
        $db = Connection::getInstance();

        $db->exec("
            ALTER TABLE student_occurrences

            DROP INDEX idx_student_occurrences_created_by,

            DROP COLUMN created_by_role,

            DROP COLUMN created_by_name,

            DROP COLUMN created_by
        ");

        $db->exec("
            ALTER TABLE school_notices

            DROP COLUMN created_by_role,

            DROP COLUMN created_by_name
        ");
    }
};