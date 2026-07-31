<?php

declare(strict_types=1);

namespace App\Repositories;

class SchoolClassRepository extends BaseRepository
{

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT id, name, year, shift, active, photo_path, photo_updated_at, created_at
            FROM school_classes
            ORDER BY year DESC, name ASC
        ");

        return $stmt->fetchAll();
    }

    public function countActive(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)
            FROM school_classes
            WHERE active = 1
        ");

        return (int) $stmt->fetchColumn();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT id, name, year, shift, active, photo_path, photo_updated_at
            FROM school_classes
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute(['id' => $id]);

        $class = $stmt->fetch();

        return $class ?: null;
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO school_classes (
                name, year, shift, active, photo_path, photo_updated_at, created_at, updated_at
            )
            VALUES (
                :name, :year, :shift, :active, :photo_path, CASE WHEN :photo_path_stamp IS NULL THEN NULL ELSE NOW() END, NOW(), NOW()
            )
        ");

        $stmt->execute([
            'name' => $data['name'],
            'year' => $data['year'],
            'shift' => $data['shift'],
            'active' => 1,
            'photo_path' => $data['photo_path'] ?? null,
            'photo_path_stamp' => !empty($data['photo_path']) ? 1 : null,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE school_classes
            SET
                name = :name,
                year = :year,
                shift = :shift,
                active = :active,
                photo_path = :photo_path,
                photo_updated_at = CASE WHEN :photo_changed = 1 THEN NOW() ELSE photo_updated_at END,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'year' => $data['year'],
            'shift' => $data['shift'],
            'active' => $data['active'],
            'photo_path' => $data['photo_path'] ?? null,
            'photo_changed' => !empty($data['photo_changed']) ? 1 : 0,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM school_classes
            WHERE id = :id
        ");

        return $stmt->execute(['id' => $id]);
    }

    public function exists(
        string $name,
        int $year,
        ?int $ignoreId = null
    ): bool {
        if ($ignoreId === null) {
            $stmt = $this->db->prepare("
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
            $stmt = $this->db->prepare("
                SELECT COUNT(*)
                FROM school_classes
                WHERE name = :name
                  AND year = :year
                  AND id <> :id
            ");

            $stmt->execute([
                'id' => $ignoreId,
                'name' => $name,
                'year' => $year,
            ]);
        }

        return (int) $stmt->fetchColumn() > 0;
    }
}