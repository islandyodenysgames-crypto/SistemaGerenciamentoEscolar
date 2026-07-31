<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence;

use App\Repositories\BaseRepository;

class ActionRepository extends BaseRepository
{
    public function byOccurrence(
        int $occurrenceId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                occurrence_actions.id,
                occurrence_actions.occurrence_id,
                occurrence_actions.action_type,
                occurrence_actions.description,
                occurrence_actions.status_after,

                occurrence_actions.created_by,
                occurrence_actions.created_by_name,
                occurrence_actions.created_by_role,

                occurrence_actions.created_at,
                occurrence_actions.updated_at

            FROM occurrence_actions

            WHERE occurrence_actions.occurrence_id =
                :occurrence_id

            ORDER BY
                occurrence_actions.created_at ASC,
                occurrence_actions.id ASC
        ");

        $stmt->execute([
            'occurrence_id' => $occurrenceId,
        ]);

        return $stmt->fetchAll();
    }

    public function byStudent(
        int $studentId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                occurrence_actions.id,
                occurrence_actions.occurrence_id,
                occurrence_actions.action_type,
                occurrence_actions.description,
                occurrence_actions.status_after,

                occurrence_actions.created_by,
                occurrence_actions.created_by_name,
                occurrence_actions.created_by_role,

                occurrence_actions.created_at,
                occurrence_actions.updated_at

            FROM occurrence_actions

            INNER JOIN student_occurrences
                ON student_occurrences.id =
                    occurrence_actions.occurrence_id

            WHERE student_occurrences.student_id =
                :student_id

            ORDER BY
                occurrence_actions.occurrence_id ASC,
                occurrence_actions.created_at ASC,
                occurrence_actions.id ASC
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO occurrence_actions (
                occurrence_id,
                action_type,
                description,
                status_after,

                created_by,
                created_by_name,
                created_by_role,

                created_at,
                updated_at
            )
            VALUES (
                :occurrence_id,
                :action_type,
                :description,
                :status_after,

                :created_by,
                :created_by_name,
                :created_by_role,

                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'occurrence_id' =>
                $data['occurrence_id'],

            'action_type' =>
                $data['action_type'],

            'description' =>
                $data['description'],

            'status_after' =>
                $data['status_after'],

            'created_by' =>
                $data['created_by'] ?? null,

            'created_by_name' =>
                $data['created_by_name'] ?? null,

            'created_by_role' =>
                $data['created_by_role'] ?? null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function countByOccurrence(
        int $occurrenceId
    ): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)

            FROM occurrence_actions

            WHERE occurrence_id = :occurrence_id
        ");

        $stmt->execute([
            'occurrence_id' => $occurrenceId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function deleteByOccurrence(
        int $occurrenceId
    ): bool {
        $stmt = $this->db->prepare("
            DELETE FROM occurrence_actions

            WHERE occurrence_id = :occurrence_id
        ");

        return $stmt->execute([
            'occurrence_id' => $occurrenceId,
        ]);
    }
}