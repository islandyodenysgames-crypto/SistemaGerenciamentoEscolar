<?php

declare(strict_types=1);

namespace App\Services;

class DashboardService
{
    private AttendanceService $attendanceService;

    public function __construct()
    {
        $this->attendanceService = new AttendanceService();
    }

    public function data(): array
    {
        $today = date('Y-m-d');

        return [
            'ranking' => $this->attendanceService->dailyRanking($today),

            'schoolFrequencyToday' =>
                $this->attendanceService->schoolFrequencyToday($today),

            'schoolFrequencyWeek' =>
                $this->attendanceService->schoolFrequencyWeek(),

            'schoolFrequencyMonth' =>
                $this->attendanceService->schoolFrequencyMonth(),

            'schoolFrequencyYear' =>
                $this->attendanceService->schoolFrequencyYear(),

            'frequencyLast30Days' =>
                $this->attendanceService->schoolFrequencyLast30Days(),

            'classesWithoutAttendance' =>
                $this->attendanceService->classesWithoutAttendanceToday($today),
        ];
    }
}