<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

final class InsightFormatter
{
    public function make(
        string $key,
        int $priority,
        string $category,
        string $title,
        string $description,
        string $recommendation,
        string $icon,
        ?string $target,
        array $period,
        array $context = []
    ): array {
        $impact = $this->impact($priority, $context);

        return [
            'key' => $key,
            'priority' => $priority,
            'level' => InsightPriority::level($priority),
            'category' => strtoupper($category),
            'title' => $title,
            'description' => $description,
            'recommendation' => $recommendation,
            'icon' => $icon,
            'target' => $target,
            'period' => $period,
            'generated_at' => date('Y-m-d H:i:s'),
            'context' => $context,
            'impact' => $impact,
            'confidence' => $this->confidence($context),
            'explanation' => $this->explanation($priority, $category, $context, $period),
            'actions' => $this->actions($category, $target),
        ];
    }

    private function impact(int $priority, array $context): array
    {
        $count = max(0, (int) ($context['count'] ?? 0));
        $percentage = abs((float) ($context['percentage'] ?? 0));
        $score = match (true) {
            $priority >= InsightPriority::CRITICAL => 82,
            $priority >= InsightPriority::ATTENTION => 58,
            $priority >= InsightPriority::POSITIVE => 42,
            default => 25,
        };

        $score += min(12, $count * 2);
        $score += min(10, (int) round($percentage / 5));
        $score = max(1, min(100, $score));
        $stars = max(1, min(5, (int) ceil($score / 20)));

        return [
            'score' => $score,
            'stars' => $stars,
            'label' => match (true) {
                $score >= 81 => 'Muito alto',
                $score >= 61 => 'Alto',
                $score >= 41 => 'Médio',
                $score >= 21 => 'Baixo',
                default => 'Muito baixo',
            },
        ];
    }

    private function confidence(array $context): int
    {
        $signals = 1;
        if (array_key_exists('count', $context)) {
            $signals++;
        }
        if (array_key_exists('percentage', $context)) {
            $signals++;
        }

        return min(96, 64 + ($signals * 10));
    }

    private function explanation(int $priority, string $category, array $context, array $period): array
    {
        $items = [];
        $categoryLabel = match (strtoupper($category)) {
            'STUDENT' => 'indicadores individuais dos alunos',
            'CLASS' => 'concentração dos indicadores por turma',
            'ATTENDANCE' => 'registros de frequência do período',
            'OCCURRENCE' => 'volume, gravidade e situação das ocorrências',
            default => 'indicadores gerais da escola',
        };
        $items[] = 'Análise baseada em ' . $categoryLabel . '.';

        if (isset($context['count'])) {
            $items[] = 'Foram identificados ' . (int) $context['count'] . ' registro(s) ou caso(s) relacionado(s).';
        }
        if (isset($context['percentage'])) {
            $items[] = 'A variação calculada foi de ' . rtrim(rtrim(number_format(abs((float) $context['percentage']), 1, ',', '.'), '0'), ',') . '% em relação ao período anterior.';
        }

        $days = (int) ($period['days'] ?? $period['window_days'] ?? 0);
        if ($days > 0) {
            $items[] = 'Janela analisada: ' . $days . ' dia(s), comparada com intervalo anterior de mesma duração quando aplicável.';
        }

        $items[] = 'Prioridade técnica atribuída: ' . $priority . ' ponto(s).';
        return $items;
    }

    private function actions(string $category, ?string $target): array
    {
        $actions = [];
        if ($target !== null && trim($target) !== '') {
            $actions[] = [
                'label' => match (strtoupper($category)) {
                    'STUDENT' => 'Ver alunos',
                    'CLASS' => 'Ver turmas',
                    'OCCURRENCE' => 'Ver ocorrências',
                    'ATTENDANCE' => 'Analisar frequência',
                    default => 'Ver detalhes',
                },
                'icon' => match (strtoupper($category)) {
                    'STUDENT' => 'users',
                    'CLASS' => 'school',
                    'OCCURRENCE' => 'clipboard-list',
                    'ATTENDANCE' => 'calendar-check',
                    default => 'arrow-up-right',
                },
                'target' => $target,
            ];
        }

        if (in_array(strtoupper($category), ['CLASS', 'ATTENDANCE', 'OCCURRENCE'], true)
            && $target !== 'inteligencia/comparacoes') {
            $actions[] = [
                'label' => 'Comparar',
                'icon' => 'scale',
                'target' => 'inteligencia/comparacoes',
            ];
        }

        return $actions;
    }
}
