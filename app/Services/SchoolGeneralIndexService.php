<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolGeneralIndexRepository;
use PDOException;

final class SchoolGeneralIndexService
{
    public function __construct(private SchoolGeneralIndexRepository $repository) {}

    public function build(array $context): array
    {
        $frequency = max(0.0, min(100.0, (float)($context['frequency'] ?? 0)));
        $goal = max(1.0, min(100.0, (float)($context['frequency_goal'] ?? 95)));
        $activeStudents = max(1, (int)($context['active_students'] ?? 0));
        $totalClasses = max(1, (int)($context['total_classes'] ?? 0));
        $priorityStudents = max(0, (int)($context['priority_students'] ?? 0));
        $criticalStudents = max(0, (int)($context['critical_students'] ?? 0));
        $attentionClasses = max(0, (int)($context['attention_classes'] ?? 0));
        $occurrences = max(0, (int)($context['occurrences'] ?? 0));
        $serious = max(0, (int)($context['serious_occurrences'] ?? 0));
        $open = max(0, (int)($context['open_occurrences'] ?? 0));
        $coverage = max(0.0, min(100.0, (float)($context['monitoring_coverage'] ?? 0)));

        $frequencyScore = round(min(1, $frequency / $goal) * 40, 1);
        $occurrencePressure = min(1, (($occurrences / $activeStudents) * .45) + (($serious / $activeStudents) * 1.8) + (($open / $activeStudents) * .35));
        $occurrenceScore = round(25 * (1 - $occurrencePressure), 1);
        $riskPressure = min(1, (($priorityStudents / $activeStudents) * .7) + (($criticalStudents / $activeStudents) * 2.2));
        $riskScore = round(20 * (1 - $riskPressure), 1);
        $monitoringScore = round(($coverage / 100) * 10, 1);
        $classesScore = round(5 * (1 - min(1, $attentionClasses / $totalClasses)), 1);
        $score = (int)round($frequencyScore + $occurrenceScore + $riskScore + $monitoringScore + $classesScore);
        $score = max(0, min(100, $score));

        $details = [
            ['key'=>'frequency','label'=>'Frequência','score'=>$frequencyScore,'max'=>40,'description'=>number_format($frequency,1,',','.') . '% frente à meta de ' . number_format($goal,1,',','.') . '%.'],
            ['key'=>'occurrences','label'=>'Ocorrências','score'=>$occurrenceScore,'max'=>25,'description'=>$occurrences . ' registro(s), ' . $serious . ' grave(s) e ' . $open . ' aberto(s).'],
            ['key'=>'risk','label'=>'Risco dos alunos','score'=>$riskScore,'max'=>20,'description'=>$priorityStudents . ' aluno(s) em atenção, sendo ' . $criticalStudents . ' crítico(s).'],
            ['key'=>'monitoring','label'=>'Acompanhamentos','score'=>$monitoringScore,'max'=>10,'description'=>number_format($coverage,0,',','.') . '% dos casos prioritários estão cobertos.'],
            ['key'=>'classes','label'=>'Turmas','score'=>$classesScore,'max'=>5,'description'=>$attentionClasses . ' de ' . $totalClasses . ' turma(s) em atenção.'],
        ];

        try {
            $this->repository->save([
                'snapshot_date'=>date('Y-m-d'), 'score'=>$score,
                'frequency_score'=>$frequencyScore, 'occurrence_score'=>$occurrenceScore,
                'risk_score'=>$riskScore, 'monitoring_score'=>$monitoringScore,
                'classes_score'=>$classesScore, 'details_json'=>json_encode($details, JSON_UNESCAPED_UNICODE),
            ]);
        } catch (PDOException) {
            // O índice continua disponível antes da execução da nova migração.
        }

        $history = $this->repository->recent(365);
        $comparisons = $this->comparisons($history, $score);
        $trend = $this->trend($history, $score);
        $label = $score >= 95 ? 'Excelente' : ($score >= 90 ? 'Muito bom' : ($score >= 80 ? 'Bom' : ($score >= 70 ? 'Atenção' : 'Crítico')));
        $tone = $score >= 90 ? 'green' : ($score >= 80 ? 'blue' : ($score >= 70 ? 'yellow' : 'red'));

        $best = $details;
        usort($best, static fn(array $a,array $b): int => (($b['score']/$b['max']) <=> ($a['score']/$a['max'])));
        $weak = $details;
        usort($weak, static fn(array $a,array $b): int => (($a['score']/$a['max']) <=> ($b['score']/$b['max'])));

        return compact('score','label','tone','details','comparisons','trend') + [
            'main_positive' => $best[0]['label'] ?? 'Frequência',
            'main_attention' => $weak[0]['label'] ?? 'Ocorrências',
        ];
    }

    private function comparisons(array $history, int $current): array
    {
        $find = static function(array $rows, int $days): ?int {
            $target = strtotime('-'.$days.' days'); $best = null; $distance = PHP_INT_MAX;
            foreach ($rows as $row) { $time = strtotime((string)($row['snapshot_date'] ?? '')); if (!$time) continue; $d=abs($time-$target); if($d<$distance){$distance=$d;$best=(int)$row['score'];} }
            return $best;
        };
        $result=[];
        foreach ([7=>'Há 7 dias',30=>'Há 30 dias',90=>'Há 90 dias'] as $days=>$label) {
            $value=$find($history,$days); $result[]=['label'=>$label,'score'=>$value,'difference'=>$value===null?null:$current-$value];
        }
        return $result;
    }

    private function trend(array $history, int $current): array
    {
        $rows = array_slice($history, -12);
        $points=[];
        foreach($rows as $row){$points[]=['date'=>date('d/m',strtotime((string)$row['snapshot_date'])),'score'=>(int)$row['score']];}
        if ($points === [] || end($points)['date'] !== date('d/m')) $points[]=['date'=>date('d/m'),'score'=>$current];
        $first=(int)($points[0]['score']??$current); $difference=$current-$first;
        return ['status'=>$difference>1?'Crescimento':($difference<-1?'Redução':'Estável'),'difference'=>$difference,'points'=>$points];
    }
}
