<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\SchoolRepository;
use App\Services\SchoolService;

class SettingsController extends Controller
{
    private SchoolService $schoolService;

    public function __construct()
    {
        $this->schoolService = new SchoolService(
            new SchoolRepository()
        );
    }

    public function index(): void
    {
        view('pages/settings/index', [
            'title' => 'Configurações',
        ]);
    }

    public function identity(): void
    {
        view('pages/settings/identity', [
            'title' => 'Identidade da Escola',
            'school' => $this->schoolService->current(),
        ]);
    }
}