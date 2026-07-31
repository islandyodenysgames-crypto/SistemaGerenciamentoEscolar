<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Auth\Roles;
use App\Repositories\Intelligence\IntelligenceRepository;
use App\Repositories\Monitoring\StudentMonitoringRepository;
use App\Repositories\UserRepository;
use App\Services\Occurrence\NotificationService;

final class PreventiveAlertService
{
    public function __construct(
        private readonly StudentMonitoringRepository $monitoringRepository,
        private readonly PredictiveAnalysisService $studentPredictions,
        private readonly ClassPredictiveAnalysisService $classPredictions,
        private readonly IntelligenceRepository $intelligenceRepository,
        private readonly UserRepository $userRepository,
        private readonly NotificationService $notificationService
    ) {}

    public function syncForUser(int $userId): int
    {
        if ($userId <= 0) return 0;

        $created = $this->syncStudentAlerts($userId);
        $user = $this->userRepository->find($userId);
        if ($user && Roles::hasFullAccess((string)($user['role'] ?? ''))) {
            $created += $this->syncClassAlerts($userId);
        }
        return $created;
    }

    private function syncStudentAlerts(int $userId): int
    {
        $created = 0;
        foreach ($this->monitoringRepository->activeStudentsForUser($userId) as $student) {
            $studentId = (int)($student['student_id'] ?? 0);
            if ($studentId <= 0) continue;
            $prediction = $this->studentPredictions->student($studentId, 30, 14);
            $status = strtoupper((string)($prediction['status'] ?? 'INSUFFICIENT'));
            if (!in_array($status, ['DETERIORATION', 'CRITICAL_ESCALATION'], true)) continue;

            $name = trim((string)($student['student_name'] ?? 'Aluno')) ?: 'Aluno';
            $critical = $status === 'CRITICAL_ESCALATION';
            $notificationId = $this->notificationService->notifyUser(
                userId: $userId,
                type: $critical ? 'PREDICTIVE_CRITICAL_ESCALATION' : 'PREDICTIVE_DETERIORATION',
                title: ($critical ? 'Alerta preventivo crítico: ' : 'Alerta preventivo: ') . $name,
                message: (string)($prediction['summary'] ?? 'Os indicadores apontam possibilidade de agravamento nas próximas duas semanas.'),
                severity: $critical ? 'DANGER' : 'WARNING',
                referenceType: 'STUDENT',
                referenceId: $studentId,
                metadata: [
                    'category' => 'INTELLIGENCE',
                    'event' => 'predictive_student_alert',
                    'prediction_status' => $status,
                    'current_level' => $prediction['current_level'] ?? null,
                    'projected_level' => $prediction['projected_level'] ?? null,
                    'horizon_days' => (int)($prediction['horizon_days'] ?? 14),
                    'event_id' => date('o-W') . '|' . $status . '|' . (string)($prediction['projected_level'] ?? ''),
                ],
                eventKey: 'predictive_student_alert'
            );
            if ($notificationId !== null) $created++;
        }
        return $created;
    }

    private function syncClassAlerts(int $userId): int
    {
        $from = date('Y-m-d', strtotime('-29 days'));
        $to = date('Y-m-d');
        $classes = $this->intelligenceRepository->classComparisonSignals($from, $to);
        $created = 0;
        foreach ($classes as $class) {
            $classId = (int)($class['id'] ?? 0);
            if ($classId <= 0) continue;
            $prediction = $this->classPredictions->schoolClass($classId, 30, 14);
            if (strtoupper((string)($prediction['status'] ?? '')) !== 'WORSENING') continue;
            $className = trim((string)($class['name'] ?? 'Turma')) ?: 'Turma';
            $id = $this->notificationService->notifyUser(
                userId: $userId,
                type: 'CLASS_PREDICTIVE_DETERIORATION',
                title: 'Alerta preventivo da turma: ' . $className,
                message: (string)($prediction['summary'] ?? 'A turma apresenta tendência coletiva de agravamento nas próximas duas semanas.'),
                severity: 'WARNING',
                referenceType: 'CLASS',
                referenceId: $classId,
                metadata: [
                    'category' => 'INTELLIGENCE',
                    'event' => 'predictive_class_alert',
                    'class_id' => $classId,
                    'class_name' => $className,
                    'horizon_days' => (int)($prediction['horizon_days'] ?? 14),
                    'event_id' => date('o-W') . '|WORSENING',
                ],
                eventKey: 'predictive_class_alert'
            );
            if ($id !== null) $created++;
        }
        return $created;
    }
}
