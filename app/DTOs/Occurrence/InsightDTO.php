<?php

declare(strict_types=1);

namespace App\DTOs\Occurrence;

final class InsightDTO
{
    public function __construct(
        private readonly string $type,

        private readonly string $title,

        private readonly string $message,

        private readonly string $severity = 'INFO',

        private readonly ?string $referenceType = null,

        private readonly ?int $referenceId = null,

        private readonly array $metadata = []
    ) {
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

    public function referenceType(): ?string
    {
        return $this->referenceType;
    }

    public function referenceId(): ?int
    {
        return $this->referenceId;
    }

    public function metadata(): array
    {
        return $this->metadata;
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

    public function toArray(): array
    {
        return [
            'type' =>
                $this->type,

            'title' =>
                $this->title,

            'message' =>
                $this->message,

            'severity' =>
                $this->severity,

            'reference_type' =>
                $this->referenceType,

            'reference_id' =>
                $this->referenceId,

            'metadata' =>
                $this->metadata,
        ];
    }
}