<?php

declare(strict_types=1);

namespace App\Services;

class ReportService
{
    private AttendanceAnalyticsService $analyticsService;

    public function __construct()
    {
        $this->analyticsService = new AttendanceAnalyticsService();
    }

    public function daily(string $date): array
    {
        return [
            'summary' => $this->analyticsService->schoolFrequencyToday($date),
            'ranking' => $this->analyticsService->dailyRanking($date),
            'classesWithoutAttendance' =>
                $this->analyticsService->classesWithoutAttendanceToday($date),
        ];
    }
}