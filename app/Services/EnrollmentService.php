<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EnrollmentRepository;

class EnrollmentService
{
    public function __construct(
        private EnrollmentRepository $repository
    ) {
    }

    public function all(): array
    {
        return $this->repository->all();
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

    public function cancel(int $id): bool
    {
        return $this->repository->cancel($id);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function exists(
        int $studentId,
        int $schoolClassId,
        ?int $ignoreId = null
    ): bool {
        return $this->repository->exists(
            $studentId,
            $schoolClassId,
            $ignoreId
        );
    }
}