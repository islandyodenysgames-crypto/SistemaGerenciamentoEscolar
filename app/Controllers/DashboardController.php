<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service
    ) {
    }

    private function guard(): void
    {
        if (!Session::has('user')) {
            Response::redirect(base_url('login'));
        }
    }

    public function index(): void
    {
        $this->guard();

        $this->view('pages/dashboard/index', array_merge(
            [
                'title' => 'Dashboard - ' . app_name(),
            ],
            $this->service->data()
        ));
    }
}