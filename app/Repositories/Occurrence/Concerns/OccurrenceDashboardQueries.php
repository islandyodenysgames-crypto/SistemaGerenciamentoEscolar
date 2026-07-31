<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence\Concerns;

trait OccurrenceDashboardQueries
{
    /**
     * Alunos com maior quantidade de ocorrências.
     */
    public function studentsWithMostOccurrences(
        int $limit = 5
    ): array {
        $limit = max(
            1,
            min(20, $limit)
        );

        $stmt = $this->db->query("
            SELECT
                students.id,
                students.name,
                students.registration,

                COUNT(
                    student_occurrences.id
                ) AS total_occurrences,

                SUM(
                    CASE
                        WHEN student_occurrences.status = 'OPEN'
                        THEN 1
                        ELSE 0
                    END
                ) AS open_occurrences,

                SUM(
                    CASE
                        WHEN student_occurrences.severity = 'CRITICAL'
                        THEN 1
                        ELSE 0
                    END
                ) AS critical_occurrences

            FROM student_occurrences

            INNER JOIN students
                ON students.id =
                    student_occurrences.student_id

            GROUP BY
                students.id,
                students.name,
                students.registration

            ORDER BY
                total_occurrences DESC,
                critical_occurrences DESC,
                open_occurrences DESC,
                students.name ASC

            LIMIT {$limit}
        ");

        return $stmt->fetchAll();
    }

    /**
     * Turmas com maior quantidade de ocorrências.
     */
    public function classesWithMostOccurrences(
        int $limit = 5
    ): array {
        $limit = max(
            1,
            min(20, $limit)
        );

        $stmt = $this->db->query("
            SELECT
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift,

                COUNT(
                    student_occurrences.id
                ) AS total_occurrences,

                SUM(
                    CASE
                        WHEN student_occurrences.severity = 'CRITICAL'
                        THEN 1
                        ELSE 0
                    END
                ) AS critical_occurrences,

                SUM(
                    CASE
                        WHEN student_occurrences.status = 'OPEN'
                        THEN 1
                        ELSE 0
                    END
                ) AS open_occurrences

            FROM student_occurrences

            INNER JOIN students
                ON students.id =
                    student_occurrences.student_id

            INNER JOIN enrollments
                ON enrollments.student_id =
                    students.id

               AND enrollments.active = 1

            INNER JOIN school_classes
                ON school_classes.id =
                    enrollments.school_class_id

            GROUP BY
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift

            ORDER BY
                total_occurrences DESC,
                critical_occurrences DESC,
                open_occurrences DESC,
                school_classes.name ASC

            LIMIT {$limit}
        ");

        return $stmt->fetchAll();
    }

    /**
     * Disciplinas com maior quantidade
     * de ocorrências.
     */
    public function subjectsWithMostOccurrences(
        int $limit = 10
    ): array {
        $limit = max(
            1,
            min(20, $limit)
        );

        $stmt = $this->db->query("
            SELECT
                COALESCE(
                    subjects.name,
                    'Sem disciplina'
                ) AS subject_name,

                COUNT(
                    student_occurrences.id
                ) AS total_occurrences,

                SUM(
                    CASE
                        WHEN student_occurrences.status = 'OPEN'
                        THEN 1
                        ELSE 0
                    END
                ) AS open_occurrences,

                SUM(
                    CASE
                        WHEN student_occurrences.severity = 'CRITICAL'
                        THEN 1
                        ELSE 0
                    END
                ) AS critical_occurrences

            FROM student_occurrences

            LEFT JOIN subjects
                ON subjects.id =
                    student_occurrences.subject_id

            GROUP BY
                student_occurrences.subject_id,
                subjects.name

            ORDER BY
                total_occurrences DESC,
                critical_occurrences DESC,
                open_occurrences DESC,
                subject_name ASC

            LIMIT {$limit}
        ");

        return $stmt->fetchAll();
    }
}