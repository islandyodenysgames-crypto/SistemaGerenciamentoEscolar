<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

final class IntelligenceTimeline
{
    public function school(array $dashboard): array
    {
        return $this->fromInsights($dashboard['insights'] ?? [], 'inteligencia');
    }

    public function class(array $dashboard, int $classId): array
    {
        $events = [];
        $period = $dashboard['period'] ?? [];
        $distribution = $dashboard['distribution'] ?? [];
        $attendance = $dashboard['attendance'] ?? [];
        $occurrences = $dashboard['occurrences'] ?? [];

        $critical = (int) ($distribution['CRITICAL'] ?? 0);
        if ($critical > 0) {
            $events[] = $this->event('CRITICAL', 'siren', $critical . ' aluno(s) em risco crítico', 'A turma possui estudantes que exigem acompanhamento imediato.', 'alunos/turma?id=' . $classId . '#classPriorityStudents', $period);
        }

        $this->appendTrend($events, $attendance['trend'] ?? [], 'Faltas sem justificativa', 'calendar-x', 'inteligencia/comparacoes?turma=' . $classId, $period);
        $this->appendTrend($events, $occurrences['trend'] ?? [], 'Ocorrências', 'clipboard-alert', 'inteligencia/comparacoes?turma=' . $classId, $period);

        if ((int) ($occurrences['open'] ?? 0) > 0) {
            $events[] = $this->event('ATTENTION', 'clipboard-check', (int) $occurrences['open'] . ' ocorrência(s) ainda aberta(s)', 'Existem registros da turma aguardando resolução ou providência.', 'ocorrencias?turma=' . $classId, $period);
        }

        return $this->fallback($events, 'Turma sem mudanças prioritárias', 'Os indicadores permaneceram estáveis no período analisado.', 'alunos/turma?id=' . $classId . '#classIntelligence', $period);
    }

    public function student(array $dashboard, int $studentId): array
    {
        $events = [];
        $period = $dashboard['period'] ?? [];
        $risk = $dashboard['risk'] ?? [];
        $attendance = $dashboard['attendance'] ?? [];
        $occurrences = $dashboard['occurrences'] ?? [];
        $level = strtoupper((string) ($risk['level'] ?? 'LOW'));

        if ($level !== 'LOW') {
            $labels = ['MODERATE' => 'atenção', 'HIGH' => 'alto', 'CRITICAL' => 'crítico'];
            $events[] = $this->event(
                $level === 'CRITICAL' ? 'CRITICAL' : 'ATTENTION',
                $level === 'CRITICAL' ? 'siren' : 'user-round-search',
                'Aluno classificado em risco ' . ($labels[$level] ?? mb_strtolower($level)),
                'A classificação considera frequência, ocorrências, gravidade e registros em aberto.',
                'alunos/perfil?id=' . $studentId . '#studentIntelligence',
                $period
            );
        }

        $this->appendTrend($events, $attendance['trend'] ?? [], 'Faltas sem justificativa', 'calendar-x', 'alunos/perfil?id=' . $studentId . '#studentHistory', $period);
        $this->appendTrend($events, $occurrences['trend'] ?? [], 'Ocorrências', 'shield-alert', 'alunos/perfil?id=' . $studentId . '#studentOccurrences', $period);

        if ((int) ($occurrences['open'] ?? 0) > 0) {
            $events[] = $this->event('ATTENTION', 'clipboard-check', (int) $occurrences['open'] . ' ocorrência(s) aberta(s)', 'Há registros aguardando resolução ou encaminhamento.', 'alunos/perfil?id=' . $studentId . '#studentOccurrences', $period);
        }

        return $this->fallback($events, 'Aluno sem mudanças prioritárias', 'Os sinais permaneceram estáveis na janela de análise.', 'alunos/perfil?id=' . $studentId . '#studentIntelligence', $period);
    }

    private function fromInsights(array $insights, string $fallbackTarget): array
    {
        $events = [];
        foreach (array_slice($insights, 0, 6) as $insight) {
            $events[] = $this->event(
                (string) ($insight['level'] ?? 'INFORMATION'),
                (string) ($insight['icon'] ?? 'sparkles'),
                (string) ($insight['title'] ?? 'Insight'),
                (string) ($insight['description'] ?? ''),
                (string) ($insight['target'] ?? $fallbackTarget),
                (array) ($insight['period'] ?? [])
            );
        }
        return $events;
    }

    private function appendTrend(array &$events, array $trend, string $label, string $icon, string $target, array $period): void
    {
        $status = strtoupper((string) ($trend['status'] ?? 'STABLE'));
        $percentage = abs((float) ($trend['percentage'] ?? 0));
        if ($status === 'STABLE' || $percentage <= 0) {
            return;
        }

        $improving = $status === 'IMPROVING';
        $events[] = $this->event(
            $improving ? 'POSITIVE' : 'ATTENTION',
            $improving ? 'trending-down' : 'trending-up',
            $label . ($improving ? ' reduziram ' : ' aumentaram ') . $this->percentage($percentage),
            'Comparação realizada com a janela imediatamente anterior de mesma duração.',
            $target,
            $period
        );
    }

    private function fallback(array $events, string $title, string $description, string $target, array $period): array
    {
        if ($events !== []) {
            return array_slice($events, 0, 6);
        }
        return [$this->event('INFORMATION', 'circle-check-big', $title, $description, $target, $period)];
    }

    private function event(string $level, string $icon, string $title, string $description, string $target, array $period): array
    {
        return [
            'date' => date('Y-m-d'),
            'date_label' => 'Hoje',
            'level' => strtoupper($level),
            'icon' => $icon,
            'title' => $title,
            'description' => $description,
            'target' => $target,
            'period' => $period,
        ];
    }

    private function percentage(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, ',', '.'), '0'), ',') . '%';
    }
}
