<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolDayRepository;
use App\Repositories\SchoolPeriodRepository;
use App\Repositories\SchoolYearRepository;

final class CurrentAcademicContextService
{
    public function __construct(private SchoolYearRepository $years,private SchoolPeriodRepository $periods,private SchoolDayRepository $days){}
    public function get(?string $date=null): array
    {
        $date=$date?:date('Y-m-d');$year=$this->years->active();
        if(!$year)return ['year'=>null,'period'=>null,'days'=>[]];
        return ['year'=>$year,'period'=>$this->periods->current((int)$year['id'],$date),'days'=>$this->days->summary((int)$year['id'])];
    }
}
