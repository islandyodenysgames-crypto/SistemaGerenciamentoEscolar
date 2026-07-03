<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class StudentService
{
    public function all(): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                students.id,
                students.name,
                students.registration,
                students.birth_date,
                students.guardian_name,
                students.guardian_phone,
                students.active,
                students.created_at,

                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift,

                COUNT(attendance_items.id) AS total_records,

                SUM(
                    CASE
                        WHEN attendance_items.status = 'P'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_presentes,

                ROUND(
                    (
                        SUM(
                            CASE
                                WHEN attendance_items.status = 'P'
                                THEN 1
                                ELSE 0
                            END
                        ) /
                        NULLIF(COUNT(attendance_items.id), 0)
                    ) * 100,
                    1
                ) AS attendance_percentage

            FROM students

            LEFT JOIN enrollments
                ON enrollments.student_id = students.id
               AND enrollments.active = 1

            LEFT JOIN school_classes
                ON school_classes.id = enrollments.school_class_id

            LEFT JOIN attendance_items
                ON attendance_items.student_id = students.id

            GROUP BY
                students.id,
                students.name,
                students.registration,
                students.birth_date,
                students.guardian_name,
                students.guardian_phone,
                students.active,
                students.created_at,
                school_classes.name,
                school_classes.year,
                school_classes.shift

            ORDER BY students.name ASC
        ");

        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT COUNT(*)
            FROM students
            WHERE active = 1
        ");

        return (int) $stmt->fetchColumn();
    }

    public function availableForEnrollment(): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                students.id,
                students.name,
                students.registration
            FROM students
            WHERE students.active = 1
              AND students.id NOT IN (
                    SELECT enrollments.student_id
                    FROM enrollments
                    WHERE enrollments.active = 1
              )
            ORDER BY students.name ASC
        ");

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                id,
                name,
                registration,
                birth_date,
                guardian_name,
                guardian_phone,
                active
            FROM students
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $student = $stmt->fetch();

        return $student ?: null;
    }

    public function create(array $data): void
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            INSERT INTO students (
                name,
                registration,
                birth_date,
                guardian_name,
                guardian_phone,
                active,
                created_at,
                updated_at
            )
            VALUES (
                :name,
                :registration,
                :birth_date,
                :guardian_name,
                :guardian_phone,
                :active,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'name' => $data['name'],
            'registration' => $data['registration'],
            'birth_date' => $data['birth_date'],
            'guardian_name' => $data['guardian_name'],
            'guardian_phone' => $data['guardian_phone'],
            'active' => 1,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            UPDATE students
            SET
                name = :name,
                registration = :registration,
                birth_date = :birth_date,
                guardian_name = :guardian_name,
                guardian_phone = :guardian_phone,
                active = :active,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'registration' => $data['registration'],
            'birth_date' => $data['birth_date'],
            'guardian_name' => $data['guardian_name'],
            'guardian_phone' => $data['guardian_phone'],
            'active' => $data['active'],
        ]);
    }

    public function delete(int $id): bool
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            DELETE FROM students
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
        ]);
    }

    public function registrationExists(
        string $registration,
        ?int $ignoreId = null
    ): bool {
        $db = Connection::getInstance();

        if ($ignoreId === null) {

            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM students
                WHERE registration = :registration
            ");

            $stmt->execute([
                'registration' => $registration,
            ]);

        } else {

            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM students
                WHERE registration = :registration
                  AND id <> :id
            ");

            $stmt->execute([
                'registration' => $registration,
                'id' => $ignoreId,
            ]);

        }

        return (int) $stmt->fetchColumn() > 0;
    }
}