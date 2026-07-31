<?php

declare(strict_types=1);

namespace App\DTOs\Occurrence;

final class TrendDTO
{
    public function __construct(
        private readonly string $type,

        private readonly string $direction,

        private readonly float $variation,

        private readonly string $label,

        private readonly string $description,

        private readonly array $series = [],

        private readonly array $metadata = []
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    public function direction(): string
    {
        return $this->direction;
    }

    public function variation(): float
    {
        return $this->variation;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function series(): array
    {
        return $this->series;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function isGrowing(): bool
    {
        return $this->direction === 'UP';
    }

    public function isStable(): bool
    {
        return $this->direction === 'STABLE';
    }

    public function isDecreasing(): bool
    {
        return $this->direction === 'DOWN';
    }

    public function toArray(): array
    {
        return [
            'type' =>
                $this->type,

            'direction' =>
                $this->direction,

            'variation' =>
                $this->variation,

            'label' =>
                $this->label,

            'description' =>
                $this->description,

            'series' =>
                $this->series,

            'metadata' =>
                $this->metadata,
        ];
    }
}