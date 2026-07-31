<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Database\Connection;
use PDO;

final class ContentContextService
{
    public function build(array $dashboard, array $hallOfFame, string $date): array
    {
        $frequency=(array)($dashboard['schoolFrequencyToday']??[]);
        $ranking=(array)($dashboard['ranking']??[]);
        $today=$this->snapshot($date);
        $previous=$this->previousSchoolDay($date);
        $percentage=(float)($frequency['percentage']??$frequency['attendance_percentage']??$today['percentage']);
        $present=(int)($frequency['presentes']??$frequency['present']??$today['present']);
        $absent=(int)($frequency['faltas']??$frequency['absent']??$today['absent']);
        $winner=is_array($ranking[0]??null)?$ranking[0]:[];
        $change=$previous['total']>0?round($percentage-$previous['percentage'],1):null;
        return [
            'attendance'=>number_format($percentage,1,',','.'),
            'attendance_raw'=>$percentage,
            'present'=>number_format($present,0,',','.'),
            'absent'=>number_format($absent,0,',','.'),
            'class'=>(string)($winner['class_name']??'turma destaque'),
            'class_attendance'=>number_format((float)($winner['attendance_percentage']??0),1,',','.'),
            'change'=>$change===null?'0,0':number_format($change,1,',','.'),
            'change_abs'=>$change===null?'0,0':number_format(abs($change),1,',','.'),
            'change_raw'=>$change,
            'evolution_class'=>(string)($hallOfFame['evolution']['class_name']??'uma de nossas turmas'),
            'calendar_events'=>count((array)($dashboard['schoolCalendar']['upcoming']??[])),
            'reference_date'=>$date,
        ];
    }

    public function comparison(string $date): array
    {
        $today=$this->snapshot($date);$previous=$this->previousSchoolDay($date);
        return ['today'=>$today['percentage'],'yesterday'=>$previous['percentage'],'change'=>$previous['total']>0?round($today['percentage']-$previous['percentage'],1):null];
    }

    private function previousSchoolDay(string $date): array
    {
        $stmt=Connection::getInstance()->prepare("SELECT MAX(attendance_date) FROM attendance WHERE attendance_date<:date");
        $stmt->execute(['date'=>$date]);$previous=(string)($stmt->fetchColumn()?:'');
        return $previous!==''?$this->snapshot($previous):['total'=>0,'present'=>0,'absent'=>0,'percentage'=>0.0];
    }

    private function snapshot(string $date): array
    {
        $stmt=Connection::getInstance()->prepare("SELECT COUNT(ai.id) total,SUM(ai.status='P') present,SUM(ai.status='F') absent FROM attendance a INNER JOIN attendance_items ai ON ai.attendance_id=a.id WHERE a.attendance_date=:date");
        $stmt->execute(['date'=>$date]);$row=$stmt->fetch(PDO::FETCH_ASSOC)?:[];$total=(int)($row['total']??0);$present=(int)($row['present']??0);
        return ['total'=>$total,'present'=>$present,'absent'=>(int)($row['absent']??0),'percentage'=>$total>0?round($present*100/$total,1):0.0];
    }
}
