<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolPeriodRepository;
use App\Repositories\SchoolYearRepository;
use DateTimeImmutable;
use InvalidArgumentException;

final class SchoolPeriodService
{
    private const TYPES=['BIMESTER','TRIMESTER','SEMESTER','CUSTOM'];
    public function __construct(private SchoolPeriodRepository $periods,private SchoolYearRepository $years){}

    public function forYear(int $yearId): array
    {
        return array_map([$this,'decorate'],$this->periods->forYear($yearId));
    }

    public function find(int $id): ?array { return $this->periods->find($id); }

    public function save(?int $id,array $input): int
    {
        $yearId=(int)($input['school_year_id']??0);$year=$this->years->find($yearId);
        if(!$year) throw new InvalidArgumentException('Ano letivo não encontrado.');
        $name=trim((string)($input['name']??''));$type=strtoupper((string)($input['type']??'CUSTOM'));
        $start=$this->date((string)($input['start_date']??''));$end=$this->date((string)($input['end_date']??''));
        if($name===''||mb_strlen($name)>120) throw new InvalidArgumentException('Informe o nome do período.');
        if(!in_array($type,self::TYPES,true)) throw new InvalidArgumentException('Tipo de período inválido.');
        if($end<$start) throw new InvalidArgumentException('A data final não pode anteceder a inicial.');
        if($start<new DateTimeImmutable($year['start_date'])||$end>new DateTimeImmutable($year['end_date'])) throw new InvalidArgumentException('O período deve estar dentro da vigência do ano letivo.');
        if($this->periods->overlaps($yearId,$start->format('Y-m-d'),$end->format('Y-m-d'),$id)) throw new InvalidArgumentException('As datas se sobrepõem a outro período.');
        return $this->periods->save($id,[
            'school_year_id'=>$yearId,'name'=>$name,'short_name'=>trim((string)($input['short_name']??''))?:null,
            'type'=>$type,'order_number'=>max(1,(int)($input['order_number']??1)),
            'start_date'=>$start->format('Y-m-d'),'end_date'=>$end->format('Y-m-d'),
            'color'=>preg_match('/^#[0-9a-f]{6}$/i',(string)($input['color']??''))?(string)$input['color']:null,
            'description'=>trim((string)($input['description']??''))?:null,'status'=>'OPEN',
        ]);
    }

    public function delete(int $id): void {$this->periods->delete($id);}
    public function current(int $yearId): ?array {$row=$this->periods->current($yearId);return $row?$this->decorate($row):null;}

    private function date(string $value): DateTimeImmutable
    {
        $date=DateTimeImmutable::createFromFormat('!Y-m-d',$value);
        if(!$date) throw new InvalidArgumentException('Informe datas válidas.');
        return $date;
    }
    private function decorate(array $row): array
    {
        $today=new DateTimeImmutable('today');$start=new DateTimeImmutable($row['start_date']);$end=new DateTimeImmutable($row['end_date']);
        $total=max(1,(int)$start->diff($end)->days+1);$elapsed=$today<$start?0:($today>$end?$total:(int)$start->diff($today)->days+1);
        $row['progress']=round(min(100,$elapsed/$total*100),1);$row['days_remaining']=$today>$end?0:max(0,(int)$today->diff($end)->days);
        return $row;
    }
}
