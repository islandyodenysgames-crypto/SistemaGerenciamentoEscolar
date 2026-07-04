<?php

declare(strict_types=1);

namespace App\Services;

class DashboardService
{
    private AttendanceAnalyticsService $analyticsService;
    private StudentService $studentService;
    private SchoolClassService $schoolClassService;

    public function __construct()
    {
        $this->analyticsService = new AttendanceAnalyticsService();
        $this->studentService = new StudentService();
        $this->schoolClassService = new SchoolClassService();
    }

    public function data(): array
    {
        $today = date('Y-m-d');

        $central = $this->analyticsService->dailyCentral($today);

        return [
            'totalStudents' => $this->studentService->countActive(),
            'totalClasses' => $this->schoolClassService->countActive(),

            'ranking' => $this->analyticsService->dailyRanking($today),

            'schoolFrequencyToday' =>
                $this->analyticsService->schoolFrequencyToday($today),

            'schoolFrequencyWeek' =>
                $this->analyticsService->schoolFrequencyWeek(),

            'schoolFrequencyMonth' =>
                $this->analyticsService->schoolFrequencyMonth(),

            'schoolFrequencyYear' =>
                $this->analyticsService->schoolFrequencyYear(),

            'frequencyLast30Days' =>
                $this->analyticsService->schoolFrequencyLast30Days(),

            'classesWithoutAttendance' =>
                $this->analyticsService->classesWithoutAttendanceToday($today),

            'executive' => [
                'generalPercentage' => (float) ($central['generalPercentage'] ?? 0),
                'doneClasses' => (int) ($central['doneClasses'] ?? 0),
                'pendingClasses' => (int) ($central['pendingClasses'] ?? 0),
                'totalClasses' => (int) ($central['totalClasses'] ?? 0),
                'studentsInAlert' => $this->studentService->countInAlert(),
            ],
        ];
    }
}