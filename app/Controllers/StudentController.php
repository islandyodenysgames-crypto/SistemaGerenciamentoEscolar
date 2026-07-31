<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Settings\SettingManager;

use App\Controllers\Concerns\Student\StudentClassActions;
use App\Controllers\Concerns\Student\StudentCrudActions;
use App\Controllers\Concerns\Student\StudentIndexActions;
use App\Controllers\Concerns\Student\StudentProfileActions;
use App\Services\Occurrence\ActionService;
use App\Services\Occurrence\OccurrenceService;
use App\Services\FavoriteService;
use App\Services\SchoolClassService;
use App\Services\StudentService;
use App\Services\ProfilePhotoService;
use App\Services\RecurrenceIntelligenceService;
use App\Services\Intelligence\IntelligenceService;
use App\Services\Monitoring\StudentMonitoringService;

class StudentController extends BaseController
{
    use StudentIndexActions;
    use StudentProfileActions;
    use StudentCrudActions;
    use StudentClassActions;

    public function __construct(
        private StudentService $service,
        private SettingManager $settings,
        private SchoolClassService $classService,
        private OccurrenceService $occurrenceService,
        private ActionService $occurrenceActionService,
        private IntelligenceService $intelligenceService,
        private StudentMonitoringService $monitoringService,
        private RecurrenceIntelligenceService $recurrenceService,
        private FavoriteService $favoriteService,
        private ProfilePhotoService $profilePhotoService
    ) {
    }
}