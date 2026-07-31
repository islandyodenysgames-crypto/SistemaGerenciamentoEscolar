<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

use App\DTOs\Occurrence\OccurrenceAnalysisDTO;
use DateTimeImmutable;

trait OccurrenceAnalysisOperations
{
    public function analyzeStudent(
        int $studentId,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null
    ): OccurrenceAnalysisDTO {
        $context = $this->analysisContextFactory->createForStudent(
            $studentId,
            $startDate,
            $endDate
        );

        return $this->analysisService->analyze($context);
    }

    public function analyzeStudentAsArray(
        int $studentId,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null
    ): array {
        return $this->analyzeStudent(
            $studentId,
            $startDate,
            $endDate
        )->toArray();
    }
}
