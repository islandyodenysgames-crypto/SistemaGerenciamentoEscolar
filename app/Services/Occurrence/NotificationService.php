<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\DTOs\Occurrence\NotificationDTO;
use App\DTOs\Occurrence\RuleResultDTO;
use App\Repositories\Occurrence\NotificationRepository;
use App\Repositories\Monitoring\StudentMonitoringRepository;
use App\Repositories\UserRepository;
use App\Core\Settings\SettingManager;
use App\Database\Connection;
use InvalidArgumentException;

final class NotificationService
{
    private const SEVERITIES = [
        'INFO',
        'WARNING',
        'DANGER',
        'SUCCESS',
    ];

    public function __construct(
        private readonly NotificationRepository $repository,
        private readonly OccurrenceService $occurrenceService,
        private readonly SeverityService $severityService,
        private readonly StudentMonitoringRepository $monitoringRepository,
        private readonly UserRepository $userRepository,
        private readonly SettingManager $settings
    ) {
    }

    public function allForUser(
        int $userId,
        int $limit = 100,
        string $category = 'ALL',
        string $status = 'ALL'
    ): array {
        $this->validateUserId($userId);

        $items = array_map(
            fn (array $item): array => $this->decorate($item),
            $this->repository->allByUser($userId, $limit)
        );

        $category = strtoupper(trim($category));
        $status = strtoupper(trim($status));

        return array_values(array_filter($items, static function (array $item) use ($category, $status): bool {
            if ($status === 'UNREAD' && !empty($item['is_read'])) return false;
            if ($status === 'READ' && empty($item['is_read'])) return false;
            return $category === 'ALL' || ($item['category'] ?? 'SYSTEM') === $category;
        }));
    }

    public function summaryForUser(int $userId): array
    {
        $this->validateUserId($userId);

        return [
            'unread_count' =>
                $this->repository->countUnreadByUser($userId),
            'items' => array_map(
                fn (array $item): array => $this->decorate($item),
                $this->repository->recentByUser($userId, 5)
            ),
        ];
    }

    public function generateForStudent(
        int $userId,
        int $studentId
    ): int {
        $this->validateUserId($userId);

        if ($studentId <= 0) {
            throw new InvalidArgumentException(
                'O aluno informado é inválido.'
            );
        }

        $analysis = $this->occurrenceService
            ->analyzeStudent($studentId);

        $created = 0;

        foreach ($analysis->alerts() as $alert) {
            if (!$alert instanceof RuleResultDTO) {
                continue;
            }

            $notification = $this->fromRuleResult(
                $userId,
                $studentId,
                $alert
            );

            $data = $notification->toArray();
            $data['fingerprint'] = $this->fingerprint(
                $userId,
                $studentId,
                $alert
            );
            $data['metadata'] = json_encode(
                $data['metadata'],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_THROW_ON_ERROR
            );

            if ($this->repository->createIfMissing($data) !== null) {
                $created++;
            }
        }

        return $created;
    }


    /**
     * Registra uma notificação informativa para cada nova ocorrência.
     */
    public function notifyOccurrenceCreated(
        int $userId,
        int $studentId,
        string $studentName,
        mixed $occurrenceSeverity,
        string $teacherName,
        ?int $occurrenceId = null
    ): ?int {
        $this->validateUserId($userId);

        if ($studentId <= 0) {
            throw new InvalidArgumentException(
                'O aluno informado é inválido.'
            );
        }

        $studentName = trim($studentName);
        $teacherName = trim($teacherName);

        if ($studentName === '') {
            $studentName = 'Aluno não identificado';
        }

        if ($teacherName === '') {
            $teacherName = 'Professor não identificado';
        }

        $severity = $this->severityService->normalize(
            $occurrenceSeverity
        );

        if (in_array($severity, [SeverityService::HIGH, SeverityService::CRITICAL], true)
            && !(bool) $this->settings->get('intelligence.notification_critical_occurrence', true)) {
            return null;
        }

        $severityLabel = $this->severityService->label(
            $severity
        );

        $notification = new NotificationDTO(
            userId: $userId,
            type: 'OCCURRENCE_CREATED',
            title: 'Nova ocorrência: ' . $studentName,
            message: sprintf(
                'O aluno %s recebeu uma ocorrência de gravidade %s, registrada pelo professor %s.',
                $studentName,
                $severityLabel,
                $teacherName
            ),
            severity: $this->notificationSeverityFromOccurrence(
                $severity
            ),
            referenceType: 'STUDENT',
            referenceId: $studentId,
            metadata: [
                'event' => 'occurrence_created',
                'occurrence_id' => $occurrenceId,
                'student_id' => $studentId,
                'student_name' => $studentName,
                'occurrence_severity' => $severity,
                'occurrence_severity_label' => $severityLabel,
                'teacher_name' => $teacherName,
            ]
        );

        $data = $notification->toArray();
        $data['fingerprint'] = $this->occurrenceFingerprint(
            $userId,
            $studentId,
            $occurrenceId
        );
        $data['metadata'] = json_encode(
            $data['metadata'],
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_THROW_ON_ERROR
        );

        return $this->repository->createIfMissing($data);
    }

    public function notifyFollowersOccurrenceCreated(
        int $studentId,
        string $studentName,
        mixed $occurrenceSeverity,
        string $teacherName,
        ?int $occurrenceId = null,
        ?int $exceptUserId = null
    ): int {
        $created = 0;
        foreach ($this->monitoringRepository->activeUserIdsByStudent($studentId) as $userId) {
            if ($exceptUserId !== null && $userId === $exceptUserId) continue;
            if ($this->notifyOccurrenceCreated($userId, $studentId, $studentName, $occurrenceSeverity, $teacherName, $occurrenceId) !== null) $created++;
            $this->generateForStudent($userId, $studentId);
        }
        return $created;
    }



    public function notifyRecurrenceFollowers(
        int $studentId,
        string $studentName,
        string $occurrenceType,
        int $total,
        int $periodDays,
        ?int $occurrenceId = null,
        ?int $exceptUserId = null
    ): int {
        $minimum = max(2, (int) $this->settings->get('intelligence.recurrence_limit', 2));
        if (!(bool) $this->settings->get('intelligence.notification_recurrent_student', true) || $total < $minimum) {
            return 0;
        }

        $severity = $total >= ($minimum * 3) ? 'DANGER' : 'WARNING';
        $title = 'Reincidência detectada: ' . $studentName;
        $message = sprintf(
            '%s possui %d registros reincidentes pelo critério: %s, nos últimos %d dias.',
            $studentName,
            $total,
            $occurrenceType,
            $periodDays
        );

        return $this->notifyMonitoringFollowers(
            studentId: $studentId,
            studentName: $studentName,
            type: 'STUDENT_RECURRENCE',
            title: $title,
            message: $message,
            severity: $severity,
            metadata: [
                'occurrence_type' => $occurrenceType,
                'total_occurrences' => $total,
                'period_days' => $periodDays,
                'occurrence_id' => $occurrenceId,
                'event_id' => $occurrenceId,
            ],
            exceptUserId: $exceptUserId,
            eventKey: 'student_recurrence_' . $occurrenceType
        );
    }

    public function notifyCriticalClassForStudent(int $studentId, ?int $exceptUserId = null): int
    {
        if (!(bool) $this->settings->get('intelligence.notification_critical_class', true) || $studentId <= 0) {
            return 0;
        }

        $window = max(1, (int) $this->settings->get('intelligence.analysis_window_days', 30));
        $threshold = max(1, (int) $this->settings->get('intelligence.class_attention_threshold', 20));
        $from = date('Y-m-d', strtotime('-' . ($window - 1) . ' days'));
        $to = date('Y-m-d');
        $db = Connection::getInstance();
        $stmt = $db->prepare("
            SELECT sc.id, sc.name,
                   COALESCE(att.unjustified_absences, 0) AS unjustified_absences,
                   COALESCE(occ.total_occurrences, 0) AS total_occurrences,
                   COALESCE(occ.serious_occurrences, 0) AS serious_occurrences
            FROM enrollments e
            INNER JOIN school_classes sc ON sc.id = e.school_class_id
            LEFT JOIN (
                SELECT a.school_class_id,
                       SUM(CASE WHEN ai.status = 'F' THEN 1 ELSE 0 END) AS unjustified_absences
                FROM attendance a
                INNER JOIN attendance_items ai ON ai.attendance_id = a.id
                WHERE a.attendance_date BETWEEN :from_date AND :to_date
                GROUP BY a.school_class_id
            ) att ON att.school_class_id = sc.id
            LEFT JOIN (
                SELECT e2.school_class_id,
                       COUNT(DISTINCT so.id) AS total_occurrences,
                       COUNT(DISTINCT CASE WHEN UPPER(so.severity) IN ('HIGH','CRITICAL') THEN so.id END) AS serious_occurrences
                FROM enrollments e2
                INNER JOIN student_occurrences so ON so.student_id = e2.student_id
                WHERE e2.active = 1 AND so.occurrence_date BETWEEN :from_date2 AND :to_date2
                GROUP BY e2.school_class_id
            ) occ ON occ.school_class_id = sc.id
            WHERE e.student_id = :student_id AND e.active = 1
            LIMIT 1
        ");
        $stmt->execute([
            'from_date' => $from,
            'to_date' => $to,
            'from_date2' => $from,
            'to_date2' => $to,
            'student_id' => $studentId,
        ]);
        $class = $stmt->fetch();
        if (!$class) return 0;

        $score = min(100,
            ((int)($class['unjustified_absences'] ?? 0) * 3)
            + ((int)($class['total_occurrences'] ?? 0) * 4)
            + ((int)($class['serious_occurrences'] ?? 0) * 12)
        );
        if ($score < $threshold) return 0;

        $recipients = array_values(array_unique(array_merge(
            $this->userRepository->activeRecipientIds('ADMINISTRATION'),
            $this->userRepository->activeRecipientIds('COORDINATION')
        )));
        $created = 0;
        foreach ($recipients as $userId) {
            if ($exceptUserId !== null && (int)$userId === $exceptUserId) continue;
            if ($this->notifyUser(
                userId: (int)$userId,
                type: 'CRITICAL_CLASS',
                title: 'Turma em atenção: ' . (string)$class['name'],
                message: sprintf('%s atingiu %d pontos de risco no período de %d dias, acima do limite configurado de %d.', (string)$class['name'], $score, $window, $threshold),
                severity: $score >= 70 ? 'DANGER' : 'WARNING',
                referenceType: 'CLASS',
                referenceId: (int)$class['id'],
                metadata: ['category' => 'INTELLIGENCE', 'risk_score' => $score, 'threshold' => $threshold, 'event_id' => date('Y-m-d')],
                eventKey: 'critical_class'
            ) !== null) $created++;
        }
        return $created;
    }

    public function notifyUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        string $severity = 'INFO',
        string $referenceType = 'SYSTEM',
        ?int $referenceId = null,
        array $metadata = [],
        ?string $eventKey = null
    ): ?int {
        $this->validateUserId($userId);
        $notification = new NotificationDTO(
            userId: $userId,
            type: $type,
            title: $title,
            message: $message,
            severity: $this->normalizeSeverity($severity),
            referenceType: strtoupper($referenceType),
            referenceId: $referenceId,
            metadata: array_merge($metadata, ['event' => $eventKey ?? strtolower($type)])
        );
        $data = $notification->toArray();
        $data['fingerprint'] = hash('sha256', implode('|', [
            'direct', (string)$userId, $type, strtoupper($referenceType),
            (string)($referenceId ?? 0), $eventKey ?? '',
            (string)($metadata['event_id'] ?? date('Y-m-d'))
        ]));
        $data['metadata'] = json_encode($data['metadata'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        return $this->repository->createIfMissing($data);
    }


    public function notifyMonitoringFollowers(
        int $studentId,
        string $studentName,
        string $type,
        string $title,
        string $message,
        string $severity = 'INFO',
        array $metadata = [],
        ?int $exceptUserId = null,
        ?string $eventKey = null
    ): int {
        if ($studentId <= 0) {
            throw new InvalidArgumentException('O aluno informado é inválido.');
        }

        $created = 0;
        foreach ($this->monitoringRepository->activeUserIdsByStudent($studentId) as $userId) {
            if ($exceptUserId !== null && $userId === $exceptUserId) {
                continue;
            }

            $notification = new NotificationDTO(
                userId: $userId,
                type: $type,
                title: $title,
                message: $message,
                severity: $this->normalizeSeverity($severity),
                referenceType: 'STUDENT',
                referenceId: $studentId,
                metadata: array_merge($metadata, [
                    'event' => $eventKey ?? strtolower($type),
                    'student_id' => $studentId,
                    'student_name' => $studentName,
                ])
            );

            $data = $notification->toArray();
            $data['fingerprint'] = hash('sha256', implode('|', [
                'monitoring',
                (string) $userId,
                (string) $studentId,
                $type,
                $eventKey ?? '',
                (string) ($metadata['event_id'] ?? $metadata['action_id'] ?? bin2hex(random_bytes(8))),
            ]));
            $data['metadata'] = json_encode(
                $data['metadata'],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            );

            if ($this->repository->createIfMissing($data) !== null) {
                $created++;
            }
        }

        return $created;
    }



    public function notifyAttendanceStatusFollowers(int $studentId, string $studentName, string $status): int
    {
        $today = date('Y-m-d');
        $currentStart = date('Y-m-d', strtotime('-29 days'));
        $previousEnd = date('Y-m-d', strtotime('-30 days'));
        $previousStart = date('Y-m-d', strtotime('-59 days'));
        $current = $this->monitoringRepository->periodMetrics($studentId, $currentStart, $today);
        $previous = $this->monitoringRepository->periodMetrics($studentId, $previousStart, $previousEnd);

        $currentRecords = (int)($current['total_records'] ?? 0);
        $previousRecords = (int)($previous['total_records'] ?? 0);
        $currentRate = (float)($current['attendance_percentage'] ?? 0);
        $previousRate = (float)($previous['attendance_percentage'] ?? 0);
        $difference = $currentRate - $previousRate;

        if ($currentRecords >= 3 && $previousRecords >= 3 && abs($difference) >= 3.0) {
            $improved = $difference > 0;
            return $this->notifyMonitoringFollowers(
                studentId: $studentId,
                studentName: $studentName,
                type: $improved ? 'FREQUENCY_IMPROVED' : 'FREQUENCY_WORSENED',
                title: ($improved ? 'Frequência melhorou: ' : 'Frequência piorou: ') . $studentName,
                message: sprintf(
                    'A frequência dos últimos 30 dias %s de %.1f%% para %.1f%% em comparação com o período anterior.',
                    $improved ? 'subiu' : 'caiu',
                    $previousRate,
                    $currentRate
                ),
                severity: $improved ? 'SUCCESS' : 'DANGER',
                metadata: [
                    'previous_rate' => $previousRate,
                    'current_rate' => $currentRate,
                    'difference' => $difference,
                    'event_id' => date('Y-m-d') . '|' . round($currentRate, 1),
                ],
                eventKey: $improved ? 'frequency_improved' : 'frequency_worsened'
            );
        }

        $positive = strtoupper($status) === 'P';
        return $this->notifyMonitoringFollowers(
            studentId: $studentId,
            studentName: $studentName,
            type: $positive ? 'ATTENDANCE_POSITIVE_SIGNAL' : 'ATTENDANCE_NEGATIVE_SIGNAL',
            title: ($positive ? 'Novo registro positivo: ' : 'Atenção à frequência: ') . $studentName,
            message: $positive
                ? 'Uma nova presença foi registrada. Ainda não há dados suficientes para confirmar uma tendência de melhora.'
                : 'Uma nova ausência foi registrada. Ainda não há dados suficientes para confirmar uma tendência de piora.',
            severity: $positive ? 'SUCCESS' : 'WARNING',
            metadata: ['attendance_status' => strtoupper($status), 'event_id' => date('Y-m-d') . '|' . strtoupper($status)],
            eventKey: $positive ? 'attendance_positive' : 'attendance_negative'
        );
    }

    public function notifyNotice(array $notice): int
    {
        $noticeId = (int)($notice['id'] ?? 0);
        if ($noticeId <= 0 || empty($notice['active'])) return 0;

        $publishedAt = (string)($notice['published_at'] ?? '');
        if ($publishedAt !== '' && strtotime($publishedAt) > time()) return 0;

        $target = strtoupper((string)($notice['target'] ?? 'ALL'));
        // Avisos exclusivos do Painel TV são conteúdo institucional público e
        // não devem gerar notificações individuais para usuários do sistema.
        if ($target === 'TV_PANEL') return 0;

        $priority = strtoupper((string)($notice['priority'] ?? 'INFO'));
        $severity = match ($priority) {
            'URGENT' => 'DANGER',
            'IMPORTANT' => 'WARNING',
            default => 'INFO',
        };
        $created = 0;
        foreach ($this->userRepository->activeRecipientIds($target) as $userId) {
            if ((int)($notice['created_by'] ?? 0) === $userId) continue;
            $dto = new NotificationDTO(
                userId: $userId,
                type: 'MANAGEMENT_NOTICE',
                title: 'Novo aviso da gestão: ' . trim((string)($notice['title'] ?? 'Aviso')),
                message: mb_strimwidth(trim(strip_tags((string)($notice['content'] ?? ''))), 0, 220, '…'),
                severity: $severity,
                referenceType: 'NOTICE',
                referenceId: $noticeId,
                metadata: ['event' => 'management_notice', 'notice_id' => $noticeId, 'category' => 'MANAGEMENT']
            );
            $data = $dto->toArray();
            $data['fingerprint'] = hash('sha256', "notice|{$noticeId}|{$userId}");
            $data['metadata'] = json_encode($data['metadata'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
            if ($this->repository->createIfMissing($data) !== null) $created++;
        }
        return $created;
    }

    public function openDestination(int $id, int $userId): string
    {
        $item = $this->repository->findForUser($id, $userId);
        if (!$item) return base_url('notificacoes');
        $this->repository->markAsRead($id, $userId);
        $metadata = $item['metadata'] ?? [];
        if (is_string($metadata)) $metadata = json_decode($metadata, true) ?: [];
        $type = strtoupper((string)($item['reference_type'] ?? ''));
        $referenceId = (int)($item['reference_id'] ?? 0);
        if ($type === 'NOTICE' && $referenceId > 0) return base_url('avisos#aviso-' . $referenceId);
        if ($type === 'CLASS' && $referenceId > 0) return base_url('alunos/turma?id=' . $referenceId . '#classIntelligence');
        if (($metadata['event'] ?? '') === 'occurrence_created' && $referenceId > 0) return base_url('alunos/perfil?id=' . $referenceId . '#ocorrencias');
        if ($referenceId > 0 && str_starts_with((string)($item['type'] ?? ''), 'MONITORING_')) return base_url('acompanhamentos#aluno-' . $referenceId);
        if ($referenceId > 0) return base_url('alunos/perfil?id=' . $referenceId . '#inteligencia');
        return base_url('notificacoes');
    }

    public function markAsRead(
        int $id,
        int $userId
    ): bool {
        return $id > 0
            && $userId > 0
            && $this->repository->markAsRead($id, $userId);
    }

    public function markAllAsRead(int $userId): bool
    {
        $this->validateUserId($userId);

        return $this->repository->markAllAsRead($userId);
    }

    public function delete(
        int $id,
        int $userId
    ): bool {
        return $id > 0
            && $userId > 0
            && $this->repository->deleteForUser($id, $userId);
    }

    private function fromRuleResult(
        int $userId,
        int $studentId,
        RuleResultDTO $result
    ): NotificationDTO {
        return new NotificationDTO(
            userId: $userId,
            type: $result->type(),
            title: $result->title(),
            message: $result->message(),
            severity: $this->normalizeSeverity(
                $result->severity()
            ),
            referenceType: 'STUDENT',
            referenceId: $studentId,
            metadata: array_merge(
                $result->metadata(),
                [
                    'rule' => $result->rule(),
                    'score' => $result->score(),
                    'factors' => $result->factors(),
                ]
            )
        );
    }

    private function fingerprint(
        int $userId,
        int $studentId,
        RuleResultDTO $result
    ): string {
        return hash(
            'sha256',
            json_encode([
                'user_id' => $userId,
                'student_id' => $studentId,
                'rule' => $result->rule(),
                'type' => $result->type(),
                'score' => $result->score(),
                'metadata' => $result->metadata(),
            ], JSON_THROW_ON_ERROR)
        );
    }


    private function occurrenceFingerprint(
        int $userId,
        int $studentId,
        ?int $occurrenceId
    ): string {
        return hash(
            'sha256',
            implode('|', [
                'occurrence_created',
                (string) $userId,
                (string) $studentId,
                $occurrenceId !== null && $occurrenceId > 0
                    ? (string) $occurrenceId
                    : bin2hex(random_bytes(16)),
            ])
        );
    }

    private function notificationSeverityFromOccurrence(
        string $severity
    ): string {
        return match ($severity) {
            SeverityService::CRITICAL,
            SeverityService::HIGH => 'DANGER',
            SeverityService::MEDIUM => 'WARNING',
            default => 'INFO',
        };
    }

    private function decorate(array $item): array
    {
        $severity = $this->normalizeSeverity(
            (string) ($item['severity'] ?? 'INFO')
        );

        $metadata = $item['metadata'] ?? [];

        if (is_string($metadata) && $metadata !== '') {
            $decoded = json_decode($metadata, true);
            $metadata = is_array($decoded) ? $decoded : [];
        }

        return [
            ...$item,
            'id' => (int) ($item['id'] ?? 0),
            'user_id' => (int) ($item['user_id'] ?? 0),
            'reference_id' => isset($item['reference_id'])
                ? (int) $item['reference_id']
                : null,
            'is_read' => (bool) ($item['is_read'] ?? false),
            'severity' => $severity,
            'severity_label' => match ($severity) {
                'DANGER' => 'Crítica',
                'WARNING' => 'Atenção',
                'SUCCESS' => 'Positiva',
                default => 'Informativa',
            },
            'severity_icon' => match ($severity) {
                'DANGER' => 'triangle-alert',
                'WARNING' => 'circle-alert',
                'SUCCESS' => 'circle-check',
                default => 'info',
            },
            'metadata' => $metadata,
            'category' => $this->categoryFor((string)($item['type'] ?? ''), (string)($item['reference_type'] ?? ''), $metadata),
            'destination_url' => base_url('notificacoes/abrir?id=' . (int)($item['id'] ?? 0)),
        ];
    }


    private function categoryFor(string $type, string $referenceType, array $metadata): string
    {
        if ($referenceType === 'NOTICE' || ($metadata['category'] ?? '') === 'MANAGEMENT') return 'MANAGEMENT';
        if (($metadata['category'] ?? '') === 'INTELLIGENCE' || str_contains($type, 'PREDICTIVE')) return 'INTELLIGENCE';
        if (str_contains($type, 'OCCURRENCE')) return 'OCCURRENCE';
        if (str_contains($type, 'MONITORING') || str_contains($type, 'ACTION')) return 'MONITORING';
        if (str_contains($type, 'ATTENDANCE') || str_contains($type, 'FREQUENCY') || str_contains($type, 'RISK') || str_contains($type, 'STALE') || isset($metadata['rule'])) return 'INTELLIGENCE';
        return 'SYSTEM';
    }

    private function normalizeSeverity(string $severity): string
    {
        $severity = strtoupper(trim($severity));

        return in_array($severity, self::SEVERITIES, true)
            ? $severity
            : 'INFO';
    }

    private function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException(
                'O usuário informado é inválido.'
            );
        }
    }
}
