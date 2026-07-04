<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolClassRepository;

class SchoolClassService
{
    private SchoolClassRepository $repository;

    public function __construct(?SchoolClassRepository $repository = null)
    {
        $this->repository = $repository ?? new SchoolClassRepository();
    }

    public function all(): array
    {
        return $this->repository->all();
    }

    public function countActive(): int
    {
        return $this->repository->countActive();
    }

    public function find(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function create(array $data): void
    {
        $this->repository->create($data);
    }

    public function update(int $id, array $data): void
    {
        $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function exists(
        string $name,
        int $year,
        ?int $ignoreId = null
    ): bool {
        return $this->repository->exists($name, $year, $ignoreId);
    }
}