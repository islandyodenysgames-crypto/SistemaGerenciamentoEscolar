<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence\Concerns;

trait OccurrenceLookupQueries
{
    /**
     * Retorna todas as ocorrências de um aluno.
     */
    public function byStudent(
        int $studentId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                student_occurrences.id,
                student_occurrences.student_id,
                student_occurrences.subject_id,
                subjects.name AS subject_name,
                student_occurrences.occurrence_date,
                student_occurrences.type,
                student_occurrences.severity,
                student_occurrences.title,
                student_occurrences.description,
                student_occurrences.actions_taken,
                student_occurrences.status,

                student_occurrences.created_by,
                student_occurrences.created_by_name,
                student_occurrences.created_by_role,

                student_occurrences.created_at,
                student_occurrences.updated_at

            FROM student_occurrences

            LEFT JOIN subjects
                ON subjects.id =
                    student_occurrences.subject_id

            WHERE student_occurrences.student_id =
                :student_id

            ORDER BY
                student_occurrences.occurrence_date DESC,
                student_occurrences.id DESC
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Localiza uma ocorrência pelo ID.
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                student_occurrences.id,
                student_occurrences.student_id,
                student_occurrences.subject_id,
                subjects.name AS subject_name,
                student_occurrences.occurrence_date,
                student_occurrences.type,
                student_occurrences.severity,
                student_occurrences.title,
                student_occurrences.description,
                student_occurrences.actions_taken,
                student_occurrences.status,

                student_occurrences.created_by,
                student_occurrences.created_by_name,
                student_occurrences.created_by_role,

                student_occurrences.created_at,
                student_occurrences.updated_at,

                students.name AS student_name,
                students.registration AS student_registration

            FROM student_occurrences

            LEFT JOIN subjects
                ON subjects.id =
                    student_occurrences.subject_id

            INNER JOIN students
                ON students.id =
                    student_occurrences.student_id

            WHERE student_occurrences.id = :id

            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $occurrence = $stmt->fetch();

        return $occurrence ?: null;
    }

    /**
     * Ocorrências mais recentes.
     */
    public function recent(
        int $limit = 8
    ): array {
        $limit = max(
            1,
            min(30, $limit)
        );

        $stmt = $this->db->query("
            SELECT
                student_occurrences.id,
                student_occurrences.student_id,
                student_occurrences.subject_id,
                subjects.name AS subject_name,
                student_occurrences.occurrence_date,
                student_occurrences.type,
                student_occurrences.severity,
                student_occurrences.title,
                student_occurrences.status,

                student_occurrences.created_by,
                student_occurrences.created_by_name,
                student_occurrences.created_by_role,

                student_occurrences.created_at,
                student_occurrences.updated_at,

                students.name AS student_name,
                students.registration AS student_registration,

                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift

            FROM student_occurrences

            LEFT JOIN subjects
                ON subjects.id =
                    student_occurrences.subject_id

            INNER JOIN students
                ON students.id =
                    student_occurrences.student_id

            LEFT JOIN enrollments
                ON enrollments.student_id =
                    students.id

               AND enrollments.active = 1

            LEFT JOIN school_classes
                ON school_classes.id =
                    enrollments.school_class_id

            ORDER BY
                CASE student_occurrences.severity
                    WHEN 'CRITICAL' THEN 4
                    WHEN 'HIGH' THEN 3
                    WHEN 'MEDIUM' THEN 2
                    ELSE 1
                END DESC,

                student_occurrences.occurrence_date DESC,
                student_occurrences.id DESC

            LIMIT {$limit}
        ");

        return $stmt->fetchAll();
    }

    /**
     * Lista as ocorrências de uma data.
     */
    public function byDate(string $date): array
    {
        $stmt = $this->db->prepare("
            SELECT
                student_occurrences.id,
                student_occurrences.student_id,
                student_occurrences.subject_id,
                subjects.name AS subject_name,
                student_occurrences.occurrence_date,
                student_occurrences.type,
                student_occurrences.severity,
                student_occurrences.title,
                student_occurrences.description,
                student_occurrences.actions_taken,
                student_occurrences.status,

                student_occurrences.created_by,
                student_occurrences.created_by_name,
                student_occurrences.created_by_role,

                student_occurrences.created_at,
                student_occurrences.updated_at,

                students.name AS student_name,
                students.registration AS student_registration,

                school_classes.id AS school_class_id,
                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift

            FROM student_occurrences

            LEFT JOIN subjects
                ON subjects.id =
                    student_occurrences.subject_id

            INNER JOIN students
                ON students.id =
                    student_occurrences.student_id

            LEFT JOIN enrollments
                ON enrollments.student_id =
                    students.id

               AND enrollments.active = 1

            LEFT JOIN school_classes
                ON school_classes.id =
                    enrollments.school_class_id

            WHERE student_occurrences.occurrence_date =
                :occurrence_date

            ORDER BY
                CASE student_occurrences.severity
                    WHEN 'CRITICAL' THEN 4
                    WHEN 'HIGH' THEN 3
                    WHEN 'MEDIUM' THEN 2
                    ELSE 1
                END DESC,

                student_occurrences.id DESC
        ");

        $stmt->execute([
            'occurrence_date' => $date,
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Lista ocorrências aplicando filtros combináveis
     * da dashboard administrativa.
     */
    public function filteredRecent(
        array $filters,
        int $limit = 50
    ): array {
        $limit = max(1, min(100, $limit));

        $conditions = [];
        $parameters = [];

        $search = trim((string) ($filters['search'] ?? ''));
        $classId = (int) ($filters['class_id'] ?? 0);
        $subjectId = (int) ($filters['subject_id'] ?? 0);
        $severity = strtoupper(trim((string) ($filters['severity'] ?? '')));
        $type = strtoupper(trim((string) ($filters['type'] ?? '')));
        $status = strtoupper(trim((string) ($filters['status'] ?? '')));
        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));

        if ($search !== '') {
            $searchValue = '%' . $search . '%';

            $conditions[] = "(
                students.name LIKE :search_student_name
                OR students.registration LIKE :search_registration
                OR student_occurrences.title LIKE :search_title
                OR student_occurrences.created_by_name LIKE :search_author
            )";

            $parameters['search_student_name'] = $searchValue;
            $parameters['search_registration'] = $searchValue;
            $parameters['search_title'] = $searchValue;
            $parameters['search_author'] = $searchValue;
        }

        if ($classId > 0) {
            $conditions[] = 'school_classes.id = :class_id';
            $parameters['class_id'] = $classId;
        }

        if ($subjectId > 0) {
            $conditions[] = 'student_occurrences.subject_id = :subject_id';
            $parameters['subject_id'] = $subjectId;
        }

        if ($severity !== '') {
            $conditions[] = 'student_occurrences.severity = :severity';
            $parameters['severity'] = $severity;
        }

        if ($type !== '') {
            $conditions[] = 'student_occurrences.type = :type';
            $parameters['type'] = $type;
        }

        if ($status !== '') {
            $conditions[] = 'student_occurrences.status = :status';
            $parameters['status'] = $status;
        }

        if ($dateFrom !== '') {
            $conditions[] = 'student_occurrences.occurrence_date >= :date_from';
            $parameters['date_from'] = $dateFrom;
        }

        if ($dateTo !== '') {
            $conditions[] = 'student_occurrences.occurrence_date <= :date_to';
            $parameters['date_to'] = $dateTo;
        }

        $where = $conditions !== []
            ? 'WHERE ' . implode("\n AND ", $conditions)
            : '';

        $stmt = $this->db->prepare("
            SELECT
                student_occurrences.id,
                student_occurrences.student_id,
                student_occurrences.subject_id,
                subjects.name AS subject_name,
                student_occurrences.occurrence_date,
                student_occurrences.type,
                student_occurrences.severity,
                student_occurrences.title,
                student_occurrences.status,
                student_occurrences.created_by,
                student_occurrences.created_by_name,
                student_occurrences.created_by_role,
                student_occurrences.created_at,
                student_occurrences.updated_at,
                students.name AS student_name,
                students.registration AS student_registration,
                school_classes.id AS school_class_id,
                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift
            FROM student_occurrences
            LEFT JOIN subjects
                ON subjects.id = student_occurrences.subject_id
            INNER JOIN students
                ON students.id = student_occurrences.student_id
            LEFT JOIN enrollments
                ON enrollments.student_id = students.id
                AND enrollments.active = 1
            LEFT JOIN school_classes
                ON school_classes.id = enrollments.school_class_id
            {$where}
            ORDER BY
                student_occurrences.occurrence_date DESC,
                student_occurrences.created_at DESC,
                student_occurrences.id DESC
            LIMIT {$limit}
        ");

        $stmt->execute($parameters);

        return $stmt->fetchAll();
    }

}