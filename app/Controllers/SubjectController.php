<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Controllers\Concerns\Subject\SubjectCrudActions;
use App\Services\SubjectService;

class SubjectController extends BaseController
{
    use SubjectCrudActions;

    public function __construct(
        private SubjectService $service
    ) {
    }
}