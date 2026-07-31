<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\Repositories\Occurrence\OccurrenceRepository;

final class OccurrenceIndicatorService
{
    private const RECURRENCE_THRESHOLD = 3;

    public function __construct(
        private OccurrenceRepository $repository
    ) {
    }

    /**
     * Indicadores prontos para apresentação na dashboard.
     */
    public function dashboard(): array
    {
        $data = $this->repository->dashboardIndicators();

        $current = (int) ($data['current_month_total'] ?? 0);
        $previous = (int) ($data['previous_month_total'] ?? 0);
        $resolved = (int) ($data['current_month_resolved'] ?? 0);
        $criticalOpen = (int) ($data['critical_open'] ?? 0);
        $highCritical = (int) ($data['high_critical_current_month'] ?? 0);
        $recurrent = $this->repository
            ->countRecurrentStudentsLast30Days(
                self::RECURRENCE_THRESHOLD
            );

        $resolutionRate = $current > 0
            ? round(($resolved / $current) * 100, 1)
            : 0.0;

        $variation = $this->monthlyVariation(
            $current,
            $previous
        );

        return [
            [
                'key' => 'recurrent_students',
                'label' => 'Alunos recorrentes',
                'value' => $recurrent,
                'suffix' => '',
                'description' => sprintf(
                    '%d ou mais ocorrências nos últimos 30 dias',
                    self::RECURRENCE_THRESHOLD
                ),
                'icon' => 'user-round-search',
                'tone' => $recurrent > 0 ? 'warning' : 'positive',
            ],
            [
                'key' => 'critical_open',
                'label' => 'Críticas abertas',
                'value' => $criticalOpen,
                'suffix' => '',
                'description' => $criticalOpen > 0
                    ? 'Exigem acompanhamento prioritário'
                    : 'Nenhuma ocorrência gravíssima pendente',
                'icon' => 'triangle-alert',
                'tone' => $criticalOpen > 0 ? 'danger' : 'positive',
            ],
            [
                'key' => 'resolution_rate',
                'label' => 'Taxa de resolução',
                'value' => $this->formatNumber($resolutionRate),
                'suffix' => '%',
                'description' => sprintf(
                    '%d de %d ocorrências do mês resolvidas',
                    $resolved,
                    $current
                ),
                'icon' => 'badge-check',
                'tone' => $this->resolutionTone($resolutionRate, $current),
            ],
            [
                'key' => 'monthly_variation',
                'label' => 'Variação mensal',
                'value' => $this->formatNumber(abs($variation['percentage'])),
                'suffix' => '%',
                'description' => $variation['description'],
                'icon' => $variation['icon'],
                'tone' => $variation['tone'],
            ],
            [
                'key' => 'high_critical_month',
                'label' => 'Alta gravidade no mês',
                'value' => $highCritical,
                'suffix' => '',
                'description' => 'Ocorrências altas ou gravíssimas',
                'icon' => 'shield-alert',
                'tone' => $highCritical > 0 ? 'warning' : 'neutral',
            ],
        ];
    }

    private function monthlyVariation(
        int $current,
        int $previous
    ): array {
        if ($previous === 0) {
            if ($current === 0) {
                return [
                    'percentage' => 0.0,
                    'description' => 'Sem ocorrências nos dois últimos meses',
                    'icon' => 'minus',
                    'tone' => 'neutral',
                ];
            }

            return [
                'percentage' => 100.0,
                'description' => sprintf(
                    'Mês anterior sem registros; mês atual com %d',
                    $current
                ),
                'icon' => 'trending-up',
                'tone' => 'warning',
            ];
        }

        $percentage = round(
            (($current - $previous) / $previous) * 100,
            1
        );

        if ($percentage > 0) {
            return [
                'percentage' => $percentage,
                'description' => sprintf(
                    'Aumento em relação ao mês anterior (%d → %d)',
                    $previous,
                    $current
                ),
                'icon' => 'trending-up',
                'tone' => 'danger',
            ];
        }

        if ($percentage < 0) {
            return [
                'percentage' => $percentage,
                'description' => sprintf(
                    'Redução em relação ao mês anterior (%d → %d)',
                    $previous,
                    $current
                ),
                'icon' => 'trending-down',
                'tone' => 'positive',
            ];
        }

        return [
            'percentage' => 0.0,
            'description' => sprintf(
                'Mesmo total do mês anterior (%d)',
                $current
            ),
            'icon' => 'minus',
            'tone' => 'neutral',
        ];
    }

    private function resolutionTone(
        float $rate,
        int $total
    ): string {
        if ($total === 0) {
            return 'neutral';
        }

        if ($rate >= 75) {
            return 'positive';
        }

        if ($rate >= 50) {
            return 'warning';
        }

        return 'danger';
    }

    private function formatNumber(float $value): string
    {
        return number_format(
            $value,
            $value === floor($value) ? 0 : 1,
            ',',
            '.'
        );
    }
}
