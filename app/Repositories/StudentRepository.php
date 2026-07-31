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
                students.photo_path,
                students.photo_updated_at,
                students.created_at,

                school_classes.id AS school_class_id,
                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift,

                COALESCE(attendance_stats.total_records, 0) AS total_records,
                COALESCE(attendance_stats.total_presentes, 0) AS total_presentes,
                COALESCE(attendance_stats.attendance_percentage, 0) AS attendance_percentage

            FROM students

            LEFT JOIN enrollments
                ON enrollments.student_id = students.id
               AND enrollments.active = 1

            LEFT JOIN school_classes
                ON school_classes.id = enrollments.school_class_id

            LEFT JOIN (
                SELECT
                    student_id,
                    COUNT(*) AS total_records,

                    SUM(
                        CASE
                            WHEN status = 'P'
                            THEN 1
                            ELSE 0
                        END
                    ) AS total_presentes,

                    ROUND(
                        (
                            SUM(
                                CASE
                                    WHEN status = 'P'
                                    THEN 1
                                    ELSE 0
                                END
                            )
                            /
                            NULLIF(COUNT(*), 0)
                        ) * 100,
                        1
                    ) AS attendance_percentage

                FROM attendance_items

                GROUP BY student_id
            ) attendance_stats
                ON attendance_stats.student_id = students.id

            ORDER BY
                school_classes.year ASC,
                school_classes.name ASC,
                students.name ASC
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

    public function countInAlert(float $threshold = 95.0): int
    {
        $stmt = $this->db->prepare("
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

                HAVING percentage < :threshold
            ) alerts
        ");
        $stmt->execute(['threshold' => $threshold]);

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

            ORDER BY name ASC
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
                active,
                photo_path,
                photo_updated_at

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

    public function findByName(string $name): ?array
    {
        $stmt = $this->db->prepare("
            SELECT *

            FROM students

            WHERE LOWER(TRIM(name)) = LOWER(TRIM(:name))

            LIMIT 1
        ");

        $stmt->execute([
            'name' => $name,
        ]);

        $student = $stmt->fetch();

        return $student ?: null;
    }

    public function byClass(int $classId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                students.id,
                students.name,
                students.registration,
                students.birth_date,
                students.guardian_name,
                students.guardian_phone,
                students.active,
                students.photo_path,
                students.photo_updated_at,

                school_classes.id AS school_class_id,
                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift,

                COALESCE(attendance_stats.total_records, 0) AS total_records,
                COALESCE(attendance_stats.total_presentes, 0) AS total_presentes,
                COALESCE(attendance_stats.attendance_percentage, 0) AS attendance_percentage

            FROM enrollments

            INNER JOIN students
                ON students.id = enrollments.student_id

            INNER JOIN school_classes
                ON school_classes.id = enrollments.school_class_id

            LEFT JOIN (
                SELECT
                    student_id,
                    COUNT(*) AS total_records,

                    SUM(
                        CASE
                            WHEN status = 'P'
                            THEN 1
                            ELSE 0
                        END
                    ) AS total_presentes,

                    ROUND(
                        (
                            SUM(
                                CASE
                                    WHEN status = 'P'
                                    THEN 1
                                    ELSE 0
                                END
                            )
                            /
                            NULLIF(COUNT(*), 0)
                        ) * 100,
                        1
                    ) AS attendance_percentage

                FROM attendance_items

                GROUP BY student_id
            ) attendance_stats
                ON attendance_stats.student_id = students.id

            WHERE enrollments.school_class_id = :class_id
              AND enrollments.active = 1

            ORDER BY students.name ASC
        ");

        $stmt->execute([
            'class_id' => $classId,
        ]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
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

            'birth_date' => !empty($data['birth_date'])
                 ? $data['birth_date']
                 : null,
            
            'guardian_name' => !empty($data['guardian_name'])
                 ? $data['guardian_name']
                 : null,

            'guardian_phone' => !empty($data['guardian_phone'])
            ? $data['guardian_phone']
            : null,

            'active' => 1,
        ]);

        return (int) $this->db->lastInsertId();
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

    public function nextRegistration(): string
    {
        $year = date('Y');

        $stmt = $this->db->prepare("
            SELECT registration

            FROM students

            WHERE registration LIKE :prefix

            ORDER BY registration DESC

            LIMIT 1
        ");

        $stmt->execute([
            'prefix' => $year . '%',
        ]);

        $last = $stmt->fetchColumn();

        if (!$last) {
            return $year . '00001';
        }

        $number = (int) substr((string) $last, 4);

        return $year . str_pad(
            (string) ($number + 1),
            5,
            '0',
            STR_PAD_LEFT
        );
    }

    public function statistics(int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(ai.id) AS total_records,

                SUM(
                    CASE
                        WHEN ai.status = 'P'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_presentes,

                SUM(
                    CASE
                        WHEN ai.status = 'F'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_faltas,

                SUM(
                    CASE
                        WHEN ai.status IN ('FJ', 'AM', 'FO')
                        THEN 1
                        ELSE 0
                    END
                ) AS total_justificadas

            FROM attendance_items AS ai

            WHERE ai.student_id = :student_id
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        $stats = $stmt->fetch() ?: [];

        $records = (int) ($stats['total_records'] ?? 0);
        $presentes = (int) ($stats['total_presentes'] ?? 0);

        $stats['total_records'] = $records;
        $stats['total_presentes'] = $presentes;
        $stats['total_faltas'] = (int) ($stats['total_faltas'] ?? 0);
        $stats['total_justificadas'] = (int) ($stats['total_justificadas'] ?? 0);

        $stats['attendance_percentage'] = $records > 0
            ? round(($presentes / $records) * 100, 1)
            : 0;

        return $stats;
    }

    public function attendanceHistory(int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                attendance.attendance_date,
                attendance_items.status,
                school_classes.name AS class_name,
                school_classes.year AS class_year,
                school_classes.shift AS class_shift

            FROM attendance_items

            INNER JOIN attendance
                ON attendance.id = attendance_items.attendance_id

            INNER JOIN school_classes
                ON school_classes.id = attendance.school_class_id

            WHERE attendance_items.student_id = :student_id

            ORDER BY
                attendance.attendance_date DESC,
                attendance.id DESC

            LIMIT 100
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        return $stmt->fetchAll();
    }

    public function calendar(
        int $studentId,
        int $year,
        int $month
    ): array {
        $start = sprintf('%04d-%02d-01', $year, $month);
        $end = date('Y-m-t', strtotime($start));

        $stmt = $this->db->prepare("
            SELECT
                attendance.attendance_date,
                attendance_items.status

            FROM attendance_items

            INNER JOIN attendance
                ON attendance.id = attendance_items.attendance_id

            WHERE attendance_items.student_id = :student_id
              AND attendance.attendance_date BETWEEN :start_date AND :end_date

            ORDER BY attendance.attendance_date ASC
        ");

        $stmt->execute([
            'student_id' => $studentId,
            'start_date' => $start,
            'end_date' => $end,
        ]);

        $items = [];

        foreach ($stmt->fetchAll() as $row) {
            $items[$row['attendance_date']] = $row['status'];
        }

        return $items;
    }

    public function attendanceEvolutionDaily(int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                attendance.attendance_date,
                attendance_items.status

            FROM attendance_items

            INNER JOIN attendance
                ON attendance.id = attendance_items.attendance_id

            WHERE attendance_items.student_id = :student_id

            ORDER BY
                attendance.attendance_date ASC,
                attendance.id ASC
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        $records = $stmt->fetchAll();

        $evolution = [];
        $totalRecords = 0;
        $totalPresentes = 0;

        foreach ($records as $record) {
            $totalRecords++;

            if (($record['status'] ?? '') === 'P') {
                $totalPresentes++;
            }

            $percentage = $totalRecords > 0
                ? round(($totalPresentes / $totalRecords) * 100, 1)
                : 0;

            $evolution[] = [
                'date' => $record['attendance_date'],
                'percentage' => $percentage,
                'total_records' => $totalRecords,
                'total_presentes' => $totalPresentes,
            ];
        }

        return $evolution;
    }

    public function attendanceEvolutionMonthly(int $studentId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                YEAR(attendance.attendance_date) AS year,
                MONTH(attendance.attendance_date) AS month,

                COUNT(attendance_items.id) AS total_records,

                SUM(
                    CASE
                        WHEN attendance_items.status = 'P'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_presentes

            FROM attendance_items

            INNER JOIN attendance
                ON attendance.id = attendance_items.attendance_id

            WHERE attendance_items.student_id = :student_id

            GROUP BY
                YEAR(attendance.attendance_date),
                MONTH(attendance.attendance_date)

            ORDER BY
                YEAR(attendance.attendance_date) ASC,
                MONTH(attendance.attendance_date) ASC
        ");

        $stmt->execute([
            'student_id' => $studentId,
        ]);

        $evolution = [];

        foreach ($stmt->fetchAll() as $row) {
            $records = (int) ($row['total_records'] ?? 0);
            $presentes = (int) ($row['total_presentes'] ?? 0);

            $evolution[] = [
                'year' => (int) ($row['year'] ?? 0),
                'month' => (int) ($row['month'] ?? 0),
                'percentage' => $records > 0
                    ? round(($presentes / $records) * 100, 1)
                    : 0,
                'total_records' => $records,
                'total_presentes' => $presentes,
            ];
        }

        return $evolution;
    }
}