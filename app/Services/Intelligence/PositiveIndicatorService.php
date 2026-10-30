<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Core\Settings\SettingManager;
use App\Repositories\Intelligence\IntelligenceRepository;

final class PositiveIndicatorService
{
    public function __construct(
        private IntelligenceRepository $repository,
        private SettingManager $settings
    ) {
    }

    public function forStudents(array $studentIds, string $from, string $to): array
    {
        return $this->repository->positiveIndicatorsByStudents($studentIds, $from, $to);
    }

    public function forStudent(int $studentId, string $from, string $to): array
    {
        $summary = $this->forStudents([$studentId], $from, $to)[$studentId] ?? [];
        $summary['timeline'] = $this->repository->positiveStudentTimeline($studentId, $from, $to);
        return $this->normalize($summary);
    }

    public function forClass(int $classId, string $from, string $to): array
    {
        return $this->normalize($this->repository->classPositiveIndicators($classId, $from, $to));
    }

    public function forSchool(string $from, string $to): array
    {
        return $this->normalize($this->repository->schoolPositiveIndicators($from, $to));
    }

    /**
     * Aplica somente fatores de proteção relacionados ao sinal analisado.
     * Reconhecimentos isolados valorizam o histórico, mas não reduzem risco.
     */
    public function applyProtection(array $rawRisk, array $signal, array $positive, array $attendanceTrend, array $occurrenceTrend): array
    {
        $rawScore = (int) ($rawRisk['score'] ?? 0);
        $factors = [];

        if (($attendanceTrend['status'] ?? '') === 'IMPROVING'
            && ((int) ($positive['attendance_responses'] ?? 0) > 0 || (int) ($positive['frequency_actions'] ?? 0) > 0)) {
            $factors[] = ['code' => 'ATTENDANCE_IMPROVEMENT', 'label' => 'Frequência melhorou no período com atuação registrada', 'value' => 6];
        }
        if (($occurrenceTrend['status'] ?? '') === 'IMPROVING'
            && ((int) ($positive['behavior_responses'] ?? 0) > 0 || (int) ($positive['behavior_actions'] ?? 0) > 0)) {
            $factors[] = ['code' => 'NO_RECURRENCE', 'label' => 'Ocorrências diminuíram no período com atuação registrada', 'value' => 6];
        }
        $resolved = min(2, (int) ($positive['resolved_adverse'] ?? 0));
        if ($resolved > 0) {
            $factors[] = ['code' => 'RESOLVED_ADVERSE', 'label' => 'Situação adversa resolvida', 'value' => $resolved * 2];
        }

        $maximum = max(0, min(25, (int) $this->settings->get('intelligence.protection_max_reduction', 15)));
        $reduction = min($maximum, array_sum(array_column($factors, 'value')));
        $adjustedScore = max(0, $rawScore - $reduction);

        // Ocorrências graves preservam ao menos o nível de atenção.
        if ((int) ($signal['serious_occurrences'] ?? 0) > 0) {
            $adjustedScore = max($adjustedScore, (int) $this->settings->get('intelligence.risk_moderate_threshold', 20));
        }

        return [
            'raw_score' => $rawScore,
            'raw_level' => (string) ($rawRisk['level'] ?? $this->levelForScore($rawScore)),
            'adjusted_score' => $adjustedScore,
            'reduction' => $rawScore - $adjustedScore,
            'factors' => $factors,
            'level' => $this->levelForScore($adjustedScore),
            'response_strength' => $this->responseStrength($positive, $factors),
            'response_strength_label' => $this->responseStrengthLabel($positive, $factors),
        ];
    }

    private function normalize(array $data): array
    {
        foreach (['recognitions','responses','attendance_responses','behavior_responses','learning_responses','resolved_adverse','monitoring_actions','frequency_actions','behavior_actions','students_recognized','students_with_response','students_with_positive_evidence'] as $key) {
            $data[$key] = (int) ($data[$key] ?? 0);
        }
        return $data;
    }

    private function responseStrength(array $positive, array $factors): string
    {
        $evidence = (int) ($positive['responses'] ?? 0) + (int) ($positive['monitoring_actions'] ?? 0) + count($factors);
        return $evidence >= 5 ? 'STRONG' : ($evidence >= 2 ? 'MODERATE' : ($evidence > 0 ? 'INITIAL' : 'NONE'));
    }

    private function responseStrengthLabel(array $positive, array $factors): string
    {
        return match ($this->responseStrength($positive, $factors)) {
            'STRONG' => 'Forte', 'MODERATE' => 'Moderada', 'INITIAL' => 'Inicial', default => 'Sem resposta registrada',
        };
    }

    private function levelForScore(int $score): string
    {
        $moderate = (int) $this->settings->get('intelligence.risk_moderate_threshold', 20);
        $high = (int) $this->settings->get('intelligence.risk_high_threshold', 45);
        $critical = (int) $this->settings->get('intelligence.risk_critical_threshold', 70);
        return match (true) {
            $score >= $critical => 'CRITICAL', $score >= $high => 'HIGH', $score >= $moderate => 'MODERATE', default => 'LOW',
        };
    }
}
