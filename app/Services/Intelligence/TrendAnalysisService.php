<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Repositories\Intelligence\IntelligenceDailySnapshotRepository;
use App\ValueObjects\Intelligence\StudentTrend;

final class TrendAnalysisService
{
    public function __construct(private readonly IntelligenceDailySnapshotRepository $snapshots)
    {
    }

    public function student(int $studentId, int $days = 30): array
    {
        $rows = $this->snapshots->recentStudentSnapshots($studentId, max(2, min(90, $days + 1)));
        return $this->fromSnapshots($rows)->toArray();
    }

    public function students(array $studentIds, int $days = 30): array
    {
        $result = [];
        foreach (array_unique(array_map('intval', $studentIds)) as $studentId) {
            if ($studentId > 0) {
                $result[$studentId] = $this->student($studentId, $days);
            }
        }
        return $result;
    }

    public function fromSnapshots(array $rows): StudentTrend
    {
        $rows = array_values(array_filter($rows, static fn(array $row): bool => !empty($row['snapshot_date'])));
        usort($rows, static fn(array $a, array $b): int => strcmp((string) $a['snapshot_date'], (string) $b['snapshot_date']));

        $samples = count($rows);
        $spanDays = $samples >= 2
            ? max(0, (int) ((new \DateTimeImmutable((string) end($rows)['snapshot_date']))->diff(new \DateTimeImmutable((string) $rows[0]['snapshot_date']))->days))
            : 0;
        $confidence = $this->confidence($samples, $spanDays);

        if ($samples < 2) {
            $insufficient = $this->indicator('INSUFFICIENT', 'Dados insuficientes', 'minus', null, 'São necessários pelo menos dois snapshots em dias diferentes para calcular esta tendência.');
            return new StudentTrend(
                $insufficient,
                $insufficient,
                $insufficient,
                $this->interventionIndicator($rows),
                'INSUFFICIENT',
                'O histórico ainda está sendo formado. Novas capturas diárias permitirão identificar a direção da evolução.',
                'LOW',
                $samples,
                $spanDays
            );
        }

        $frequency = $this->numericTrend($rows, 'attendance_percentage', 2.0, false, 'Frequência', 'ponto(s) percentual(is)');
        $occurrences = $this->numericTrend($rows, 'total_occurrences', 1.0, true, 'Ocorrências', 'ocorrência(s)');
        $risk = $this->numericTrend($rows, 'risk_score', 3.0, true, 'Risco', 'ponto(s)');
        $interventions = $this->interventionIndicator($rows);

        $worsening = count(array_filter([$frequency, $occurrences, $risk], static fn(array $item): bool => ($item['status'] ?? '') === 'WORSENING'));
        $improving = count(array_filter([$frequency, $occurrences, $risk], static fn(array $item): bool => ($item['status'] ?? '') === 'IMPROVING'));
        $overall = $worsening >= 2 ? 'WORSENING' : ($improving >= 2 ? 'IMPROVING' : 'STABLE');
        $summary = match ($overall) {
            'WORSENING' => 'Os registros históricos mostram agravamento em mais de um indicador. O caso merece revisão preventiva.',
            'IMPROVING' => 'Os registros históricos mostram melhora consistente em mais de um indicador. Recomenda-se confirmar a continuidade.',
            default => 'A evolução geral permanece estável, sem mudança consistente suficiente para indicar melhora ou agravamento.',
        };

        return new StudentTrend($frequency, $occurrences, $risk, $interventions, $overall, $summary, $confidence, $samples, $spanDays);
    }

    private function numericTrend(array $rows, string $column, float $threshold, bool $higherIsWorse, string $label, string $unit): array
    {
        $values = array_values(array_filter(array_map(
            static fn(array $row): ?float => isset($row[$column]) && $row[$column] !== null ? (float) $row[$column] : null,
            $rows
        ), static fn(?float $value): bool => $value !== null));

        if (count($values) < 2) {
            return $this->indicator('INSUFFICIENT', 'Dados insuficientes', 'minus', null, 'Não há valores suficientes de ' . mb_strtolower($label) . ' para comparar.');
        }

        $segment = max(1, (int) floor(count($values) / 3));
        $initial = array_slice($values, 0, $segment);
        $recent = array_slice($values, -$segment);
        $initialAverage = array_sum($initial) / count($initial);
        $recentAverage = array_sum($recent) / count($recent);
        $delta = round($recentAverage - $initialAverage, 1);

        if (abs($delta) < $threshold) {
            return $this->indicator('STABLE', 'Estável', 'arrow-right', $delta, $label . ' variou apenas ' . number_format(abs($delta), 1, ',', '.') . ' ' . $unit . ' no histórico disponível.');
        }

        $isWorsening = $higherIsWorse ? $delta > 0 : $delta < 0;
        $status = $isWorsening ? 'WORSENING' : 'IMPROVING';
        $title = $isWorsening ? 'Piorando' : 'Melhorando';
        $icon = $isWorsening ? 'trending-down' : 'trending-up';
        $direction = $delta > 0 ? 'aumentou' : 'caiu';

        return $this->indicator(
            $status,
            $title,
            $icon,
            $delta,
            $label . ' ' . $direction . ' ' . number_format(abs($delta), 1, ',', '.') . ' ' . $unit . ' entre o início e o trecho mais recente do histórico.'
        );
    }

    private function interventionIndicator(array $rows): array
    {
        $latest = $rows === [] ? [] : end($rows);
        if ($latest === []) {
            return $this->indicator('INSUFFICIENT', 'Dados insuficientes', 'minus', null, 'Ainda não existe snapshot para analisar as intervenções.');
        }
        if (empty($latest['has_active_monitoring'])) {
            return $this->indicator('NO_MONITORING', 'Sem acompanhamento', 'user-x', null, 'O snapshot mais recente não registra acompanhamento ativo.');
        }
        if ((int) ($latest['active_followers'] ?? 0) <= 0) {
            return $this->indicator('DELAYED', 'Sem acompanhante', 'user-minus', null, 'O acompanhamento está ativo, mas não possui acompanhante vinculado.');
        }
        $days = isset($latest['days_without_action']) && $latest['days_without_action'] !== null ? (int) $latest['days_without_action'] : null;
        if ($days === null) {
            return $this->indicator('DELAYED', 'Sem intervenção registrada', 'clock-alert', null, 'O acompanhamento está ativo, mas ainda não possui ação formal registrada.');
        }
        if ($days >= 14) {
            return $this->indicator('DELAYED', 'Intervenção atrasada', 'clock-alert', (float) $days, 'A última atualização de intervenção ocorreu há ' . $days . ' dias.');
        }
        return $this->indicator('ADEQUATE', 'Em dia', 'circle-check', (float) $days, 'A última atualização de intervenção ocorreu há ' . $days . ' dia(s).');
    }

    private function indicator(string $status, string $label, string $icon, ?float $delta, string $explanation): array
    {
        return compact('status', 'label', 'icon', 'delta', 'explanation');
    }

    private function confidence(int $samples, int $spanDays): string
    {
        if ($samples >= 14 && $spanDays >= 21) {
            return 'HIGH';
        }
        if ($samples >= 5 && $spanDays >= 7) {
            return 'MEDIUM';
        }
        return 'LOW';
    }
}
