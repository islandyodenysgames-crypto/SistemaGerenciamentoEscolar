<?php

declare(strict_types=1);

namespace App\DTOs\Occurrence;

final class AnalysisContextDTO
{
    public function __construct(
        private readonly int $studentId,

        private readonly array $student = [],

        private readonly array $occurrences = [],

        private readonly array $statistics = [],

        private readonly array $metadata = [],

        private readonly ?\DateTimeImmutable $startDate = null,

        private readonly ?\DateTimeImmutable $endDate = null
    ) {
    }

    public function studentId(): int
    {
        return $this->studentId;
    }

    public function student(): array
    {
        return $this->student;
    }

    public function occurrences(): array
    {
        return $this->occurrences;
    }

    public function statistics(): array
    {
        return $this->statistics;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function startDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function totalOccurrences(): int
    {
        return count($this->occurrences);
    }

    public function hasOccurrences(): bool
    {
        return $this->occurrences !== [];
    }

    public function hasStudentData(): bool
    {
        return $this->student !== [];
    }

    public function statistic(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->statistics[$key] ?? $default;
    }

    public function metadataValue(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->metadata[$key] ?? $default;
    }

    public function studentValue(
        string $key,
        mixed $default = null
    ): mixed {
        return $this->student[$key] ?? $default;
    }

    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,

            'student' => $this->student,

            'occurrences' => $this->occurrences,

            'statistics' => $this->statistics,

            'metadata' => $this->metadata,

            'start_date' => $this->startDate?->format(
                'Y-m-d H:i:s'
            ),

            'end_date' => $this->endDate?->format(
                'Y-m-d H:i:s'
            ),
        ];
    }
}