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
                id,
                name,
                registration,
                birth_date,
                guardian_name,
                guardian_phone,
                active,
                created_at
            FROM students
            ORDER BY name ASC
        ");

        return $stmt->fetchAll();
    }
}