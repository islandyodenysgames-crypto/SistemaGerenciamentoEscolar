<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Repositories\Intelligence\IntelligenceDailySnapshotRepository;
use App\Repositories\Intelligence\IntelligenceRepository;
use PDOException;

final class IntelligenceDailySnapshotService
{
    public function __construct(
        private readonly IntelligenceDailySnapshotRepository $snapshotRepository,
        private readonly IntelligenceRepository $intelligenceRepository,
        private readonly IntelligenceService $intelligenceService
    ) {}

    public function captureToday(bool $force = false): array
    {
        $date = date('Y-m-d');

        try {
            if (!$force && $this->snapshotRepository->hasSchoolSnapshot($date)) {
                return ['status' => 'SKIPPED', 'date' => $date, 'students' => 0, 'message' => 'O snapshot de hoje já existe.'];
            }

            $students = $this->snapshotRepository->activeStudents();
            $studentIds = array_values(array_filter(array_map(
                static fn(array $student): int => (int) ($student['student_id'] ?? 0),
                $students
            )));
            $monitoring = $this->intelligenceRepository->monitoringOperationalSummary($studentIds, 14);
            $monitoringByStudent = (array) ($monitoring['by_student'] ?? []);

            $riskCounts = ['LOW' => 0, 'MODERATE' => 0, 'HIGH' => 0, 'CRITICAL' => 0];
            $combinedRisk = 0;
            $saved = 0;

            foreach ($students as $student) {
                $studentId = (int) ($student['student_id'] ?? 0);
                if ($studentId <= 0) {
                    continue;
                }

                $dashboard = $this->intelligenceService->studentDashboard($studentId);
                $risk = (array) ($dashboard['risk'] ?? []);
                $attendance = (array) ($dashboard['attendance'] ?? []);
                $occurrences = (array) ($dashboard['occurrences'] ?? []);
                $operational = (array) ($monitoringByStudent[$studentId] ?? []);

                $records = (int) ($attendance['total_records'] ?? 0);
                $presences = (int) ($attendance['presences'] ?? 0);
                $attendancePercentage = $records > 0 ? round(($presences / $records) * 100, 2) : null;
                $riskLevel = strtoupper((string) ($risk['level'] ?? 'LOW'));
                if (!isset($riskCounts[$riskLevel])) {
                    $riskLevel = 'LOW';
                }
                $riskCounts[$riskLevel]++;

                $unjustified = (int) ($attendance['unjustified_absences'] ?? 0);
                $totalOccurrences = (int) ($occurrences['total'] ?? 0);
                if ($unjustified > 0 && $totalOccurrences > 0) {
                    $combinedRisk++;
                }

                $this->snapshotRepository->saveStudent([
                    'snapshot_date' => $date,
                    'student_id' => $studentId,
                    'class_id' => !empty($student['class_id']) ? (int) $student['class_id'] : null,
                    'class_name' => ($student['class_name'] ?? null) !== null ? (string) $student['class_name'] : null,
                    'analysis_window_days' => (int) ($dashboard['period']['days'] ?? 30),
                    'risk_level' => $riskLevel,
                    'risk_score' => (int) ($risk['score'] ?? 0),
                    'attendance_records' => $records,
                    'presences' => $presences,
                    'attendance_percentage' => $attendancePercentage,
                    'unjustified_absences' => $unjustified,
                    'attenuated_absences' => (int) ($attendance['attenuated_absences'] ?? 0),
                    'total_occurrences' => $totalOccurrences,
                    'serious_occurrences' => (int) ($occurrences['serious'] ?? 0),
                    'open_occurrences' => (int) ($occurrences['open'] ?? 0),
                    'has_active_monitoring' => !empty($operational['has_active_monitoring']) ? 1 : 0,
                    'active_followers' => (int) ($operational['active_followers'] ?? 0),
                    'pending_recommendations' => (int) ($operational['pending_recommendations'] ?? 0),
                    'latest_action_date' => $operational['latest_action_date'] ?? null,
                    'days_without_action' => $operational['days_without_action'] ?? null,
                ]);
                $saved++;
            }

            $school = $this->intelligenceService->dashboard();
            $summary = (array) ($school['summary'] ?? []);
            $attendance = (array) ($school['attendance'] ?? []);
            $occurrences = (array) ($school['occurrences'] ?? []);
            $schoolMonitoring = (array) ($school['monitoring'] ?? []);

            $this->snapshotRepository->saveSchool([
                'snapshot_date' => $date,
                'analysis_window_days' => (int) ($school['period']['days'] ?? 30),
                'active_students' => count($students),
                'priority_students' => $riskCounts['MODERATE'] + $riskCounts['HIGH'] + $riskCounts['CRITICAL'],
                'critical_students' => $riskCounts['CRITICAL'],
                'high_risk_students' => $riskCounts['HIGH'],
                'moderate_risk_students' => $riskCounts['MODERATE'],
                'attention_classes' => (int) ($summary['classes_in_attention'] ?? 0),
                'combined_risk_students' => $combinedRisk,
                'stale_critical_occurrences' => (int) ($summary['stale_critical_occurrences'] ?? 0),
                'unjustified_absences' => (int) ($attendance['unjustified_absences'] ?? 0),
                'students_with_unjustified_absence' => (int) ($attendance['students_affected'] ?? 0),
                'total_occurrences' => (int) ($occurrences['total'] ?? 0),
                'serious_occurrences' => (int) ($occurrences['serious'] ?? 0),
                'open_occurrences' => (int) ($occurrences['open'] ?? 0),
                'monitoring_coverage_percentage' => (int) ($schoolMonitoring['coverage_percentage'] ?? 0),
                'covered_cases' => (int) ($schoolMonitoring['covered_cases'] ?? 0),
                'without_follower' => (int) ($schoolMonitoring['without_follower'] ?? 0),
                'without_recent_action' => (int) ($schoolMonitoring['without_recent_action'] ?? 0),
                'pending_recommendations' => (int) ($schoolMonitoring['pending_recommendations'] ?? 0),
            ]);

            return ['status' => 'CAPTURED', 'date' => $date, 'students' => $saved, 'message' => 'Snapshot diário registrado com sucesso.'];
        } catch (PDOException $exception) {
            if ($exception->getCode() === '42S02' || str_contains(mb_strtolower($exception->getMessage()), 'doesn\'t exist') || str_contains(mb_strtolower($exception->getMessage()), 'not found') || str_contains(mb_strtolower($exception->getMessage()), 'não existe')) {
                return ['status' => 'MIGRATION_REQUIRED', 'date' => $date, 'students' => 0, 'message' => 'Execute as migrações antes de capturar snapshots.'];
            }
            throw $exception;
        }
    }
}
