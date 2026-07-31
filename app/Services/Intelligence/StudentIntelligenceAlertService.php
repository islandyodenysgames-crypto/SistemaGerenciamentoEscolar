<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Repositories\Intelligence\StudentIntelligenceSnapshotRepository;
use App\Repositories\Monitoring\StudentMonitoringRepository;
use App\Services\Occurrence\NotificationService;

final class StudentIntelligenceAlertService
{
    private const LEVEL_ORDER = ['LOW' => 0, 'MODERATE' => 1, 'HIGH' => 2, 'CRITICAL' => 3];
    private const LEVEL_LABELS = ['LOW' => 'baixo', 'MODERATE' => 'atenção', 'HIGH' => 'alto', 'CRITICAL' => 'crítico'];

    public function __construct(
        private readonly StudentMonitoringRepository $monitoringRepository,
        private readonly StudentIntelligenceSnapshotRepository $snapshotRepository,
        private readonly IntelligenceService $intelligenceService,
        private readonly NotificationService $notificationService
    ) {}

    public function syncForUser(int $userId): int
    {
        if ($userId <= 0) return 0;
        $created = 0;

        foreach ($this->monitoringRepository->activeStudentsForUser($userId) as $student) {
            $studentId = (int)($student['student_id'] ?? 0);
            if ($studentId <= 0) continue;

            $name = trim((string)($student['student_name'] ?? 'Aluno')) ?: 'Aluno';
            $dashboard = $this->intelligenceService->studentDashboard($studentId);
            $risk = $dashboard['risk'] ?? [];
            $level = strtoupper((string)($risk['level'] ?? 'LOW'));
            $score = (int)($risk['score'] ?? 0);
            $latestActionDate = $this->monitoringRepository->latestActionDateForStudent($studentId);
            $snapshot = $this->snapshotRepository->find($userId, $studentId);
            $staleAlertAt = null;

            if ($snapshot !== null) {
                $previousLevel = strtoupper((string)($snapshot['risk_level'] ?? 'LOW'));
                $previousScore = (int)($snapshot['risk_score'] ?? 0);
                $direction = (self::LEVEL_ORDER[$level] ?? 0) <=> (self::LEVEL_ORDER[$previousLevel] ?? 0);

                if ($direction !== 0) {
                    $improved = $direction < 0;
                    $created += $this->notificationService->notifyMonitoringFollowers(
                        studentId: $studentId,
                        studentName: $name,
                        type: $improved ? 'RISK_REDUCED' : 'RISK_INCREASED',
                        title: ($improved ? 'Risco reduzido: ' : 'Risco aumentado: ') . $name,
                        message: sprintf(
                            'O nível de risco escolar mudou de %s para %s. Pontuação atual: %d (anterior: %d).',
                            self::LEVEL_LABELS[$previousLevel] ?? mb_strtolower($previousLevel),
                            self::LEVEL_LABELS[$level] ?? mb_strtolower($level),
                            $score,
                            $previousScore
                        ),
                        severity: $improved ? 'SUCCESS' : ($level === 'CRITICAL' ? 'DANGER' : 'WARNING'),
                        metadata: [
                            'previous_level' => $previousLevel,
                            'current_level' => $level,
                            'previous_score' => $previousScore,
                            'current_score' => $score,
                            'event_id' => $previousLevel . '|' . $level . '|' . date('Y-m-d'),
                        ],
                        eventKey: $improved ? 'risk_reduced' : 'risk_increased'
                    );
                }
            } elseif (in_array($level, ['HIGH', 'CRITICAL'], true)) {
                $created += $this->notificationService->notifyMonitoringFollowers(
                    studentId: $studentId,
                    studentName: $name,
                    type: 'RISK_PRIORITY_IDENTIFIED',
                    title: 'Prioridade identificada: ' . $name,
                    message: 'O aluno está atualmente no nível de risco ' . (self::LEVEL_LABELS[$level] ?? mb_strtolower($level)) . ' e requer revisão do acompanhamento.',
                    severity: $level === 'CRITICAL' ? 'DANGER' : 'WARNING',
                    metadata: ['current_level' => $level, 'current_score' => $score, 'event_id' => 'initial|' . $level],
                    eventKey: 'risk_priority_identified'
                );
            }

            $referenceDate = $latestActionDate ?: (string)($student['start_date'] ?? '');
            if ($referenceDate !== '') {
                $days = max(0, (int)((new \DateTimeImmutable($referenceDate))->diff(new \DateTimeImmutable('today'))->format('%r%a')));
                $lastStale = (string)($snapshot['last_stale_alert_at'] ?? '');
                $canAlertAgain = $lastStale === '' || strtotime($lastStale) <= strtotime('-7 days');
                if ($days >= 14 && $canAlertAgain) {
                    $created += $this->notificationService->notifyMonitoringFollowers(
                        studentId: $studentId,
                        studentName: $name,
                        type: 'MONITORING_STALE',
                        title: 'Acompanhamento sem movimentação: ' . $name,
                        message: $latestActionDate
                            ? 'A última ação foi registrada há ' . $days . ' dias. Revise o caso e registre nova providência quando necessário.'
                            : 'O acompanhamento está ativo há ' . $days . ' dias e ainda não possui ação registrada.',
                        severity: $days >= 30 ? 'DANGER' : 'WARNING',
                        metadata: ['days_without_action' => $days, 'latest_action_date' => $latestActionDate, 'event_id' => 'stale|' . intdiv($days, 7)],
                        eventKey: 'monitoring_stale'
                    );
                    $staleAlertAt = date('Y-m-d H:i:s');
                }
            }

            $this->snapshotRepository->save($userId, $studentId, $level, $score, $latestActionDate, $staleAlertAt);
        }

        return $created;
    }
}
