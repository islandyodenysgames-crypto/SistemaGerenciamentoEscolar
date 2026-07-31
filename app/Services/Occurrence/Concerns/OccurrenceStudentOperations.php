<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

trait OccurrenceStudentOperations
{
    public function countByStudent(
            int $studentId
        ): int {
            return $this->repository->countByStudent(
                $studentId
            );
        }

    public function countOpenByStudent(
            int $studentId
        ): int {
            return $this->repository->countOpenByStudent(
                $studentId
            );
        }

    public function countResolvedByStudent(
            int $studentId
        ): int {
            return $this->repository
                ->countResolvedByStudent(
                    $studentId
                );
        }

    public function countByTypeForStudent(
            int $studentId
        ): array {
            return $this->repository
                ->countByTypeForStudent(
                    $studentId
                );
        }

    public function countBySeverityForStudent(
            int $studentId
        ): array {
            return $this->repository
                ->countBySeverityForStudent(
                    $studentId
                );
        }

}
