<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\StudentRepository;

class StudentService
{
    public function __construct(
        private StudentRepository $repository
    ) {
    }

    public function all(): array
    {
        return $this->repository->all();
    }

    public function countActive(): int
    {
        return $this->repository->countActive();
    }

    public function countInAlert(): int
    {
        return $this->repository->countInAlert();
    }

    public function availableForEnrollment(): array
    {
        return $this->repository->availableForEnrollment();
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

    public function registrationExists(
        string $registration,
        ?int $ignoreId = null
    ): bool {
        return $this->repository->registrationExists(
            $registration,
            $ignoreId
        );
    }
}