<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolDayRepository;
use App\Repositories\SchoolPeriodRepository;
use App\Repositories\SchoolYearRepository;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use InvalidArgumentException;

final class SchoolDayService
{
    private const TYPES=['SCHOOL_DAY','HOLIDAY','RECESS','VACATION','PLANNING','COUNCIL','STOPPAGE','MAKEUP','SATURDAY_SCHOOL','OPTIONAL'];
    public function __construct(private SchoolDayRepository $days,private SchoolYearRepository $years,private SchoolPeriodRepository $periods){}
    public function generateWeekdays(int $yearId): int
    {
        $year=$this->years->find($yearId);if(!$year)throw new InvalidArgumentException('Ano letivo não encontrado.');
        $count=0;$range=new DatePeriod(new DateTimeImmutable($year['start_date']),new DateInterval('P1D'),(new DateTimeImmutable($year['end_date']))->modify('+1 day'));
        foreach($range as $date){if((int)$date->format('N')>5)continue;$period=$this->periods->current($yearId,$date->format('Y-m-d'));
            $this->days->upsert(['school_year_id'=>$yearId,'school_period_id'=>$period['id']??null,'calendar_event_id'=>null,'school_date'=>$date->format('Y-m-d'),'day_type'=>'SCHOOL_DAY','is_instructional'=>1,'is_completed'=>0,'title'=>'Dia letivo','notes'=>null]);$count++;}
        return $count;
    }
    public function save(array $input): void
    {
        $yearId=(int)($input['school_year_id']??0);$year=$this->years->find($yearId);if(!$year)throw new InvalidArgumentException('Ano letivo não encontrado.');
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',(string)($input['school_date']??''));if(!$date)throw new InvalidArgumentException('Data inválida.');
        if($date<new DateTimeImmutable($year['start_date'])||$date>new DateTimeImmutable($year['end_date']))throw new InvalidArgumentException('A data está fora do ano letivo.');
        $type=strtoupper((string)($input['day_type']??''));if(!in_array($type,self::TYPES,true))throw new InvalidArgumentException('Tipo de dia inválido.');
        $instructional=in_array($type,['SCHOOL_DAY','MAKEUP','SATURDAY_SCHOOL'],true)?1:0;$period=$this->periods->current($yearId,$date->format('Y-m-d'));
        $this->days->upsert(['school_year_id'=>$yearId,'school_period_id'=>$period['id']??null,'calendar_event_id'=>$input['calendar_event_id']??null,'school_date'=>$date->format('Y-m-d'),'day_type'=>$type,'is_instructional'=>$instructional,'is_completed'=>0,'title'=>trim((string)($input['title']??''))?:null,'notes'=>trim((string)($input['notes']??''))?:null]);
    }
    public function syncCalendarEvent(int $eventId,array $event): void
    {
        if(empty($event['affects_school_day'])||empty($event['school_year_id'])||empty($event['school_day_type']))return;
        $start=new DateTimeImmutable((string)$event['start_date']);$end=new DateTimeImmutable((string)($event['end_date']?:$event['start_date']));
        foreach(new DatePeriod($start,new DateInterval('P1D'),$end->modify('+1 day')) as $date){
            $this->save(['school_year_id'=>(int)$event['school_year_id'],'school_date'=>$date->format('Y-m-d'),'day_type'=>$event['school_day_type'],'title'=>$event['title'],'notes'=>$event['description']??null,'calendar_event_id'=>$eventId]);
        }
    }
    public function data(int $yearId): array{return ['days'=>$this->days->forYear($yearId),'summary'=>$this->days->summary($yearId)];}
}
