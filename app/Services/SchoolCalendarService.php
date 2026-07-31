<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolCalendarRepository;
use InvalidArgumentException;

class SchoolCalendarService
{
    private const TYPES=['ASSESSMENT','MEETING','COUNCIL','EVENT','TRAINING','HOLIDAY','DEADLINE','OTHER'];
    public function __construct(private SchoolCalendarRepository $repository){}
    public function types(): array { return ['ASSESSMENT'=>'Avaliação','MEETING'=>'Reunião','COUNCIL'=>'Conselho de classe','EVENT'=>'Evento','TRAINING'=>'Formação','HOLIDAY'=>'Feriado/Recesso','DEADLINE'=>'Prazo','OTHER'=>'Outro']; }
    public function all(): array { return $this->repository->all(); }
    public function find(int $id): ?array { return $this->repository->find($id); }
    public function upcoming(int $limit=6): array { return $this->repository->upcoming($limit); }
    public function dashboard(?int $year=null,?int $month=null): array {
        $year=$year ?: (int)date('Y'); $month=$month ?: (int)date('n');
        $start=sprintf('%04d-%02d-01',$year,$month); $end=date('Y-m-t',strtotime($start));
        return ['year'=>$year,'month'=>$month,'events'=>$this->repository->forRange($start,$end),'upcoming'=>$this->upcoming(5)];
    }
    public function create(array $data): int { return $this->repository->create($this->normalize($data,true)); }
    public function update(int $id,array $data): bool { $d=$this->normalize($data,false); unset($d['created_by']); return $this->repository->update($id,$d); }
    public function delete(int $id): bool { return $this->repository->delete($id); }
    private function normalize(array $d,bool $creating): array {
        $title=trim((string)($d['title']??'')); if($title==='') throw new InvalidArgumentException('Informe o título do evento.');
        $type=strtoupper(trim((string)($d['type']??'OTHER'))); if(!in_array($type,self::TYPES,true)) throw new InvalidArgumentException('Tipo de evento inválido.');
        $start=(string)($d['start_date']??''); if(!$this->validDate($start)) throw new InvalidArgumentException('Informe uma data inicial válida.');
        $end=trim((string)($d['end_date']??'')); if($end!==''&&!$this->validDate($end)) throw new InvalidArgumentException('Informe uma data final válida.');
        if($end!==''&&$end<$start) throw new InvalidArgumentException('A data final não pode ser anterior à inicial.');
        $startTime=$this->time($d['start_time']??null);
        $endTime=$this->time($d['end_time']??null);
        // Horários informados sempre prevalecem sobre uma marcação antiga de "dia inteiro".
        $allDay=(!empty($d['all_day']) && $startTime===null && $endTime===null)?1:0;
        if($startTime!==null && $endTime!==null && $endTime<$startTime) throw new InvalidArgumentException('O horário final não pode ser anterior ao horário inicial.');
        $result=['title'=>$title,'description'=>trim((string)($d['description']??''))?:null,'type'=>$type,'start_date'=>$start,'end_date'=>$end?:null,'start_time'=>$allDay?null:$startTime,'end_time'=>$allDay?null:$endTime,'location'=>trim((string)($d['location']??''))?:null,'all_day'=>$allDay,'featured'=>((string)($d['featured']??'0'))==='1'?1:0,'active'=>((string)($d['active']??'0'))==='1'?1:0];
        if($creating) $result['created_by']=(int)($d['created_by']??0)?:null;
        return $result;
    }
    private function validDate(string $v): bool { $dt=\DateTime::createFromFormat('Y-m-d',$v); return $dt&&$dt->format('Y-m-d')===$v; }
    private function time(mixed $v): ?string { $v=trim((string)$v); return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/',$v)?$v:null; }
}
