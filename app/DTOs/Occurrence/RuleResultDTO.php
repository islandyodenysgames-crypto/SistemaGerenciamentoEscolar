<?php

declare(strict_types=1);

namespace App\DTOs\Occurrence;

final class RuleResultDTO
{
    public function __construct(
        private readonly string $rule,

        private readonly bool $matched,

        private readonly string $type,

        private readonly string $title,

        private readonly string $message,

        private readonly string $severity = 'INFO',

        private readonly int $score = 0,

        private readonly array $factors = [],

        private readonly array $metadata = []
    ) {
    }

    public function rule(): string
    {
        return $this->rule;
    }

    public function matched(): bool
    {
        return $this->matched;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function severity(): string
    {
        return $this->severity;
    }

    public function score(): int
    {
        return $this->score;
    }

    public function factors(): array
    {
        return $this->factors;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function hasMatched(): bool
    {
        return $this->matched;
    }

    public function hasScore(): bool
    {
        return $this->score !== 0;
    }

    public function isInfo(): bool
    {
        return $this->severity === 'INFO';
    }

    public function isWarning(): bool
    {
        return $this->severity === 'WARNING';
    }

    public function isDanger(): bool
    {
        return $this->severity === 'DANGER';
    }

    public function isSuccess(): bool
    {
        return $this->severity === 'SUCCESS';
    }

    public function toInsightDTO(): InsightDTO
    {
        return new InsightDTO(
            type: $this->type,
            title: $this->title,
            message: $this->message,
            severity: $this->severity,
            metadata: array_merge(
                $this->metadata,
                [
                    'rule' => $this->rule,
                    'score' => $this->score,
                    'factors' => $this->factors,
                ]
            )
        );
    }

    public function toArray(): array
    {
        return [
            'rule' => $this->rule,
            'matched' => $this->matched,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'severity' => $this->severity,
            'score' => $this->score,
            'factors' => $this->factors,
            'metadata' => $this->metadata,
        ];
    }
}