<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Repositories\Intelligence\IntelligenceDailySnapshotRepository;

final class ClassTrendAnalysisService
{
    public function __construct(private readonly IntelligenceDailySnapshotRepository $snapshots) {}

    public function schoolClass(int $classId, int $days = 30): array
    {
        return $this->fromSnapshots($this->snapshots->recentClassSnapshots($classId, $days));
    }

    public function classes(array $classIds, int $days = 30): array
    {
        $result = [];
        foreach ($this->snapshots->recentClassesSnapshots($classIds, $days) as $classId => $rows) {
            $result[(int) $classId] = $this->fromSnapshots($rows);
        }
        return $result;
    }

    public function fromSnapshots(array $rows): array
    {
        $rows = array_values(array_filter($rows, static fn(array $row): bool => !empty($row['snapshot_date'])));
        $samples = count($rows);
        $spanDays = $samples >= 2 ? (int) (new \DateTimeImmutable((string) end($rows)['snapshot_date']))->diff(new \DateTimeImmutable((string) $rows[0]['snapshot_date']))->days : 0;
        $confidence = $samples >= 14 && $spanDays >= 21 ? 'HIGH' : (($samples >= 5 && $spanDays >= 7) ? 'MEDIUM' : 'LOW');
        if ($samples < 2) {
            $insufficient = $this->indicator('INSUFFICIENT', 'Dados insuficientes', 'minus', null, 'São necessários snapshots de pelo menos dois dias diferentes.');
            return ['frequency'=>$insufficient,'occurrences'=>$insufficient,'risk'=>$insufficient,'coverage'=>$insufficient,'recommendations'=>$insufficient,'overall_status'=>'INSUFFICIENT','summary'=>'O histórico da turma ainda está sendo formado.','confidence'=>$confidence,'samples'=>$samples,'span_days'=>$spanDays,'evolution_score'=>0.0];
        }

        $frequency = $this->trend($rows, 'attendance_percentage', 2.0, false, 'Frequência', 'ponto(s) percentual(is)');
        $occurrences = $this->trend($rows, 'total_occurrences', 1.0, true, 'Ocorrências', 'ocorrência(s)');
        $risk = $this->trend($rows, 'average_risk_score', 3.0, true, 'Risco médio', 'ponto(s)');
        $coverageRows = array_map(static function(array $row): array {
            $total=max(1,(int)($row['priority_students']??0));
            $row['coverage_percentage']=round(((int)($row['covered_students']??0)/$total)*100,2);
            return $row;
        }, $rows);
        $coverage = $this->trend($coverageRows, 'coverage_percentage', 5.0, false, 'Cobertura dos acompanhamentos', 'ponto(s) percentual(is)');
        $recommendations = $this->trend($rows, 'pending_recommendations', 1.0, true, 'Recomendações pendentes', 'recomendação(ões)');

        $items=[$frequency,$occurrences,$risk,$coverage,$recommendations];
        $w=count(array_filter($items,static fn(array $i):bool=>($i['status']??'')==='WORSENING'));
        $i=count(array_filter($items,static fn(array $i):bool=>($i['status']??'')==='IMPROVING'));
        $overall=$w>=2?'WORSENING':($i>=2?'IMPROVING':'STABLE');
        $summary=match($overall){'WORSENING'=>'A turma apresenta agravamento consistente em mais de um indicador histórico.','IMPROVING'=>'A turma apresenta melhora consistente em mais de um indicador histórico.',default=>'A turma permanece estável, sem mudança coletiva consistente.'};
        $score=round(($i*2)-($w*2)+$this->directionScore($frequency)+$this->directionScore($risk),1);
        return ['frequency'=>$frequency,'occurrences'=>$occurrences,'risk'=>$risk,'coverage'=>$coverage,'recommendations'=>$recommendations,'overall_status'=>$overall,'summary'=>$summary,'confidence'=>$confidence,'samples'=>$samples,'span_days'=>$spanDays,'evolution_score'=>$score];
    }

    private function trend(array $rows,string $column,float $threshold,bool $higherIsWorse,string $label,string $unit):array
    {
        $values=array_values(array_filter(array_map(static fn(array $r):?float=>isset($r[$column])&&$r[$column]!==null?(float)$r[$column]:null,$rows),static fn(?float $v):bool=>$v!==null));
        if(count($values)<2)return $this->indicator('INSUFFICIENT','Dados insuficientes','minus',null,'Não há valores suficientes para comparar.');
        $segment=max(1,(int)floor(count($values)/3));
        $initial=array_sum(array_slice($values,0,$segment))/$segment;
        $recent=array_sum(array_slice($values,-$segment))/$segment;
        $delta=round($recent-$initial,1);
        if(abs($delta)<$threshold)return $this->indicator('STABLE','Estável','arrow-right',$delta,$label.' variou apenas '.number_format(abs($delta),1,',','.').' '.$unit.'.');
        $worse=$higherIsWorse?$delta>0:$delta<0;
        return $this->indicator($worse?'WORSENING':'IMPROVING',$worse?'Piorando':'Melhorando',$worse?'trending-down':'trending-up',$delta,$label.' '.($delta>0?'aumentou':'caiu').' '.number_format(abs($delta),1,',','.').' '.$unit.'.');
    }

    private function indicator(string $status,string $label,string $icon,?float $delta,string $explanation):array{return compact('status','label','icon','delta','explanation');}
    private function directionScore(array $indicator):float{return match($indicator['status']??''){ 'IMPROVING'=>1.0,'WORSENING'=>-1.0,default=>0.0};}
}
