<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AttendanceService;

class ReportController extends Controller
{
    private function guard(): void
    {
        if (!Session::has('user')) {
            Response::redirect(base_url('login'));
        }
    }

    public function index(): void
    {
        $this->guard();

        $this->view('relatorios/index', [
            'title' => 'Relatórios - ' . app_name(),
        ]);
    }

    public function daily(): void
    {
        $this->guard();

        $date = (string) Request::get('data', date('Y-m-d'));

        $service = new AttendanceService();

        $this->view('relatorios/diario', [
            'title' => 'Relatório Diário - ' . app_name(),
            'date' => $date,
            'summary' => $service->schoolFrequencyToday($date),
            'ranking' => $service->dailyRanking($date),
            'classesWithoutAttendance' => $service->classesWithoutAttendanceToday($date),
        ]);
    }
}