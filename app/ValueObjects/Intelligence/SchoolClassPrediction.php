<?php

declare(strict_types=1);

namespace App\ValueObjects\Intelligence;

final class SchoolClassPrediction
{
    public function __construct(
        public readonly string $status,
        public readonly string $label,
        public readonly string $summary,
        public readonly string $confidence,
        public readonly int $horizonDays,
        public readonly array $evidence,
        public readonly array $recommendations
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'label' => $this->label,
            'summary' => $this->summary,
            'confidence' => $this->confidence,
            'horizon_days' => $this->horizonDays,
            'evidence' => $this->evidence,
            'recommendations' => $this->recommendations,
        ];
    }
}
