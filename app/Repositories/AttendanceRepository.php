<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database\Connection;
use PDO;

class AttendanceRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function all(): array
    {
        $stmt = $this->db->query("
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
        $stmt = $this->db->prepare("
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

    public function findByClassAndDate(int $classId, string $date): ?array
    {
        $stmt = $this->db->prepare("
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
        $stmt = $this->db->prepare("
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

        $stmt->execute(['attendance_id' => $attendanceId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
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

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
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

    public function delete(int $id): bool
    {
        $items = $this->db->prepare("
            DELETE FROM attendance_items
            WHERE attendance_id = :id
        ");

        $items->execute(['id' => $id]);

        $attendance = $this->db->prepare("
            DELETE FROM attendance
            WHERE id = :id
        ");

        return $attendance->execute(['id' => $id]);
    }

    public function existsForClassAndDate(int $classId, string $date): bool
    {
        $stmt = $this->db->prepare("
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
        $stmt = $this->db->prepare("
            SELECT id, name, year, shift
            FROM school_classes
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute(['id' => $classId]);

        $class = $stmt->fetch();

        return $class ?: null;
    }

    public function classStudents(int $classId): array
    {
        $stmt = $this->db->prepare("
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

    public function classAttendanceHistory(int $classId): array
    {
        $stmt = $this->db->prepare("
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

        $stmt->execute(['class_id' => $classId]);

        return $stmt->fetchAll();
    }

    public function insertAttendanceItem(
        int $attendanceId,
        int $studentId,
        string $status
    ): void {
        $stmt = $this->db->prepare("
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

    public function updateItemStatus(int $itemId, string $status): void
    {
        $stmt = $this->db->prepare("
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
}