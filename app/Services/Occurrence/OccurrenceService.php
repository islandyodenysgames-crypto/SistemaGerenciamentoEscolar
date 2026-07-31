<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\Repositories\Occurrence\OccurrenceRepository;
use App\Services\OccurrenceAttachmentService;
use App\Services\Occurrence\Concerns\OccurrenceAnalysisOperations;
use App\Services\Occurrence\Concerns\OccurrenceCalendarOperations;
use App\Services\Occurrence\Concerns\OccurrenceCatalogOperations;
use App\Services\Occurrence\Concerns\OccurrenceCrudOperations;
use App\Services\Occurrence\Concerns\OccurrenceDashboardOperations;
use App\Services\Occurrence\Concerns\OccurrenceFormattingOperations;
use App\Services\Occurrence\Concerns\OccurrenceStatisticsOperations;
use App\Services\Occurrence\Concerns\OccurrenceStudentOperations;
use App\Services\Occurrence\Concerns\OccurrenceValidationOperations;

class OccurrenceService
{
    use OccurrenceCrudOperations;
    use OccurrenceAnalysisOperations;
    use OccurrenceStudentOperations;
    use OccurrenceDashboardOperations;
    use OccurrenceStatisticsOperations;
    use OccurrenceCalendarOperations;
    use OccurrenceCatalogOperations;
    use OccurrenceFormattingOperations;
    use OccurrenceValidationOperations;

    private const TYPES = [
        'OBSERVATION',
        'WARNING',
        'SUSPENSION',
        'REFERRAL',
        'PRAISE',
        'OTHER',
    ];

    private const STATUSES = [
        'OPEN',
        'RESOLVED',
    ];

    public function __construct(
        private OccurrenceRepository $repository,
        private SeverityService $severityService,
        private OccurrenceAnalysisContextFactory $analysisContextFactory,
        private OccurrenceAnalysisService $analysisService,
        private OccurrenceAttachmentService $attachmentService
    ) {
    }
}
