<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Core\Settings\SettingManager;
use App\Repositories\Intelligence\IntelligenceDailySnapshotRepository;
use App\ValueObjects\Intelligence\StudentPrediction;

final class PredictiveAnalysisService
{
    public function __construct(
        private readonly IntelligenceDailySnapshotRepository $snapshots,
        private readonly TrendAnalysisService $trends,
        private readonly SettingManager $settings
    ) {}

    public function student(int $studentId, int $days = 30, int $horizonDays = 14): array
    {
        $rows = $this->snapshots->recentStudentSnapshots($studentId, max(2, min(90, $days + 1)));
        $trend = $this->trends->fromSnapshots($rows)->toArray();
        return $this->fromSnapshots($rows, $trend, $horizonDays)->toArray();
    }

    public function students(array $studentIds, int $days = 30, int $horizonDays = 14): array
    {
        $result = [];
        foreach (array_unique(array_map('intval', $studentIds)) as $studentId) {
            if ($studentId > 0) {
                $result[$studentId] = $this->student($studentId, $days, $horizonDays);
            }
        }
        return $result;
    }

    public function fromSnapshots(array $rows, array $trend, int $horizonDays = 14): StudentPrediction
    {
        $horizonDays = max(7, min(30, $horizonDays));
        $rows = array_values(array_filter($rows, static fn(array $row): bool => !empty($row['snapshot_date'])));
        usort($rows, static fn(array $a, array $b): int => strcmp((string) $a['snapshot_date'], (string) $b['snapshot_date']));
        $latest = $rows === [] ? [] : end($rows);
        $samples = (int) ($trend['samples'] ?? count($rows));
        $spanDays = (int) ($trend['span_days'] ?? 0);

        $currentScore = (int) ($latest['risk_score'] ?? 0);
        $currentLevel = strtoupper((string) ($latest['risk_level'] ?? $this->levelForScore($currentScore)));

        if ($samples < 2 || $spanDays < 1) {
            return new StudentPrediction(
                'INSUFFICIENT',
                'Histórico insuficiente',
                'Ainda não há dados suficientes para projetar o cenário.',
                'A previsão será habilitada após existirem snapshots em pelo menos dois dias diferentes.',
                $currentLevel,
                $currentScore,
                $currentLevel,
                $currentScore,
                $horizonDays,
                'LOW',
                [],
                ['Acompanhar diariamente até que o sistema reúna histórico suficiente para uma projeção confiável.']
            );
        }

        $adjustment = 0;
        $evidence = [];
        $actions = [];

        $adjustment += $this->applyTrend($trend['frequency'] ?? [], 12, $evidence, 'A frequência apresenta piora histórica.', 'A frequência apresenta melhora histórica.');
        $adjustment += $this->applyTrend($trend['occurrences'] ?? [], 11, $evidence, 'As ocorrências estão aumentando.', 'As ocorrências estão reduzindo.');
        $adjustment += $this->applyTrend($trend['risk'] ?? [], 16, $evidence, 'A pontuação de risco está crescendo.', 'A pontuação de risco está diminuindo.');

        $interventionStatus = strtoupper((string) (($trend['interventions']['status'] ?? 'INSUFFICIENT')));
        if ($interventionStatus === 'DELAYED') {
            $adjustment += 10;
            $evidence[] = (string) (($trend['interventions']['explanation'] ?? 'A intervenção está atrasada.'));
            $actions[] = 'Revisar o acompanhamento e registrar uma intervenção atualizada.';
        } elseif ($interventionStatus === 'NO_MONITORING') {
            $adjustment += 12;
            $evidence[] = 'O aluno não possui acompanhamento ativo no snapshot mais recente.';
            $actions[] = 'Avaliar a abertura de acompanhamento preventivo e designar um responsável.';
        } elseif ($interventionStatus === 'ADEQUATE') {
            $adjustment -= 4;
            $evidence[] = 'O acompanhamento possui intervenção atualizada.';
        }

        $pending = (int) ($latest['pending_recommendations'] ?? 0);
        if ($pending > 0) {
            $adjustment += min(8, $pending * 2);
            $evidence[] = $pending . ' recomendação(ões) permanece(m) pendente(s).';
            $actions[] = 'Revisar e encaminhar as recomendações ainda pendentes.';
        }

        if ((int) ($latest['serious_occurrences'] ?? 0) > 0) {
            $adjustment += 5;
            $evidence[] = 'Há ocorrência(s) de alta gravidade no período analisado.';
            $actions[] = 'Confirmar o tratamento das ocorrências de maior gravidade.';
        }

        $projectedScore = max(0, min(100, $currentScore + $adjustment));
        $projectedLevel = $this->levelForScore($projectedScore);
        $status = $this->predictionStatus($currentScore, $projectedScore, $currentLevel, $projectedLevel);
        $confidence = $this->predictionConfidence((string) ($trend['confidence'] ?? 'LOW'), $samples, $spanDays);

        [$label, $headline, $summary] = $this->texts($status, $currentLevel, $projectedLevel, $horizonDays);

        if ($status === 'DETERIORATION' || $status === 'CRITICAL_ESCALATION') {
            $actions[] = 'Realizar revisão preventiva do caso antes do próximo ciclo de registros.';
        } elseif ($status === 'IMPROVEMENT') {
            $actions[] = 'Manter as estratégias atuais e confirmar se a melhora continua nos próximos snapshots.';
        } else {
            $actions[] = 'Manter o monitoramento e observar qualquer mudança consistente nos indicadores.';
        }

        return new StudentPrediction(
            $status,
            $label,
            $headline,
            $summary,
            $currentLevel,
            $currentScore,
            $projectedLevel,
            $projectedScore,
            $horizonDays,
            $confidence,
            array_values(array_unique($evidence)),
            array_slice(array_values(array_unique($actions)), 0, 4)
        );
    }

    private function applyTrend(array $indicator, int $weight, array &$evidence, string $worseningText, string $improvingText): int
    {
        $status = strtoupper((string) ($indicator['status'] ?? 'INSUFFICIENT'));
        if ($status === 'WORSENING') {
            $evidence[] = (string) ($indicator['explanation'] ?? $worseningText);
            return $weight;
        }
        if ($status === 'IMPROVING') {
            $evidence[] = (string) ($indicator['explanation'] ?? $improvingText);
            return -max(3, (int) round($weight * 0.7));
        }
        return 0;
    }

    private function predictionStatus(int $currentScore, int $projectedScore, string $currentLevel, string $projectedLevel): string
    {
        if ($projectedLevel === 'CRITICAL' && $currentLevel !== 'CRITICAL') {
            return 'CRITICAL_ESCALATION';
        }
        $delta = $projectedScore - $currentScore;
        if ($delta >= 8 || $this->rank($projectedLevel) > $this->rank($currentLevel)) {
            return 'DETERIORATION';
        }
        if ($delta <= -8 || $this->rank($projectedLevel) < $this->rank($currentLevel)) {
            return 'IMPROVEMENT';
        }
        return 'STABLE';
    }

    private function texts(string $status, string $currentLevel, string $projectedLevel, int $horizonDays): array
    {
        return match ($status) {
            'CRITICAL_ESCALATION' => [
                'Risco de agravamento crítico',
                'O aluno pode atingir nível crítico nas próximas semanas.',
                'Mantidas as tendências atuais, o cenário projetado para os próximos ' . $horizonDays . ' dias passa de ' . $this->levelLabel($currentLevel) . ' para ' . $this->levelLabel($projectedLevel) . '.',
            ],
            'DETERIORATION' => [
                'Tendência de agravamento',
                'O cenário mais provável é de piora.',
                'Os sinais históricos indicam aumento do risco nas próximas ' . $horizonDays . ' dias caso não exista mudança relevante.',
            ],
            'IMPROVEMENT' => [
                'Tendência de melhora',
                'O cenário mais provável é de evolução positiva.',
                'Os registros apontam redução do risco nas próximas ' . $horizonDays . ' dias, desde que as estratégias atuais sejam mantidas.',
            ],
            default => [
                'Tendência de estabilidade',
                'O cenário mais provável é de manutenção.',
                'Não há mudança consistente suficiente para projetar alteração importante do nível de risco nos próximos ' . $horizonDays . ' dias.',
            ],
        };
    }

    private function predictionConfidence(string $trendConfidence, int $samples, int $spanDays): string
    {
        if ($trendConfidence === 'HIGH' && $samples >= 14 && $spanDays >= 21) {
            return 'HIGH';
        }
        if (in_array($trendConfidence, ['MEDIUM', 'HIGH'], true) && $samples >= 5 && $spanDays >= 7) {
            return 'MEDIUM';
        }
        return 'LOW';
    }

    private function levelForScore(int $score): string
    {
        $moderate = (int) $this->settings->get('intelligence.risk_moderate_threshold', 20);
        $high = (int) $this->settings->get('intelligence.risk_high_threshold', 45);
        $critical = (int) $this->settings->get('intelligence.risk_critical_threshold', 70);
        return match (true) {
            $score >= $critical => 'CRITICAL',
            $score >= $high => 'HIGH',
            $score >= $moderate => 'MODERATE',
            default => 'LOW',
        };
    }

    private function rank(string $level): int
    {
        return ['LOW' => 0, 'MODERATE' => 1, 'HIGH' => 2, 'CRITICAL' => 3][$level] ?? 0;
    }

    private function levelLabel(string $level): string
    {
        return ['LOW' => 'baixo', 'MODERATE' => 'atenção', 'HIGH' => 'alto', 'CRITICAL' => 'crítico'][$level] ?? mb_strtolower($level);
    }
}
