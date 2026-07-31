<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\DTOs\Occurrence\TimelineEventDTO;
use App\Repositories\Occurrence\NotificationRepository;
use App\Services\StudentService;
use DateTimeImmutable;
use Throwable;

final class TimelineService
{
    public function __construct(
        private readonly OccurrenceService $occurrenceService,
        private readonly ActionService $actionService,
        private readonly StudentService $studentService,
        private readonly NotificationRepository $notificationRepository,
        private readonly SeverityService $severityService
    ) {
    }

    public function forStudent(
        int $studentId,
        int $userId = 0,
        int $limit = 100
    ): array {
        if ($studentId <= 0) {
            return [];
        }

        $events = [];
        $occurrences = $this->occurrenceService->byStudent($studentId);
        $actions = $this->actionService->groupedByStudent($studentId);

        foreach ($occurrences as $occurrence) {
            $event = $this->occurrenceEvent($occurrence);

            if ($event !== null) {
                $events[] = $event;
            }

            $occurrenceId = (int) ($occurrence['id'] ?? 0);

            foreach ($actions[$occurrenceId] ?? [] as $action) {
                $actionEvent = $this->actionEvent($action, $occurrence);

                if ($actionEvent !== null) {
                    $events[] = $actionEvent;
                }
            }
        }

        foreach ($this->studentService->attendanceHistory($studentId) as $attendance) {
            $event = $this->attendanceEvent($attendance);

            if ($event !== null) {
                $events[] = $event;
            }
        }

        if ($userId > 0) {
            foreach ($this->notificationRepository->byStudentForUser(
                $studentId,
                $userId,
                50
            ) as $notification) {
                $event = $this->notificationEvent($notification, $studentId);

                if ($event !== null) {
                    $events[] = $event;
                }
            }
        }

        usort(
            $events,
            static fn (TimelineEventDTO $left, TimelineEventDTO $right): int =>
                $right->occurredAt() <=> $left->occurredAt()
        );

        $limit = max(1, min(300, $limit));

        return array_map(
            static fn (TimelineEventDTO $event): array => $event->toArray(),
            array_slice($events, 0, $limit)
        );
    }

    private function occurrenceEvent(array $item): ?TimelineEventDTO
    {
        $date = $this->date(
            $item['created_at']
                ?? $item['occurrence_date']
                ?? null
        );

        if ($date === null) {
            return null;
        }

        $severity = $this->severityService->normalize(
            $item['severity'] ?? 'LOW'
        );

        return new TimelineEventDTO(
            eventType: 'OCCURRENCE',
            title: (string) ($item['title'] ?? 'Ocorrência registrada'),
            description: (string) ($item['description'] ?? ''),
            occurredAt: $date,
            severity: $severity,
            category: (string) ($item['type_label'] ?? $item['type'] ?? 'Ocorrência'),
            subjectName: $this->nullable($item['subject_name'] ?? null),
            authorName: $this->nullable($item['created_by_name'] ?? null),
            status: (string) ($item['status'] ?? 'OPEN'),
            referenceType: 'OCCURRENCE',
            referenceId: (int) ($item['id'] ?? 0),
            actionLabel: 'Ver ocorrência',
            actionUrl: base_url('ocorrencias/editar?id=' . (int) ($item['id'] ?? 0)),
            metadata: [
                'type_label' => $item['type_label'] ?? null,
                'severity_label' => $item['severity_label'] ?? null,
            ]
        );
    }

    private function actionEvent(array $action, array $occurrence): ?TimelineEventDTO
    {
        $date = $this->date($action['created_at'] ?? null);

        if ($date === null) {
            return null;
        }

        $labels = $this->actionService->actionTypes();
        $type = strtoupper((string) ($action['action_type'] ?? 'OTHER'));
        $status = strtoupper((string) ($action['status_after'] ?? 'OPEN'));

        return new TimelineEventDTO(
            eventType: 'ACTION',
            title: $labels[$type] ?? 'Providência registrada',
            description: (string) ($action['description'] ?? ''),
            occurredAt: $date,
            severity: $status === 'RESOLVED' ? 'SUCCESS' : 'INFO',
            category: 'Providência',
            subjectName: $this->nullable($occurrence['subject_name'] ?? null),
            authorName: $this->nullable($action['created_by_name'] ?? null),
            status: $status,
            referenceType: 'OCCURRENCE_ACTION',
            referenceId: (int) ($action['id'] ?? 0),
            actionLabel: 'Ver ocorrência',
            actionUrl: base_url('ocorrencias/providencia?id=' . (int) ($occurrence['id'] ?? 0)),
            metadata: [
                'occurrence_id' => (int) ($occurrence['id'] ?? 0),
                'occurrence_title' => $occurrence['title'] ?? null,
            ]
        );
    }

    private function attendanceEvent(array $item): ?TimelineEventDTO
    {
        $date = $this->date($item['attendance_date'] ?? null);

        if ($date === null) {
            return null;
        }

        $map = [
            'P' => ['Presença registrada', 'SUCCESS'],
            'F' => ['Falta registrada', 'DANGER'],
            'FJ' => ['Falta justificada', 'WARNING'],
            'AM' => ['Atestado médico', 'INFO'],
            'FO' => ['Falta por transporte', 'INFO'],
        ];

        $status = strtoupper((string) ($item['status'] ?? ''));
        [$title, $severity] = $map[$status] ?? ['Frequência registrada', 'INFO'];
        $className = trim((string) ($item['class_name'] ?? 'Turma não informada'));

        return new TimelineEventDTO(
            eventType: 'ATTENDANCE',
            title: $title,
            description: 'Registro de frequência em ' . $className . '.',
            occurredAt: $date,
            severity: $severity,
            category: 'Frequência',
            status: $status,
            referenceType: 'ATTENDANCE',
            metadata: ['class_name' => $className]
        );
    }

    private function notificationEvent(
        array $item,
        int $studentId
    ): ?TimelineEventDTO {
        $date = $this->date($item['created_at'] ?? null);

        if ($date === null) {
            return null;
        }

        return new TimelineEventDTO(
            eventType: 'NOTIFICATION',
            title: (string) ($item['title'] ?? 'Alerta do sistema'),
            description: (string) ($item['message'] ?? ''),
            occurredAt: $date,
            severity: strtoupper((string) ($item['severity'] ?? 'INFO')),
            category: 'Alerta inteligente',
            status: !empty($item['is_read']) ? 'READ' : 'UNREAD',
            referenceType: 'NOTIFICATION',
            referenceId: (int) ($item['id'] ?? 0),
            actionLabel: 'Ver aluno',
            actionUrl: base_url('alunos/perfil?id=' . $studentId),
            metadata: ['notification_type' => $item['type'] ?? null]
        );
    }

    private function date(mixed $value): ?DateTimeImmutable
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
