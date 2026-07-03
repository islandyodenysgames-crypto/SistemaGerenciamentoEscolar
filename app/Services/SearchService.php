<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class SearchService
{
    public function search(string $term): array
    {
        $term = trim($term);

        if ($term === '') {
            return [
                'students' => [],
                'classes' => [],
                'dates' => [],
            ];
        }

        return [
            'students' => $this->students($term),
            'classes' => $this->classes($term),
            'dates' => $this->dates($term),
        ];
    }

    private function students(string $term): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                students.id,
                students.name,
                students.registration,
                students.active
            FROM students
            WHERE students.name LIKE :term
               OR students.registration LIKE :term
            ORDER BY students.name ASC
            LIMIT 10
        ");

        $stmt->execute([
            'term' => '%' . $term . '%',
        ]);

        return $stmt->fetchAll();
    }

    private function classes(string $term): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift,
                school_classes.active
            FROM school_classes
            WHERE school_classes.name LIKE :term
               OR school_classes.shift LIKE :term
               OR school_classes.year LIKE :term
            ORDER BY school_classes.year DESC, school_classes.name ASC
            LIMIT 10
        ");

        $stmt->execute([
            'term' => '%' . $term . '%',
        ]);

        return $stmt->fetchAll();
    }

    private function dates(string $term): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                attendance.attendance_date,
                COUNT(attendance.id) AS total_attendances
            FROM attendance
            WHERE attendance.attendance_date LIKE :term
            GROUP BY attendance.attendance_date
            ORDER BY attendance.attendance_date DESC
            LIMIT 10
        ");

        $stmt->execute([
            'term' => '%' . $term . '%',
        ]);

        return $stmt->fetchAll();
    }
}