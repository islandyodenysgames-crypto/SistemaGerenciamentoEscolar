<?php

declare(strict_types=1);

namespace App\ValueObjects\Intelligence;

final class StudentTrend
{
    public function __construct(
        public readonly array $frequency,
        public readonly array $occurrences,
        public readonly array $risk,
        public readonly array $interventions,
        public readonly string $overallStatus,
        public readonly string $summary,
        public readonly string $confidence,
        public readonly int $samples,
        public readonly int $spanDays
    ) {}

    public function toArray(): array
    {
        return [
            'frequency' => $this->frequency,
            'occurrences' => $this->occurrences,
            'risk' => $this->risk,
            'interventions' => $this->interventions,
            'overall_status' => $this->overallStatus,
            'summary' => $this->summary,
            'confidence' => $this->confidence,
            'samples' => $this->samples,
            'span_days' => $this->spanDays,
        ];
    }
}
