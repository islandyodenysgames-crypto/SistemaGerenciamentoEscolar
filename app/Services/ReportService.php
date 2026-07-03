<?php

declare(strict_types=1);

namespace App\Services;

class ReportService
{
    private AttendanceService $attendanceService;

    public function __construct()
    {
        $this->attendanceService = new AttendanceService();
    }

    public function daily(string $date): array
    {
        return [
            'summary' => $this->attendanceService->schoolFrequencyToday($date),
            'ranking' => $this->attendanceService->dailyRanking($date),
            'classesWithoutAttendance' =>
                $this->attendanceService->classesWithoutAttendanceToday($date),
        ];
    }
}