<?php

declare(strict_types=1);

namespace App\DTOs\Occurrence;

final class RiskDTO
{
    public function __construct(
        private readonly string $level,

        private readonly int $score,

        private readonly string $label,

        private readonly string $color,

        private readonly string $icon,

        private readonly array $factors = [],

        private readonly array $metadata = []
    ) {
    }

    public function level(): string
    {
        return $this->level;
    }

    public function score(): int
    {
        return $this->score;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function color(): string
    {
        return $this->color;
    }

    public function icon(): string
    {
        return $this->icon;
    }

    public function factors(): array
    {
        return $this->factors;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function isLow(): bool
    {
        return $this->level === 'LOW';
    }

    public function isAttention(): bool
    {
        return $this->level === 'ATTENTION';
    }

    public function isHigh(): bool
    {
        return $this->level === 'HIGH';
    }

    public function isCritical(): bool
    {
        return $this->level === 'CRITICAL';
    }

    public function toArray(): array
    {
        return [
            'level' =>
                $this->level,

            'score' =>
                $this->score,

            'label' =>
                $this->label,

            'color' =>
                $this->color,

            'icon' =>
                $this->icon,

            'factors' =>
                $this->factors,

            'metadata' =>
                $this->metadata,
        ];
    }
}