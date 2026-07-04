<?php

declare(strict_types=1);

namespace App\Repositories;

class StudentRepository extends BaseRepository
{
    public function all(): array
    {
        $stmt = $this->db->query("
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
                        )
                        /
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

            ORDER BY students.name
        ");

        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM students
            WHERE active = 1
        ");

        return (int) $stmt->fetchColumn();
    }

    public function countInAlert(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM (
                SELECT
                    students.id,
                    ROUND(
                        (
                            SUM(
                                CASE
                                    WHEN attendance_items.status = 'P'
                                    THEN 1
                                    ELSE 0
                                END
                            )
                            /
                            NULLIF(COUNT(attendance_items.id), 0)
                        ) * 100,
                        1
                    ) AS percentage
                FROM students

                INNER JOIN attendance_items
                    ON attendance_items.student_id = students.id

                WHERE students.active = 1

                GROUP BY students.id

                HAVING percentage < 85

            ) alerts
        ");

        return (int) $stmt->fetchColumn();
    }

    public function availableForEnrollment(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                name,
                registration
            FROM students
            WHERE active = 1
              AND id NOT IN (
                    SELECT student_id
                    FROM enrollments
                    WHERE active = 1
              )
            ORDER BY name
        ");

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
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
        $stmt = $this->db->prepare("
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
        $stmt = $this->db->prepare("
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
        $stmt = $this->db->prepare("
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
        if ($ignoreId === null) {

            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM students
                WHERE registration = :registration
            ");

            $stmt->execute([
                'registration' => $registration,
            ]);

        } else {

            $stmt = $this->db->prepare("
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