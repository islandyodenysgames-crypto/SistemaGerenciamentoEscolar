<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Services\Occurrence\OccurrenceService;
use App\Services\Occurrence\OccurrenceIndicatorService;
use App\Services\SchoolClassService;
use App\Services\SubjectService;
use App\Services\RecurrenceIntelligenceService;

class OccurrenceDashboardController extends Controller
{
    public function __construct(
        private OccurrenceService $service,
        private SchoolClassService $classService,
        private SubjectService $subjectService,
        private OccurrenceIndicatorService $indicatorService,
        private RecurrenceIntelligenceService $recurrenceService
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

        $year = (int) Request::get('ano', date('Y'));
        $month = (int) Request::get('mes', date('n'));

        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        if ($year < 2000 || $year > ((int) date('Y') + 2)) {
            $year = (int) date('Y');
        }

        $selectedDate = trim((string) Request::get('dia', ''));

        if (!$this->isValidDate($selectedDate)) {
            $selectedDate = '';
        }

        $filters = [
            'search' => trim((string) Request::get('busca', '')),
            'class_id' => max(0, (int) Request::get('turma', 0)),
            'subject_id' => max(0, (int) Request::get('disciplina', 0)),
            'severity' => strtoupper(trim((string) Request::get('gravidade', ''))),
            'type' => strtoupper(trim((string) Request::get('tipo', ''))),
            'status' => strtoupper(trim((string) Request::get('status', ''))),
            'date_from' => trim((string) Request::get('data_inicial', '')),
            'date_to' => trim((string) Request::get('data_final', '')),
        ];

        $types = $this->service->types();
        $statuses = $this->service->statuses();
        $severities = $this->service->severities();

        if (!isset($types[$filters['type']])) {
            $filters['type'] = '';
        }

        if (!isset($statuses[$filters['status']])) {
            $filters['status'] = '';
        }

        if (!isset($severities[$filters['severity']])) {
            $filters['severity'] = '';
        }

        if (!$this->isValidDate($filters['date_from'])) {
            $filters['date_from'] = '';
        }

        if (!$this->isValidDate($filters['date_to'])) {
            $filters['date_to'] = '';
        }

        $this->view('pages/occurrences/dashboard', [
            'title' => 'Ocorrências - ' . app_name(),
            'totalToday' => $this->service->countToday(),
            'totalOpen' => $this->service->countOpen(),
            'totalResolved' => $this->service->countResolved(),
            'totalCurrentMonth' => $this->service->countCurrentMonth(),
            'occurrenceIndicators' => $this->indicatorService->dashboard(),
            'mostFrequentTypes' => $this->service->mostFrequentTypes(6),
            'studentsRanking' => $this->service->studentsWithMostOccurrences(5),
            'classesRanking' => $this->service->classesWithMostOccurrences(5),
            'subjectsRanking' => $this->service->subjectsWithMostOccurrences(10),
            'recentOccurrences' => $this->service->filteredRecent($filters, 50),
            'occurrenceFilters' => $filters,
            'filterClasses' => $this->classService->all(),
            'filterSubjects' => $this->subjectService->active(),
            'occurrenceTypes' => $types,
            'occurrenceStatuses' => $statuses,
            'occurrenceSeverities' => $severities,
            'occurrenceSeverityClasses' => $this->service->severityClasses(),
            'occurrenceSeverityIcons' => $this->service->severityIcons(),
            'occurrenceCalendar' => $this->service->calendarMonth($year, $month),
            'calendarYear' => $year,
            'calendarMonth' => $month,
            'selectedDate' => $selectedDate,
            'selectedDateOccurrences' => $selectedDate !== ''
                ? $this->service->byDate($selectedDate)
                : [],
            'recurrence' => $this->recurrenceService->dashboard(),
        ]);
    }

    private function isValidDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }

        $object = \DateTime::createFromFormat('Y-m-d', $date);

        return $object !== false && $object->format('Y-m-d') === $date;
    }
}
