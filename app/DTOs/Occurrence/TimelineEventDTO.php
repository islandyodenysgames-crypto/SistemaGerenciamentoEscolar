<?php

declare(strict_types=1);

namespace App\DTOs\Occurrence;

final class TimelineEventDTO
{
    public function __construct(
        private readonly string $eventType,

        private readonly string $title,

        private readonly string $description,

        private readonly \DateTimeImmutable $occurredAt,

        private readonly string $severity = 'INFO',

        private readonly ?string $category = null,

        private readonly ?string $subjectName = null,

        private readonly ?string $authorName = null,

        private readonly ?string $status = null,

        private readonly ?string $referenceType = null,

        private readonly ?int $referenceId = null,

        private readonly ?string $actionLabel = null,

        private readonly ?string $actionUrl = null,

        private readonly array $metadata = []
    ) {
    }

    public function eventType(): string
    {
        return $this->eventType;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function severity(): string
    {
        return $this->severity;
    }

    public function category(): ?string
    {
        return $this->category;
    }

    public function subjectName(): ?string
    {
        return $this->subjectName;
    }

    public function authorName(): ?string
    {
        return $this->authorName;
    }

    public function status(): ?string
    {
        return $this->status;
    }

    public function referenceType(): ?string
    {
        return $this->referenceType;
    }

    public function referenceId(): ?int
    {
        return $this->referenceId;
    }

    public function actionLabel(): ?string
    {
        return $this->actionLabel;
    }

    public function actionUrl(): ?string
    {
        return $this->actionUrl;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function hasAction(): bool
    {
        return $this->actionLabel !== null
            && $this->actionLabel !== ''
            && $this->actionUrl !== null
            && $this->actionUrl !== '';
    }

    public function isOccurrence(): bool
    {
        return $this->eventType === 'OCCURRENCE';
    }

    public function isNotification(): bool
    {
        return $this->eventType === 'NOTIFICATION';
    }

    public function isAttendance(): bool
    {
        return $this->eventType === 'ATTENDANCE';
    }

    public function isInsight(): bool
    {
        return $this->eventType === 'INSIGHT';
    }

    public function toArray(): array
    {
        return [
            'event_type' =>
                $this->eventType,

            'title' =>
                $this->title,

            'description' =>
                $this->description,

            'occurred_at' =>
                $this->occurredAt->format(
                    'Y-m-d H:i:s'
                ),

            'severity' =>
                $this->severity,

            'category' =>
                $this->category,

            'subject_name' =>
                $this->subjectName,

            'author_name' =>
                $this->authorName,

            'status' =>
                $this->status,

            'reference_type' =>
                $this->referenceType,

            'reference_id' =>
                $this->referenceId,

            'action_label' =>
                $this->actionLabel,

            'action_url' =>
                $this->actionUrl,

            'metadata' =>
                $this->metadata,
        ];
    }
}