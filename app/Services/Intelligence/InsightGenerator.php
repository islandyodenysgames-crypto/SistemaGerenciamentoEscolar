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
        $positive = (array) ($data['positive_indicators'] ?? []);
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
        $attendanceStatus = strtoupper((string) ($attendanceTrend['status'] ?? 'STABLE'));
        $attendancePercentage = abs((float) ($attendanceTrend['percentage'] ?? 0));
        $attendanceHasBaseline = (bool) ($attendanceTrend['baseline_available'] ?? ((int) ($attendanceTrend['previous'] ?? 0) > 0));
        if (in_array($attendanceStatus, ['WORSENING', 'NEW_SIGNAL'], true) && ($attendancePercentage > 0 || !$attendanceHasBaseline)) {
            $insights[] = $this->formatter->make(
                'school.attendance_worsening', InsightPriority::ATTENTION, 'ATTENDANCE',
                $attendanceHasBaseline
                    ? 'Faltas sem justificativa aumentaram ' . $this->percentage($attendancePercentage)
                    : (int) ($attendanceTrend['current'] ?? 0) . ' falta(s) sem justificativa no período',
                $attendanceHasBaseline
                    ? 'O período atual apresenta piora em comparação com o período anterior de mesma duração.'
                    : 'Não havia faltas sem justificativa no período anterior; por isso não existe uma variação percentual válida.',
                'Verifique as turmas mais afetadas e procure padrões por aluno e por período.',
                'trending-up', 'inteligencia/casos?tipo=students_attention', $period,
                ['percentage' => $attendancePercentage, 'baseline_available' => $attendanceHasBaseline, 'current' => (int) ($attendanceTrend['current'] ?? 0)]
            );
        } elseif ($attendanceStatus === 'IMPROVING' && $attendancePercentage > 0) {
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
        $occurrenceStatus = strtoupper((string) ($occurrenceTrend['status'] ?? 'STABLE'));
        $occurrencePercentage = abs((float) ($occurrenceTrend['percentage'] ?? 0));
        $occurrenceHasBaseline = (bool) ($occurrenceTrend['baseline_available'] ?? ((int) ($occurrenceTrend['previous'] ?? 0) > 0));
        if (in_array($occurrenceStatus, ['WORSENING', 'NEW_SIGNAL'], true) && ($occurrencePercentage > 0 || !$occurrenceHasBaseline)) {
            $insights[] = $this->formatter->make(
                'school.occurrences_worsening', InsightPriority::ATTENTION, 'OCCURRENCE',
                $occurrenceHasBaseline
                    ? 'Ocorrências aumentaram ' . $this->percentage($occurrencePercentage)
                    : (int) ($occurrenceTrend['current'] ?? 0) . ' ocorrência(s) no período',
                $occurrenceHasBaseline
                    ? 'O volume de ocorrências cresceu em relação ao período anterior de mesma duração.'
                    : 'Não havia ocorrências no período anterior; por isso não existe uma variação percentual válida.',
                'Compare as turmas e concentre a intervenção onde houver maior gravidade ou reincidência.',
                'triangle-alert', 'inteligencia/comparacoes', $period,
                ['percentage' => $occurrencePercentage, 'baseline_available' => $occurrenceHasBaseline, 'current' => (int) ($occurrenceTrend['current'] ?? 0)]
            );
        } elseif ($occurrenceStatus === 'IMPROVING' && $occurrencePercentage > 0) {
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
            $topClass = null;
            foreach ($classes as $class) {
                if (
                    $topClass === null
                    || (int) ($class['risk_score'] ?? 0) > (int) ($topClass['risk_score'] ?? 0)
                ) {
                    $topClass = $class;
                }
            }
            $topScore = (int) ($topClass['risk_score'] ?? 0);
            $topRiskLevel = strtoupper((string) ($topClass['risk_level'] ?? 'MODERATE'));
            $topRiskLabel = match ($topRiskLevel) {
                'CRITICAL' => 'crítico',
                'HIGH' => 'alto',
                'MODERATE' => 'atenção',
                default => 'baixo',
            };
            $insightPriority = $topRiskLevel === 'CRITICAL'
                ? InsightPriority::CRITICAL
                : InsightPriority::ATTENTION;
            $description = $topClass
                ? 'A turma ' . (string) ($topClass['name'] ?? '')
                    . ' possui a maior pontuação do período: ' . $topScore
                    . ' pontos, em nível ' . $topRiskLabel . '.'
                : 'Existem turmas com concentração relevante de faltas e ocorrências.';
            $insights[] = $this->formatter->make(
                'school.attention_classes', $insightPriority, 'CLASS',
                $attentionClasses . ' turma(s) em atenção', $description,
                'Use a comparação entre turmas para definir a ordem de acompanhamento da equipe.',
                'school', 'inteligencia/casos?tipo=attention_classes', $period,
                [
                    'count' => $attentionClasses,
                    'top_class' => (string) ($topClass['name'] ?? ''),
                    'top_score' => $topScore,
                    'top_risk_level' => $topRiskLevel,
                ]
            );
        }

        $responses = (int) ($positive['responses'] ?? 0);
        $recognitions = (int) ($positive['recognitions'] ?? 0);
        if ($responses > 0 || $recognitions > 0) {
            $insights[] = $this->formatter->make(
                'school.positive_actions', InsightPriority::POSITIVE, 'STUDENT',
                ($responses + $recognitions) . ' registro(s) de atuação positiva',
                $responses . ' resposta(s) pedagógica(s) e ' . $recognitions . ' reconhecimento(s) foram registrados no período.',
                'Consulte os alunos envolvidos e acompanhe se a evolução se mantém nos próximos registros.',
                'sparkles', 'inteligencia/casos?tipo=pedagogical_responses', $period,
                ['responses' => $responses, 'recognitions' => $recognitions]
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
