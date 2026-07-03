<?php

declare(strict_types=1);

namespace App\Services;

class DashboardService
{
    private AttendanceService $attendanceService;
    private StudentService $studentService;
    private SchoolClassService $schoolClassService;

    public function __construct()
    {
        $this->attendanceService = new AttendanceService();
        $this->studentService = new StudentService();
        $this->schoolClassService = new SchoolClassService();
    }

    public function data(): array
    {
        $today = date('Y-m-d');

        return [
            'totalStudents' => $this->studentService->countActive(),

            'totalClasses' => $this->schoolClassService->countActive(),

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