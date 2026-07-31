<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence;

use App\Repositories\BaseRepository;
use App\Repositories\Occurrence\Concerns\OccurrenceCalendarQueries;
use App\Repositories\Occurrence\Concerns\OccurrenceCrudQueries;
use App\Repositories\Occurrence\Concerns\OccurrenceDashboardQueries;
use App\Repositories\Occurrence\Concerns\OccurrenceLookupQueries;
use App\Repositories\Occurrence\Concerns\OccurrenceStatisticsQueries;
use App\Repositories\Occurrence\Concerns\OccurrenceStudentQueries;

class OccurrenceRepository extends BaseRepository
{
    use OccurrenceCrudQueries;
    use OccurrenceLookupQueries;
    use OccurrenceStudentQueries;
    use OccurrenceStatisticsQueries;
    use OccurrenceDashboardQueries;
    use OccurrenceCalendarQueries;
}