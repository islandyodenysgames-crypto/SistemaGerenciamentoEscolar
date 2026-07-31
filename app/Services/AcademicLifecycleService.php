<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AcademicLifecycleRepository;
use App\Repositories\SchoolPeriodRepository;
use App\Repositories\SchoolYearRepository;
use InvalidArgumentException;

final class AcademicLifecycleService
{
    public function __construct(private AcademicLifecycleRepository $repository,private SchoolYearRepository $years,private SchoolPeriodRepository $periods){}
    public function closePeriod(int $id,int $userId,?string $ip): void
    {
        $p=$this->repository->period($id);if(!$p)throw new InvalidArgumentException('Período não encontrado.');
        if($p['status']==='CLOSED')throw new InvalidArgumentException('O período já está fechado.');
        $metrics=$this->repository->metrics($p['start_date'],$p['end_date']);
        $this->repository->snapshot((int)$p['school_year_id'],$id,'PERIOD_CLOSE',$metrics,$userId);
        $this->repository->setPeriodStatus($id,'CLOSED');
        $this->repository->audit((int)$p['school_year_id'],$id,'PERIOD_CLOSED','Período letivo fechado.',$metrics,$userId,$ip);
    }
    public function reopenPeriod(int $id,int $userId,?string $ip): void
    {
        $p=$this->repository->period($id);if(!$p)throw new InvalidArgumentException('Período não encontrado.');
        $this->repository->setPeriodStatus($id,'REOPENED');
        $this->repository->audit((int)$p['school_year_id'],$id,'PERIOD_REOPENED','Período letivo reaberto.',[],$userId,$ip);
    }
    public function closeYear(int $id,int $userId,?string $ip): void
    {
        $year=$this->years->find($id);if(!$year)throw new InvalidArgumentException('Ano letivo não encontrado.');
        foreach($this->periods->forYear($id) as $p){if($p['status']!=='CLOSED')throw new InvalidArgumentException('Feche todos os períodos antes de encerrar o ano.');}
        $metrics=$this->repository->metrics($year['start_date'],$year['end_date']);
        $this->repository->snapshot($id,null,'YEAR_CLOSE',$metrics,$userId);$this->years->setStatus($id,'CLOSED');
        $this->repository->audit($id,null,'YEAR_CLOSED','Ano letivo encerrado.',$metrics,$userId,$ip);
    }
    public function createNextYear(int $sourceId,array $input,int $userId,?string $ip): int
    {
        $source=$this->years->find($sourceId);if(!$source)throw new InvalidArgumentException('Ano de origem não encontrado.');
        $year=(int)($input['year']??((int)$source['year']+1));$start=(string)($input['start_date']??$year.'-02-01');$end=(string)($input['end_date']??$year.'-12-20');
        if($this->years->yearExists($year))throw new InvalidArgumentException('O novo ano já está cadastrado.');
        $id=$this->years->create(['name'=>trim((string)($input['name']??''))?:'Ano Letivo '.$year,'year'=>$year,'start_date'=>$start,'end_date'=>$end,'status'=>'PREPARATION','is_active'=>0,'notes'=>'Criado pelo Assistente de Novo Ano']);
        if(!empty($input['copy_periods']))foreach($this->periods->forYear($sourceId) as $p){$offset=$year-(int)$source['year'];$this->periods->save(null,['school_year_id'=>$id,'name'=>$p['name'],'short_name'=>$p['short_name'],'type'=>$p['type'],'order_number'=>$p['order_number'],'start_date'=>date('Y-m-d',strtotime($p['start_date']." {$offset} year")),'end_date'=>date('Y-m-d',strtotime($p['end_date']." {$offset} year")),'color'=>$p['color'],'description'=>$p['description'],'status'=>'OPEN']);}
        $this->repository->audit($id,null,'YEAR_CREATED_BY_ASSISTANT','Novo ano criado pelo assistente.',['source_year_id'=>$sourceId,'periods_copied'=>!empty($input['copy_periods'])],$userId,$ip);return $id;
    }
    public function history(): array{return $this->repository->history();}
    public function audits(): array{return $this->repository->audits();}
}
