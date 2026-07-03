<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class EnrollmentService
{
    public function all(): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                enrollments.id,
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
            ORDER BY school_classes.year DESC, school_classes.name ASC, students.name ASC
        ");

        return $stmt->fetchAll();
    }

    public function create(array $data): void
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
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
            'school_class_id' => $data['school_class_id'],
            'enrollment_date' => $data['enrollment_date'],
            'active' => 1,
        ]);
    }

    public function exists(int $studentId, int $schoolClassId): bool
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
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

        return (int) $stmt->fetchColumn() > 0;
    }
}