<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Intelligence\IntelligenceService;
use App\Core\Settings\SettingManager;

class DashboardService
{
    public function __construct(
        private AttendanceAnalyticsService $analyticsService,
        private StudentService $studentService,
        private SchoolClassService $schoolClassService,
        private OccurrenceService $occurrenceService,
        private NoticeService $noticeService,
        private SchoolCalendarService $calendarService,
        private IntelligenceService $intelligenceService,
        private FavoriteService $favoriteService,
        private SettingManager $settings,
        private SchoolGeneralIndexService $schoolGeneralIndexService
    ) {
    }

    public function data(): array
    {
        $today = date('Y-m-d');

        $central = $this->analyticsService->dailyCentral(
            $today
        );

        $user = (array) \App\Core\Session::get('user', []);
        $frequencyGoal = max(0.0, min(100.0, (float)$this->settings->get('school_goals.frequency_goal', 95.0)));
        $attentionThreshold = max(0.0, $frequencyGoal - 5.0);

        $intelligence = $this->intelligenceService->dashboard();
        $schoolIndex = $this->schoolGeneralIndexService->build([
            'frequency' => (float) ($central['generalPercentage'] ?? 0),
            'frequency_goal' => $frequencyGoal,
            'active_students' => $this->studentService->countActive(),
            'total_classes' => $this->schoolClassService->countActive(),
            'priority_students' => (int) ($intelligence['summary']['students_in_attention'] ?? 0),
            'critical_students' => (int) ($intelligence['summary']['critical_students'] ?? 0),
            'attention_classes' => (int) ($intelligence['summary']['classes_in_attention'] ?? 0),
            'occurrences' => (int) ($intelligence['occurrences']['total'] ?? 0),
            'serious_occurrences' => (int) ($intelligence['occurrences']['serious'] ?? 0),
            'open_occurrences' => (int) ($intelligence['occurrences']['open'] ?? 0),
            'monitoring_coverage' => (float) ($intelligence['monitoring']['coverage_percentage'] ?? 0),
        ]);

        return [
            /*
             * Dados gerais
             */
            'totalStudents' => $this->studentService
                ->countActive(),

            'totalClasses' => $this->schoolClassService
                ->countActive(),

            /*
             * Frequência
             */
            'ranking' => $this->analyticsService
                ->dailyRanking($today),

            'schoolFrequencyToday' => $this->analyticsService
                ->schoolFrequencyToday($today),

            'schoolFrequencyWeek' => $this->analyticsService
                ->schoolFrequencyWeek(),

            'schoolFrequencyMonth' => $this->analyticsService
                ->schoolFrequencyMonth(),

            'schoolFrequencyYear' => $this->analyticsService
                ->schoolFrequencyYear(),

            'frequencyLast30Days' => $this->analyticsService
                ->schoolFrequencyByPreset((string) ($_GET['frequency_period'] ?? '30d')),

            'frequencyPeriod' => (string) ($_GET['frequency_period'] ?? '30d'),

            'frequencyHeatmap' => $this->analyticsService->schoolFrequencyHeatmapByPreset((string) ($_GET['heatmap_period'] ?? 'month')),

            'classesWithoutAttendance' => $this->analyticsService
                ->classesWithoutAttendanceToday($today),

            /*
             * Painel executivo
             */
            'executive' => [
                'generalPercentage' => (float) (
                    $central['generalPercentage'] ?? 0
                ),

                'doneClasses' => (int) (
                    $central['doneClasses'] ?? 0
                ),

                'pendingClasses' => (int) (
                    $central['pendingClasses'] ?? 0
                ),

                'totalClasses' => (int) (
                    $central['totalClasses'] ?? 0
                ),

                'studentsInAlert' => $this->studentService
                    ->countInAlert($frequencyGoal),
                'frequencyGoal' => $frequencyGoal,
                'attentionThreshold' => $attentionThreshold,
            ],

            /*
             * Ocorrências
             */
            'occurrenceSummary' => [
                'today' => $this->occurrenceService
                    ->countToday(),

                'open' => $this->occurrenceService
                    ->countOpen(),

                'resolved' => $this->occurrenceService
                    ->countResolved(),

                'currentMonth' => $this->occurrenceService
                    ->countCurrentMonth(),
            ],

            /*
             * Avisos da Gestão
             */
            // Todos os avisos vigentes são enviados ao mural. A navegação
            // do carrossel cuida da quantidade exibida por vez.
            'activeNotices' => $this->noticeService
                ->activeForDashboard(),

            'activeNoticesCount' => $this->noticeService
                ->countActive(),
            
            'noticesPriority' => $this->noticeService
                 ->highestActivePriority(),

            /* Calendário Escolar */
            'schoolCalendar' => $this->calendarService->dashboard(),

            /*
             * Inteligência escolar
             */
            'intelligence' => $intelligence,
            'schoolIndex' => $schoolIndex,

            'favorites' => $this->favoriteService->allForUser((int) ($user['id'] ?? 0)),
            'schoolGoals' => ['frequency_goal' => $frequencyGoal, 'attention_threshold' => $attentionThreshold],

        ];
    }
}