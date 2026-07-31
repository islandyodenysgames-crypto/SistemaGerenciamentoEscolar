<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

final class InsightGenerator
{
    public function __construct(private InsightFormatter $formatter)
    {
    }

    public function school(array $data): array
    {
        $summary = $data['summary'] ?? [];
        $attendance = $data['attendance'] ?? [];
        $occurrences = $data['occurrences'] ?? [];
        $classes = $data['classes'] ?? [];
        $period = $data['period'] ?? [];
        $insights = [];

        $criticalStudents = (int) ($summary['critical_students'] ?? 0);
        if ($criticalStudents > 0) {
            $insights[] = $this->formatter->make(
                'school.critical_students', InsightPriority::CRITICAL, 'STUDENT',
                $criticalStudents . ' aluno(s) em risco crítico',
                'Há estudantes cuja combinação de faltas, reincidência e gravidade atingiu o nível mais alto de risco.',
                'Priorize a análise individual, revise os registros recentes e defina responsáveis pelo acompanhamento.',
                'siren', 'inteligencia/casos?tipo=critical_students', $period,
                ['count' => $criticalStudents]
            );
        }

        $stale = (int) ($summary['stale_critical_occurrences'] ?? 0);
        if ($stale > 0) {
            $insights[] = $this->formatter->make(
                'school.stale_critical_occurrences', InsightPriority::CRITICAL, 'OCCURRENCE',
                $stale . ' ocorrência(s) crítica(s) atrasada(s)',
                'Existem ocorrências críticas abertas além do prazo definido nas configurações de inteligência.',
                'Revise as providências pendentes e registre a resolução ou o próximo encaminhamento.',
                'clock-alert', 'inteligencia/casos?tipo=stale_critical_occurrences', $period,
                ['count' => $stale]
            );
        }

        $attentionStudents = (int) ($summary['students_in_attention'] ?? 0);
        if ($attentionStudents > 0) {
            $insights[] = $this->formatter->make(
                'school.students_attention', InsightPriority::ATTENTION, 'STUDENT',
                $attentionStudents . ' aluno(s) precisam de acompanhamento',
                'O motor identificou alunos em nível moderado, alto ou crítico no período analisado.',
                'Distribua os casos entre a equipe e registre as ações adotadas para cada estudante.',
                'user-round-search', 'inteligencia/casos?tipo=students_attention', $period,
                ['count' => $attentionStudents]
            );
        }

        $attendanceTrend = $attendance['trend'] ?? [];
        $attendanceStatus = (string) ($attendanceTrend['status'] ?? 'stable');
        $attendancePercentage = abs((float) ($attendanceTrend['percentage'] ?? 0));
        if ($attendanceStatus === 'worsening' && $attendancePercentage > 0) {
            $insights[] = $this->formatter->make(
                'school.attendance_worsening', InsightPriority::ATTENTION, 'ATTENDANCE',
                'Faltas sem justificativa aumentaram ' . $this->percentage($attendancePercentage),
                'O período atual apresenta piora em comparação com o período anterior de mesma duração.',
                'Verifique as turmas mais afetadas e procure padrões por aluno e por período.',
                'trending-up', 'inteligencia/casos?tipo=students_attention', $period,
                ['percentage' => $attendancePercentage]
            );
        } elseif ($attendanceStatus === 'improving' && $attendancePercentage > 0) {
            $insights[] = $this->formatter->make(
                'school.attendance_improving', InsightPriority::POSITIVE, 'ATTENDANCE',
                'Faltas sem justificativa reduziram ' . $this->percentage($attendancePercentage),
                'A frequência apresentou evolução positiva em relação ao período anterior.',
                'Identifique as ações que contribuíram para a melhora e mantenha o acompanhamento.',
                'trending-down', 'inteligencia/comparacoes', $period,
                ['percentage' => $attendancePercentage]
            );
        }

        $occurrenceTrend = $occurrences['trend'] ?? [];
        $occurrenceStatus = (string) ($occurrenceTrend['status'] ?? 'stable');
        $occurrencePercentage = abs((float) ($occurrenceTrend['percentage'] ?? 0));
        if ($occurrenceStatus === 'worsening' && $occurrencePercentage > 0) {
            $insights[] = $this->formatter->make(
                'school.occurrences_worsening', InsightPriority::ATTENTION, 'OCCURRENCE',
                'Ocorrências aumentaram ' . $this->percentage($occurrencePercentage),
                'O volume de ocorrências cresceu em relação ao período anterior de mesma duração.',
                'Compare as turmas e concentre a intervenção onde houver maior gravidade ou reincidência.',
                'triangle-alert', 'inteligencia/comparacoes', $period,
                ['percentage' => $occurrencePercentage]
            );
        } elseif ($occurrenceStatus === 'improving' && $occurrencePercentage > 0) {
            $insights[] = $this->formatter->make(
                'school.occurrences_improving', InsightPriority::POSITIVE, 'OCCURRENCE',
                'Ocorrências reduziram ' . $this->percentage($occurrencePercentage),
                'O período atual registrou menos ocorrências que o período anterior.',
                'Mantenha as práticas preventivas e acompanhe se a melhora se sustenta nas próximas análises.',
                'badge-check', 'inteligencia/comparacoes', $period,
                ['percentage' => $occurrencePercentage]
            );
        }

        $attentionClasses = (int) ($summary['classes_in_attention'] ?? 0);
        if ($attentionClasses > 0) {
            $topClass = $classes[0] ?? null;
            $description = $topClass
                ? 'A turma ' . (string) ($topClass['name'] ?? '') . ' aparece entre as maiores prioridades do período.'
                : 'Existem turmas com concentração relevante de faltas e ocorrências.';
            $insights[] = $this->formatter->make(
                'school.attention_classes', InsightPriority::ATTENTION, 'CLASS',
                $attentionClasses . ' turma(s) em atenção', $description,
                'Use a comparação entre turmas para definir a ordem de acompanhamento da equipe.',
                'school', 'inteligencia/casos?tipo=attention_classes', $period,
                ['count' => $attentionClasses]
            );
        }

        if ($insights === []) {
            $insights[] = $this->formatter->make(
                'school.stable', InsightPriority::INFORMATION, 'SCHOOL',
                'Nenhum sinal prioritário identificado',
                'Os indicadores analisados permanecem estáveis e não há casos críticos no período.',
                'Mantenha o monitoramento regular para identificar mudanças precocemente.',
                'circle-check-big', null, $period
            );
        }

        return $insights;
    }

    private function percentage(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, ',', '.'), '0'), ',') . '%';
    }
}
