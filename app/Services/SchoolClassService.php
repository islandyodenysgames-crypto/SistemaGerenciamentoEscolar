<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class SchoolClassService
{
    public function all(): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                id,
                name,
                year,
                shift,
                active,
                created_at
            FROM school_classes
            ORDER BY year DESC, name ASC
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
                year,
                shift,
                active
            FROM school_classes
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $class = $stmt->fetch();

        return $class ?: null;
    }

    public function create(array $data): void
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            INSERT INTO school_classes (
                name,
                year,
                shift,
                active,
                created_at,
                updated_at
            )
            VALUES (
                :name,
                :year,
                :shift,
                :active,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'name'   => $data['name'],
            'year'   => $data['year'],
            'shift'  => $data['shift'],
            'active' => 1,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            UPDATE school_classes
            SET
                name = :name,
                year = :year,
                shift = :shift,
                active = :active,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id'     => $id,
            'name'   => $data['name'],
            'year'   => $data['year'],
            'shift'  => $data['shift'],
            'active' => $data['active'],
        ]);
    }

    public function delete(int $id): bool
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            DELETE FROM school_classes
            WHERE id = :id
        ");

        return $stmt->execute([
            'id' => $id,
        ]);
    }

    public function exists(
        string $name,
        int $year,
        ?int $ignoreId = null
    ): bool
    {
        $db = Connection::getInstance();

        if ($ignoreId === null) {

            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM school_classes
                WHERE name = :name
                  AND year = :year
            ");

            $stmt->execute([
                'name' => $name,
                'year' => $year,
            ]);

        } else {

            $stmt = $db->prepare("
                SELECT COUNT(*)
                FROM school_classes
                WHERE name = :name
                  AND year = :year
                  AND id <> :id
            ");

            $stmt->execute([
                'id'   => $ignoreId,
                'name' => $name,
                'year' => $year,
            ]);

        }

        return (int) $stmt->fetchColumn() > 0;
    }
}