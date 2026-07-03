<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Services\AttendanceService;

class DashboardController extends Controller
{
    public function index(): void
    {
        if (!Session::has('user')) {
            Response::redirect(base_url('login'));
        }

        $attendanceService = new AttendanceService();

        $today = date('Y-m-d');

        $this->view('dashboard/index', [
            'title' => 'Dashboard - ' . app_name(),
            'user' => Session::get('user'),
            'ranking' => $attendanceService->dailyRanking($today),
            'schoolFrequencyToday' => $attendanceService->schoolFrequencyToday($today),
            'frequencyLast30Days' => $attendanceService->schoolFrequencyLast30Days(),
        ]);
    }
}