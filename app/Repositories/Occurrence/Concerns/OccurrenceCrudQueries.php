<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence\Concerns;

trait OccurrenceCrudQueries
{
    /**
     * Cria uma ocorrência individual.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO student_occurrences (
                student_id,
                subject_id,
                occurrence_date,
                type,
                severity,
                title,
                description,
                actions_taken,
                status,
                created_by,
                created_by_name,
                created_by_role,
                created_at,
                updated_at
            )
            VALUES (
                :student_id,
                :subject_id,
                :occurrence_date,
                :type,
                :severity,
                :title,
                :description,
                :actions_taken,
                :status,
                :created_by,
                :created_by_name,
                :created_by_role,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'student_id' =>
                $data['student_id'],

            'subject_id' =>
                $data['subject_id'],

            'occurrence_date' =>
                $data['occurrence_date'],

            'type' =>
                $data['type'],

            'severity' =>
                $data['severity'] ?? 'LOW',

            'title' =>
                $data['title'],

            'description' =>
                $data['description'],

            'actions_taken' =>
                $data['actions_taken'] ?? null,

            'status' =>
                $data['status'],

            'created_by' =>
                $data['created_by'] ?? null,

            'created_by_name' =>
                $data['created_by_name'] ?? null,

            'created_by_role' =>
                $data['created_by_role'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Cria a mesma ocorrência para vários alunos.
     *
     * A transação é controlada pelo OccurrenceService.
     */
    public function createMultiple(
        array $studentIds,
        array $data
    ): int {
        $stmt = $this->db->prepare("
            INSERT INTO student_occurrences (
                student_id,
                subject_id,
                occurrence_date,
                type,
                severity,
                title,
                description,
                actions_taken,
                status,
                created_by,
                created_by_name,
                created_by_role,
                created_at,
                updated_at
            )
            VALUES (
                :student_id,
                :subject_id,
                :occurrence_date,
                :type,
                :severity,
                :title,
                :description,
                :actions_taken,
                :status,
                :created_by,
                :created_by_name,
                :created_by_role,
                NOW(),
                NOW()
            )
        ");

        $created = 0;

        foreach ($studentIds as $studentId) {
            $studentId = (int) $studentId;

            if ($studentId <= 0) {
                continue;
            }

            $stmt->execute([
                'student_id' =>
                    $studentId,

                'subject_id' =>
                    $data['subject_id'],

                'occurrence_date' =>
                    $data['occurrence_date'],

                'type' =>
                    $data['type'],

                'severity' =>
                    $data['severity'] ?? 'LOW',

                'title' =>
                    $data['title'],

                'description' =>
                    $data['description'],

                'actions_taken' =>
                    $data['actions_taken'] ?? null,

                'status' =>
                    $data['status'],

                'created_by' =>
                    $data['created_by'] ?? null,

                'created_by_name' =>
                    $data['created_by_name'] ?? null,

                'created_by_role' =>
                    $data['created_by_role'] ?? null,
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * Atualiza a ocorrência sem modificar
     * seus dados originais de autoria.
     */
    public function update(
        int $id,
        array $data
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE student_occurrences

            SET
                subject_id = :subject_id,
                occurrence_date = :occurrence_date,
                type = :type,
                severity = :severity,
                title = :title,
                description = :description,
                actions_taken = :actions_taken,
                status = :status,
                updated_at = NOW()

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,

            'subject_id' =>
                $data['subject_id'],

            'occurrence_date' =>
                $data['occurrence_date'],

            'type' =>
                $data['type'],

            'severity' =>
                $data['severity'] ?? 'LOW',

            'title' =>
                $data['title'],

            'description' =>
                $data['description'],

            'actions_taken' =>
                $data['actions_taken'] ?? null,

            'status' =>
                $data['status'],
        ]);
    }

    /**
     * Atualiza somente a situação da ocorrência.
     */
    public function updateStatus(
        int $id,
        string $status
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE student_occurrences

            SET
                status = :status,
                updated_at = NOW()

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }

    /**
     * Exclui uma ocorrência.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM student_occurrences

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
        ]);
    }
}