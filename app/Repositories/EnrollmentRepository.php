<?php

declare(strict_types=1);

namespace App\Repositories;

class EnrollmentRepository extends BaseRepository
{
    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                enrollments.id,
                enrollments.student_id,
                enrollments.school_class_id,
                enrollments.enrollment_date,
                enrollments.active,

                students.name AS student_name,
                students.registration,

                school_classes.name AS class_name,
                school_classes.year,
                school_classes.shift

            FROM enrollments

            INNER JOIN students
                ON students.id = enrollments.student_id

            INNER JOIN school_classes
                ON school_classes.id = enrollments.school_class_id

            ORDER BY
                enrollments.active DESC,
                school_classes.year DESC,
                school_classes.name ASC,
                students.name ASC
        ");

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                student_id,
                school_class_id,
                enrollment_date,
                active

            FROM enrollments

            WHERE id = :id

            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $enrollment = $stmt->fetch();

        return $enrollment ?: null;
    }

    public function activeByStudent(
        int $studentId
    ): ?array {
        $stmt = $this->db->prepare("
            SELECT
                enrollments.id,
                enrollments.student_id,
                enrollments.school_class_id,
                enrollments.enrollment_date,
                enrollments.active,

                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift

            FROM enrollments

            INNER JOIN school_classes
                ON school_classes.id = enrollments.school_class_id

            WHERE enrollments.student_id = :student_id
              AND enrollments.active = 1

            ORDER BY enrollments.id DESC

            LIMIT 1
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        $enrollment = $stmt->fetch();

        return $enrollment ?: null;
    }

    public function historyByStudent(
        int $studentId
    ): array {
        $stmt = $this->db->prepare("
            SELECT
                enrollments.id,
                enrollments.student_id,
                enrollments.school_class_id,
                enrollments.enrollment_date,
                enrollments.active,
                enrollments.created_at,
                enrollments.updated_at,

                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift

            FROM enrollments

            INNER JOIN school_classes
                ON school_classes.id = enrollments.school_class_id

            WHERE enrollments.student_id = :student_id

            ORDER BY
                enrollments.active DESC,
                enrollments.enrollment_date DESC,
                enrollments.id DESC
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO enrollments (
                student_id,
                school_class_id,
                enrollment_date,
                active,
                created_at,
                updated_at
            )
            VALUES (
                :student_id,
                :school_class_id,
                :enrollment_date,
                :active,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'student_id' => $data['student_id'],

            'school_class_id' =>
                $data['school_class_id'],

            'enrollment_date' =>
                $data['enrollment_date'],

            'active' => 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(
        int $id,
        array $data
    ): void {
        $stmt = $this->db->prepare("
            UPDATE enrollments

            SET
                student_id = :student_id,
                school_class_id = :school_class_id,
                enrollment_date = :enrollment_date,
                active = :active,
                updated_at = NOW()

            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,

            'student_id' =>
                $data['student_id'],

            'school_class_id' =>
                $data['school_class_id'],

            'enrollment_date' =>
                $data['enrollment_date'],

            'active' => $data['active'],
        ]);
    }

    public function cancel(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE enrollments

            SET
                active = 0,
                updated_at = NOW()

            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
        ]);
    }

    public function cancelActiveByStudent(
        int $studentId
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE enrollments

            SET
                active = 0,
                updated_at = NOW()

            WHERE student_id = :student_id
              AND active = 1
        ");

        return $stmt->execute([
            'student_id' => $studentId,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM enrollments
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
        ]);
    }

    public function exists(
        int $studentId,
        int $schoolClassId,
        ?int $ignoreId = null
    ): bool {
        if ($ignoreId === null) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)

                FROM enrollments

                WHERE student_id = :student_id
                  AND school_class_id = :school_class_id
                  AND active = 1
            ");

            $stmt->execute([
                'student_id' => $studentId,
                'school_class_id' => $schoolClassId,
            ]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*)

                FROM enrollments

                WHERE student_id = :student_id
                  AND school_class_id = :school_class_id
                  AND id <> :id
                  AND active = 1
            ");

            $stmt->execute([
                'student_id' => $studentId,
                'school_class_id' => $schoolClassId,
                'id' => $ignoreId,
            ]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }

    public function hasActiveEnrollment(
        int $studentId
    ): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)

            FROM enrollments

            WHERE student_id = :student_id
              AND active = 1
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }
}