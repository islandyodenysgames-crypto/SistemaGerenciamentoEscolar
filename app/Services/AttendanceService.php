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

        $stmt->execute(['id' => $id]);

        $attendance = $stmt->fetch();

        return $attendance ?: null;
    }

    public function items(int $attendanceId): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                attendance_items.id,
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

        $stmt->execute(['attendance_id' => $attendanceId]);

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

        $stmt->execute(['class' => $classId]);

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

                SUM(CASE WHEN attendance_items.status <> 'P' THEN 1 ELSE 0 END) AS raw_absences,

                ROUND(
                    (
                        SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                        / COUNT(attendance_items.id)
                    ) * 100,
                    1
                ) AS attendance_percentage

            FROM attendance

            INNER JOIN school_classes
                ON school_classes.id = attendance.school_class_id

            INNER JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id

            WHERE attendance.attendance_date = :date

            GROUP BY
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift

            ORDER BY
                ranking_absences ASC,
                attendance_percentage DESC,
                raw_absences ASC,
                school_classes.name ASC
        ");

        $stmt->execute(['date' => $date]);

        return $stmt->fetchAll();
    }

    public function schoolFrequencyToday(string $date): array
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

            WHERE attendance.attendance_date = :date
        ");

        $stmt->execute(['date' => $date]);

        $data = $stmt->fetch();

        return $data ?: [
            'total_students' => 0,
            'presentes' => 0,
            'faltas' => 0,
            'justificadas' => 0,
            'atestados' => 0,
            'onibus' => 0,
            'percentage' => 0,
        ];
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
}