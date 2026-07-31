<?php

declare(strict_types=1);

namespace App\DTOs\Occurrence;

final class OccurrenceAnalysisDTO
{
    public function __construct(
        private readonly int $studentId,

        private readonly bool $recurrent = false,

        private readonly bool $growingSeverity = false,

        private readonly bool $growingFrequency = false,

        private readonly bool $positiveEvolution = false,

        private readonly string $riskLevel = 'LOW',

        private readonly int $riskScore = 0,

        private readonly array $alerts = [],

        private readonly array $insights = [],

        private readonly array $recommendations = [],

        private readonly array $metadata = []
    ) {
    }

    public function studentId(): int
    {
        return $this->studentId;
    }

    public function isRecurrent(): bool
    {
        return $this->recurrent;
    }

    public function hasGrowingSeverity(): bool
    {
        return $this->growingSeverity;
    }

    public function hasGrowingFrequency(): bool
    {
        return $this->growingFrequency;
    }

    public function hasPositiveEvolution(): bool
    {
        return $this->positiveEvolution;
    }

    public function riskLevel(): string
    {
        return $this->riskLevel;
    }

    public function riskScore(): int
    {
        return $this->riskScore;
    }

    public function alerts(): array
    {
        return $this->alerts;
    }

    public function insights(): array
    {
        return $this->insights;
    }

    public function recommendations(): array
    {
        return $this->recommendations;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function hasAlerts(): bool
    {
        return $this->alerts !== [];
    }

    public function hasInsights(): bool
    {
        return $this->insights !== [];
    }

    public function isLowRisk(): bool
    {
        return $this->riskLevel === 'LOW';
    }

    public function isAttentionRisk(): bool
    {
        return $this->riskLevel === 'ATTENTION';
    }

    public function isHighRisk(): bool
    {
        return $this->riskLevel === 'HIGH';
    }

    public function isCriticalRisk(): bool
    {
        return $this->riskLevel === 'CRITICAL';
    }

    public function toArray(): array
    {
        return [
            'student_id' =>
                $this->studentId,

            'recurrent' =>
                $this->recurrent,

            'growing_severity' =>
                $this->growingSeverity,

            'growing_frequency' =>
                $this->growingFrequency,

            'positive_evolution' =>
                $this->positiveEvolution,

            'risk_level' =>
                $this->riskLevel,

            'risk_score' =>
                $this->riskScore,

            'alerts' =>
                $this->alerts,

            'insights' =>
                $this->insights,

            'recommendations' =>
                $this->recommendations,

            'metadata' =>
                $this->metadata,
        ];
    }
}