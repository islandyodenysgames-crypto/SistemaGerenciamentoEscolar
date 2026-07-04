<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $service
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

        $this->view('pages/reports/index', [
            'title' => 'Relatórios - ' . app_name(),
        ]);
    }

    public function daily(): void
    {
        $this->guard();

        $date = (string) Request::get('data', date('Y-m-d'));

        $this->view('pages/reports/diario', array_merge(
            [
                'title' => 'Relatório Diário - ' . app_name(),
                'date' => $date,
            ],
            $this->service->daily($date)
        ));
    }
}