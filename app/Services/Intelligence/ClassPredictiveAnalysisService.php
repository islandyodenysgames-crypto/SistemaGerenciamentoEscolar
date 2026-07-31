<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\ValueObjects\Intelligence\SchoolClassPrediction;

final class ClassPredictiveAnalysisService
{
    public function __construct(private readonly ClassTrendAnalysisService $trends) {}

    public function schoolClass(int $classId, int $days = 30, int $horizonDays = 14): array
    {
        return $this->fromTrend($this->trends->schoolClass($classId,$days),$horizonDays)->toArray();
    }

    public function classes(array $classIds,int $days=30,int $horizonDays=14):array
    {
        $result=[];
        foreach($this->trends->classes($classIds,$days) as $id=>$trend){$result[(int)$id]=$this->fromTrend($trend,$horizonDays)->toArray();}
        return $result;
    }

    public function fromTrend(array $trend,int $horizonDays=14):SchoolClassPrediction
    {
        if(($trend['overall_status']??'INSUFFICIENT')==='INSUFFICIENT')return new SchoolClassPrediction('INSUFFICIENT','Histórico insuficiente','Ainda não há histórico suficiente para projetar o comportamento coletivo da turma.','LOW',$horizonDays,[],['Acompanhar diariamente até que o sistema reúna histórico suficiente para uma projeção confiável.']);
        $status=(string)$trend['overall_status'];
        $evidence=[];$recommendations=[];
        foreach(['frequency'=>'frequência','occurrences'=>'ocorrências','risk'=>'risco médio','coverage'=>'cobertura','recommendations'=>'recomendações pendentes'] as $key=>$label){$item=(array)($trend[$key]??[]);if(in_array($item['status']??'', ['WORSENING','IMPROVING'],true))$evidence[]=(string)($item['explanation']??$label);}
        if($status==='WORSENING'){$label='Tendência de agravamento';$summary='Mantido o comportamento atual, a turma tende a exigir maior atenção nas próximas duas semanas.';$recommendations=['Revisar os alunos prioritários da turma.','Intensificar a busca ativa e o acompanhamento de frequência.','Avaliar estratégias coletivas com a equipe pedagógica.','Priorizar recomendações pendentes.'];}
        elseif($status==='IMPROVING'){$label='Tendência de melhora';$summary='Mantidas as estratégias atuais, a turma tende a consolidar evolução positiva nas próximas duas semanas.';$recommendations=['Manter as estratégias que produziram melhora.','Confirmar se a evolução alcança os alunos prioritários.','Registrar as ações eficazes para reutilização.'];}
        else{$label='Tendência de estabilidade';$summary='O cenário mais provável é de manutenção do comportamento coletivo nas próximas duas semanas.';$recommendations=['Acompanhar os indicadores semanalmente.','Atuar preventivamente nos alunos que destoam da turma.'];}
        return new SchoolClassPrediction($status,$label,$summary,(string)($trend['confidence']??'LOW'),$horizonDays,array_slice($evidence,0,5),$recommendations);
    }
}
