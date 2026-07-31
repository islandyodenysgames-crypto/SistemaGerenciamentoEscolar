<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class SchoolYearRepository extends BaseRepository
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT * FROM school_years ORDER BY year DESC, id DESC'
        )->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT * FROM school_years WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $year = $statement->fetch(PDO::FETCH_ASSOC);

        return $year ?: null;
    }

    public function active(): ?array
    {
        $year = $this->db->query(
            'SELECT * FROM school_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1'
        )->fetch(PDO::FETCH_ASSOC);

        return $year ?: null;
    }

    public function yearExists(int $year, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM school_years WHERE year = :year';
        $parameters = ['year' => $year];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $ignoreId;
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $statement = $this->db->prepare(<<<'SQL'
INSERT INTO school_years
    (name, year, start_date, end_date, status, is_active, notes, created_at, updated_at)
VALUES
    (:name, :year, :start_date, :end_date, :status, :is_active, :notes, NOW(), NOW())
SQL);
        $statement->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $statement = $this->db->prepare(<<<'SQL'
UPDATE school_years
SET name = :name,
    year = :year,
    start_date = :start_date,
    end_date = :end_date,
    status = :status,
    is_active = :is_active,
    notes = :notes,
    updated_at = NOW()
WHERE id = :id
SQL);
        $statement->execute($data);
    }

    public function deactivateAllExcept(?int $exceptId = null): void
    {
        if ($exceptId === null) {
            $this->db->exec("UPDATE school_years SET is_active = 0 WHERE is_active = 1");
            return;
        }

        $statement = $this->db->prepare(
            'UPDATE school_years SET is_active = 0 WHERE is_active = 1 AND id <> :id'
        );
        $statement->execute(['id' => $exceptId]);
    }

    public function activate(int $id): void
    {
        $statement = $this->db->prepare(
            "UPDATE school_years SET is_active = 1, status = 'ACTIVE', updated_at = NOW() WHERE id = :id"
        );
        $statement->execute(['id' => $id]);
    }

    public function setStatus(int $id, string $status): void
    {
        $statement = $this->db->prepare(
            'UPDATE school_years SET status = :status, is_active = 0, updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $id, 'status' => $status]);
    }

    public function transaction(callable $operation): mixed
    {
        $this->db->beginTransaction();

        try {
            $result = $operation();
            $this->db->commit();
            return $result;
        } catch (\Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
