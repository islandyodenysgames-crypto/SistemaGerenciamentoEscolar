<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Services\Intelligence\IntelligenceService;
use App\Services\Intelligence\IntelligenceDailySnapshotService;
use App\Core\Response;
use App\Core\Session;
use App\Core\Request;

class IntelligenceController extends BaseController
{
    public function __construct(
        private IntelligenceService $service,
        private IntelligenceDailySnapshotService $snapshotService
    ) {
    }

    public function cases(): void
    {
        if (!$this->guard()) {
            return;
        }
        $user = (array) Session::get('user', []);
        $isTeacher = strtoupper((string) ($user['role'] ?? '')) === 'TEACHER';
        if (!$isTeacher && !$this->authorize(Permissions::INTELLIGENCE_VIEW)) {
            return;
        }

        $type = (string) Request::get('tipo', 'students_attention');
        $cases = $this->service->cases($type, [
            'q' => Request::get('q', ''),
            'risk' => Request::get('risco', ''),
            'class' => Request::get('turma', ''),
            'page' => Request::get('pagina', 1),
            'per_page' => Request::get('por_pagina', 10),
        ]);

        $this->view('pages/intelligence/cases', [
            'title' => $cases['meta']['title'] . ' - ' . app_name(),
            'cases' => $cases,
        ]);
    }


    public function comparisons(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::INTELLIGENCE_VIEW)) {
            return;
        }

        $classId = (int) Request::get('turma', 0);
        $this->view('pages/intelligence/comparisons', [
            'title' => 'Comparações entre turmas - ' . app_name(),
            'comparison' => $this->service->classComparisons($classId > 0 ? $classId : null),
        ]);
    }

    public function index(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::INTELLIGENCE_VIEW)) {
            return;
        }

        // Captura no máximo uma vez por dia. Se a migração ainda não tiver sido
        // executada, a Central continua funcionando normalmente.
        $this->snapshotService->captureToday();

        $this->view('pages/intelligence/index', [
            'title' => 'Central de Inteligência - ' . app_name(),
            'intelligence' => $this->service->dashboard(),
        ]);
    }
}
