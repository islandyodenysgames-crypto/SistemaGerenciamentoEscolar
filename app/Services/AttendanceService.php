<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class AttendanceService
{
    public const STATUS_PRESENTE = 'P';
    public const STATUS_FALTA = 'F';
    public const STATUS_FALTA_JUSTIFICADA = 'FJ';
    public const STATUS_ATESTADO_MEDICO = 'AM';
    public const STATUS_FALTA_ONIBUS = 'FO';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PRESENTE => 'Presença',
            self::STATUS_FALTA => 'Falta',
            self::STATUS_FALTA_JUSTIFICADA => 'Falta Justificada',
            self::STATUS_ATESTADO_MEDICO => 'Atestado Médico',
            self::STATUS_FALTA_ONIBUS => 'Falta de Ônibus',
        ];
    }

    public function all(): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                attendance.id,
                attendance.school_class_id,
                attendance.attendance_date,
                attendance.notes,
                school_classes.name AS class_name,
                school_classes.year,
                school_classes.shift
            FROM attendance
            INNER JOIN school_classes
                ON school_classes.id = attendance.school_class_id
            ORDER BY attendance.attendance_date DESC
        ");

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                attendance.id,
                attendance.school_class_id,
                attendance.attendance_date,
                attendance.notes,
                school_classes.name AS class_name,
                school_classes.year,
                school_classes.shift
            FROM attendance
            INNER JOIN school_classes
                ON school_classes.id = attendance.school_class_id
            WHERE attendance.id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $attendance = $stmt->fetch();

        return $attendance ?: null;
    }

    public function findByClassAndDate(int $classId, string $date): ?array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                id,
                school_class_id,
                attendance_date,
                notes,
                created_at
            FROM attendance
            WHERE school_class_id = :class_id
              AND attendance_date = :attendance_date
            LIMIT 1
        ");

        $stmt->execute([
            'class_id' => $classId,
            'attendance_date' => $date,
        ]);

        $attendance = $stmt->fetch();

        return $attendance ?: null;
    }

    public function items(int $attendanceId): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                attendance_items.id,
                attendance_items.student_id,
                attendance_items.status,
                attendance_items.justification,
                students.name AS student_name,
                students.registration
            FROM attendance_items
            INNER JOIN students
                ON students.id = attendance_items.student_id
            WHERE attendance_items.attendance_id = :attendance_id
            ORDER BY students.name ASC
        ");

        $stmt->execute([
            'attendance_id' => $attendanceId,
        ]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            INSERT INTO attendance (
                school_class_id,
                attendance_date,
                notes,
                created_at,
                updated_at
            )
            VALUES (
                :school_class_id,
                :attendance_date,
                :notes,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'school_class_id' => $data['school_class_id'],
            'attendance_date' => $data['attendance_date'],
            'notes' => $data['notes'],
        ]);

        return (int) $db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            UPDATE attendance
            SET
                attendance_date = :attendance_date,
                notes = :notes,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'attendance_date' => $data['attendance_date'],
            'notes' => $data['notes'],
        ]);
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            UPDATE attendance_items
            SET
                status = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $itemId,
            'status' => $status,
        ]);
    }

    public function delete(int $id): bool
    {
        $db = Connection::getInstance();

        $items = $db->prepare("
            DELETE FROM attendance_items
            WHERE attendance_id = :id
        ");

        $items->execute([
            'id' => $id,
        ]);

        $attendance = $db->prepare("
            DELETE FROM attendance
            WHERE id = :id
        ");

        return $attendance->execute([
            'id' => $id,
        ]);
    }

    public function existsForClassAndDate(int $classId, string $date): bool
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM attendance
            WHERE school_class_id = :class_id
              AND attendance_date = :attendance_date
        ");

        $stmt->execute([
            'class_id' => $classId,
            'attendance_date' => $date,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function classInfo(int $classId): ?array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                id,
                name,
                year,
                shift
            FROM school_classes
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $classId,
        ]);

        $class = $stmt->fetch();

        return $class ?: null;
    }

    public function classStudents(int $classId): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                students.id,
                students.name,
                students.registration
            FROM enrollments
            INNER JOIN students
                ON students.id = enrollments.student_id
            WHERE enrollments.school_class_id = :class
              AND enrollments.active = 1
            ORDER BY students.name
        ");

        $stmt->execute([
            'class' => $classId,
        ]);

        return $stmt->fetchAll();
    }

    public function classAttendanceHistory(int $classId): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                id,
                attendance_date,
                notes,
                created_at
            FROM attendance
            WHERE school_class_id = :class_id
            ORDER BY attendance_date DESC, id DESC
            LIMIT 10
        ");

        $stmt->execute([
            'class_id' => $classId,
        ]);

        return $stmt->fetchAll();
    }

    public function insertAttendanceItem(
        int $attendanceId,
        int $studentId,
        string $status
    ): void {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            INSERT INTO attendance_items (
                attendance_id,
                student_id,
                status,
                created_at,
                updated_at
            )
            VALUES (
                :attendance_id,
                :student_id,
                :status,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'attendance_id' => $attendanceId,
            'student_id' => $studentId,
            'status' => $status,
        ]);
    }

    public function dailyCentral(string $date): array
    {
        $classes = $this->dailyRanking($date);

        $totalClasses = count($classes);
        $doneClasses = 0;
        $pendingClasses = 0;

        foreach ($classes as $class) {
            if ((int) ($class['has_attendance'] ?? 0) === 1) {
                $doneClasses++;
            } else {
                $pendingClasses++;
            }
        }

        $summary = $this->schoolFrequencyToday($date);

        return [
            'totalClasses' => $totalClasses,
            'doneClasses' => $doneClasses,
            'pendingClasses' => $pendingClasses,
            'generalPercentage' => (float) ($summary['percentage'] ?? 0),
            'summary' => $summary,
            'classes' => $classes,
        ];
    }

    public function dailyGeneralPercentage(string $date): float
    {
        $summary = $this->schoolFrequencyToday($date);

        return (float) ($summary['percentage'] ?? 0);
    }

    public function dailyRanking(string $date): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                school_classes.id AS class_id,
                school_classes.name AS class_name,
                school_classes.year,
                school_classes.shift,

                COUNT(attendance_items.id) AS total_students,

                SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presentes,

                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS ranking_absences,

                SUM(CASE WHEN attendance_items.status IN ('FJ', 'AM', 'FO') THEN 1 ELSE 0 END) AS attenuated_absences,

                SUM(CASE WHEN attendance_items.status = 'FJ' THEN 1 ELSE 0 END) AS justificadas,

                SUM(CASE WHEN attendance_items.status = 'AM' THEN 1 ELSE 0 END) AS atestados,

                SUM(CASE WHEN attendance_items.status = 'FO' THEN 1 ELSE 0 END) AS onibus,

                SUM(CASE WHEN attendance_items.status <> 'P' THEN 1 ELSE 0 END) AS raw_absences,

                CASE
                    WHEN COUNT(attendance_items.id) = 0
                    THEN NULL
                    ELSE ROUND(
                        (
                            SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                            / COUNT(attendance_items.id)
                        ) * 100,
                        1
                    )
                END AS attendance_percentage,

                CASE
                    WHEN COUNT(attendance_items.id) = 0
                    THEN NULL
                    ELSE ROUND(
                        100 -
                        (
                            (
                                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END)
                                / COUNT(attendance_items.id)
                            ) * 100
                        ),
                        2
                    )
                END AS ife_score,

                CASE
                    WHEN attendance.id IS NULL
                    THEN 0
                    ELSE 1
                END AS has_attendance,

                attendance.id AS attendance_id,
                attendance.created_at AS attendance_created_at

            FROM school_classes

            LEFT JOIN attendance
                ON attendance.school_class_id = school_classes.id
               AND attendance.attendance_date = :date

            LEFT JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id

            WHERE school_classes.active = 1

            GROUP BY
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift,
                attendance.id,
                attendance.created_at

            ORDER BY
                has_attendance DESC,
                ife_score DESC,
                attendance_percentage DESC,
                raw_absences ASC,
                school_classes.name ASC
        ");

        $stmt->execute([
            'date' => $date,
        ]);

        return $stmt->fetchAll();
    }

    public function schoolFrequencyToday(string $date): array
    {
        return $this->frequencyByPeriod($date, $date);
    }

    public function schoolFrequencyWeek(): array
    {
        return $this->frequencyBySqlCondition("
            YEARWEEK(attendance.attendance_date, 1) = YEARWEEK(CURDATE(), 1)
        ");
    }

    public function schoolFrequencyMonth(): array
    {
        return $this->frequencyBySqlCondition("
            YEAR(attendance.attendance_date) = YEAR(CURDATE())
            AND MONTH(attendance.attendance_date) = MONTH(CURDATE())
        ");
    }

    public function schoolFrequencyYear(): array
    {
        return $this->frequencyBySqlCondition("
            YEAR(attendance.attendance_date) = YEAR(CURDATE())
        ");
    }

    public function schoolFrequencyLast30Days(): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                attendance.attendance_date,
                ROUND(
                    (
                        SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                        / COUNT(attendance_items.id)
                    ) * 100,
                    1
                ) AS percentage
            FROM attendance
            INNER JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id
            WHERE attendance.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY attendance.attendance_date
            ORDER BY attendance.attendance_date ASC
        ");

        return $stmt->fetchAll();
    }

    public function classesWithoutAttendanceToday(string $date): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift
            FROM school_classes
            WHERE school_classes.active = 1
              AND school_classes.id NOT IN (
                  SELECT attendance.school_class_id
                  FROM attendance
                  WHERE attendance.attendance_date = :date
              )
            ORDER BY
                school_classes.year DESC,
                school_classes.name ASC
        ");

        $stmt->execute([
            'date' => $date,
        ]);

        return $stmt->fetchAll();
    }

    private function frequencyByPeriod(string $startDate, string $endDate): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                COUNT(attendance_items.id) AS total_students,
                SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS faltas,
                SUM(CASE WHEN attendance_items.status = 'FJ' THEN 1 ELSE 0 END) AS justificadas,
                SUM(CASE WHEN attendance_items.status = 'AM' THEN 1 ELSE 0 END) AS atestados,
                SUM(CASE WHEN attendance_items.status = 'FO' THEN 1 ELSE 0 END) AS onibus,
                ROUND(
                    (
                        SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                        / COUNT(attendance_items.id)
                    ) * 100,
                    1
                ) AS percentage
            FROM attendance
            INNER JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id
            WHERE attendance.attendance_date BETWEEN :start_date AND :end_date
        ");

        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $this->normalizeFrequencyResult($stmt->fetch());
    }

    private function frequencyBySqlCondition(string $condition): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                COUNT(attendance_items.id) AS total_students,
                SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS faltas,
                SUM(CASE WHEN attendance_items.status = 'FJ' THEN 1 ELSE 0 END) AS justificadas,
                SUM(CASE WHEN attendance_items.status = 'AM' THEN 1 ELSE 0 END) AS atestados,
                SUM(CASE WHEN attendance_items.status = 'FO' THEN 1 ELSE 0 END) AS onibus,
                ROUND(
                    (
                        SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                        / COUNT(attendance_items.id)
                    ) * 100,
                    1
                ) AS percentage
            FROM attendance
            INNER JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id
            WHERE {$condition}
        ");

        return $this->normalizeFrequencyResult($stmt->fetch());
    }

    private function normalizeFrequencyResult(array|false $data): array
    {
        if (!$data || (int) ($data['total_students'] ?? 0) === 0) {
            return [
                'total_students' => 0,
                'presentes' => 0,
                'faltas' => 0,
                'justificadas' => 0,
                'atestados' => 0,
                'onibus' => 0,
                'percentage' => 0,
            ];
        }

        return $data;
    }
}