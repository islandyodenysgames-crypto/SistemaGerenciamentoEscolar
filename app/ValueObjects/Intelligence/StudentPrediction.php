<?php

declare(strict_types=1);

namespace App\ValueObjects\Intelligence;

final class StudentPrediction
{
    public function __construct(
        public readonly string $status,
        public readonly string $label,
        public readonly string $headline,
        public readonly string $summary,
        public readonly string $currentLevel,
        public readonly int $currentScore,
        public readonly string $projectedLevel,
        public readonly int $projectedScore,
        public readonly int $horizonDays,
        public readonly string $confidence,
        public readonly array $evidence,
        public readonly array $preventiveActions
    ) {}

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'label' => $this->label,
            'headline' => $this->headline,
            'summary' => $this->summary,
            'current_level' => $this->currentLevel,
            'current_score' => $this->currentScore,
            'projected_level' => $this->projectedLevel,
            'projected_score' => $this->projectedScore,
            'horizon_days' => $this->horizonDays,
            'confidence' => $this->confidence,
            'evidence' => $this->evidence,
            'preventive_actions' => $this->preventiveActions,
        ];
    }
}
