<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence\Concerns;

trait OccurrenceStudentQueries
{
    /**
     * Total de ocorrências do aluno.
     */
    public function countByStudent(
        int $studentId
    ): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE student_id = :student_id
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Total de ocorrências abertas do aluno.
     */
    public function countOpenByStudent(
        int $studentId
    ): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE student_id = :student_id
              AND status = 'OPEN'
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Total de ocorrências resolvidas do aluno.
     */
    public function countResolvedByStudent(
        int $studentId
    ): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE student_id = :student_id
              AND status = 'RESOLVED'
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Contagem por tipo para determinado aluno.
     */
    public function countByTypeForStudent(
        int $studentId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                type,
                COUNT(*) AS total

            FROM student_occurrences

            WHERE student_id = :student_id

            GROUP BY type
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        $counts = [
            'OBSERVATION' => 0,
            'WARNING' => 0,
            'SUSPENSION' => 0,
            'REFERRAL' => 0,
            'PRAISE' => 0,
            'OTHER' => 0,
        ];

        foreach ($stmt->fetchAll() as $row) {
            $type = strtoupper(
                (string) (
                    $row['type'] ?? 'OTHER'
                )
            );

            if (array_key_exists($type, $counts)) {
                $counts[$type] = (int) (
                    $row['total'] ?? 0
                );
            }
        }

        return $counts;
    }

    /**
     * Contagem por gravidade para determinado aluno.
     */
    public function countBySeverityForStudent(
        int $studentId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                severity,
                COUNT(*) AS total

            FROM student_occurrences

            WHERE student_id = :student_id

            GROUP BY severity
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        $counts = $this->emptySeverityCounts();

        foreach ($stmt->fetchAll() as $row) {
            $severity = strtoupper(
                (string) (
                    $row['severity'] ?? 'LOW'
                )
            );

            if (
                array_key_exists(
                    $severity,
                    $counts
                )
            ) {
                $counts[$severity] = (int) (
                    $row['total'] ?? 0
                );
            }
        }

        return $counts;
    }
}