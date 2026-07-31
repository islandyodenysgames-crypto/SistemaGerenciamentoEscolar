<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\Occurrence\Actions\OccurrenceActionActions;
use App\Controllers\Concerns\Occurrence\Create\OccurrenceCreateActions;
use App\Controllers\Concerns\Occurrence\Management\OccurrenceManageActions;
use App\Controllers\Concerns\Occurrence\Multiple\OccurrenceMultipleActions;
use App\Controllers\Concerns\Occurrence\Shared\OccurrenceHelpers;
use App\Services\Occurrence\ActionService;
use App\Services\Occurrence\ActionAttachmentService;
use App\Services\Occurrence\NotificationService;
use App\Services\Occurrence\OccurrenceService;
use App\Services\StudentService;
use App\Services\SubjectService;
use App\Services\OccurrenceAttachmentService;

class OccurrenceController extends BaseController
{
    use OccurrenceCreateActions;
    use OccurrenceMultipleActions;
    use OccurrenceManageActions;
    use OccurrenceActionActions;
    use OccurrenceHelpers;

    public function __construct(
        private OccurrenceService $service,
        private StudentService $studentService,
        private ActionService $actionService,
        private SubjectService $subjectService,
        private NotificationService $notificationService,
        private OccurrenceAttachmentService $attachmentService,
        private ActionAttachmentService $actionAttachmentService
    ) {
    }
}