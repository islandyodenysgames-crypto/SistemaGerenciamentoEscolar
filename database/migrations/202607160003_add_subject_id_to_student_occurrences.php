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

            ADD COLUMN subject_id
                BIGINT UNSIGNED
                NULL

            AFTER student_id
        ");

        $stmt = $db->prepare("
            SELECT id

            FROM subjects

            WHERE code = :code

            LIMIT 1
        ");

        $stmt->execute([
            'code' => 'GERAL',
        ]);

        $generalSubjectId = (int) (
            $stmt->fetchColumn() ?: 0
        );

        if ($generalSubjectId <= 0) {
            throw new \RuntimeException(
                'A disciplina padrão Geral da Escola não foi encontrada.'
            );
        }

        $stmt = $db->prepare("
            UPDATE student_occurrences

            SET subject_id = :subject_id

            WHERE subject_id IS NULL
        ");

        $stmt->execute([
            'subject_id' =>
                $generalSubjectId,
        ]);

        $db->exec("
            ALTER TABLE student_occurrences

            MODIFY COLUMN subject_id
                BIGINT UNSIGNED
                NOT NULL
        ");

        $db->exec("
            ALTER TABLE student_occurrences

            ADD KEY idx_student_occurrences_subject_id (
                subject_id
            )
        ");

        $db->exec("
            ALTER TABLE student_occurrences

            ADD CONSTRAINT fk_student_occurrences_subject

            FOREIGN KEY (
                subject_id
            )

            REFERENCES subjects (
                id
            )

            ON UPDATE CASCADE

            ON DELETE RESTRICT
        ");
    }

    public function down(): void
    {
        $db = Connection::getInstance();

        $db->exec("
            ALTER TABLE student_occurrences

            DROP FOREIGN KEY
                fk_student_occurrences_subject
        ");

        $db->exec("
            ALTER TABLE student_occurrences

            DROP INDEX
                idx_student_occurrences_subject_id
        ");

        $db->exec("
            ALTER TABLE student_occurrences

            DROP COLUMN subject_id
        ");
    }
};