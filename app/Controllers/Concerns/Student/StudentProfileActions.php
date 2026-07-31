<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Student;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

trait StudentProfileActions
{
    public function profile(): void
    {
        $this->guard();

        if (
            !$this->authorize(
                Permissions::STUDENTS_VIEW,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::get('id');

        if ($id <= 0) {
            Session::set(
                'student_error',
                'Aluno inválido.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $student = $this->service->find($id);

        if (!$student) {
            Session::set(
                'student_error',
                'Aluno não encontrado.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $year = (int) Request::get(
            'ano',
            date('Y')
        );

        $month = (int) Request::get(
            'mes',
            date('n')
        );

        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        if (
            $year < 2000
            || $year > ((int) date('Y') + 1)
        ) {
            $year = (int) date('Y');
        }

        $chartMode = trim(
            (string) Request::get(
                'grafico',
                'diario'
            )
        );

        if (
            !in_array(
                $chartMode,
                ['diario', 'mensal'],
                true
            )
        ) {
            $chartMode = 'diario';
        }

        $attendanceEvolution = $chartMode === 'mensal'
            ? $this->service
                ->attendanceEvolutionMonthly($id)
            : $this->service
                ->attendanceEvolutionDaily($id);

        $occurrenceSuccess = Session::get(
            'occurrence_success'
        );

        $occurrenceError = Session::get(
            'occurrence_error'
        );

        $occurrenceWarning = Session::get(
            'occurrence_warning'
        );

        $monitoringSuccess = Session::get('monitoring_success');
        $monitoringError = Session::get('monitoring_error');
        Session::remove('occurrence_success');
        Session::remove('occurrence_error');
        Session::remove('occurrence_warning');
        Session::remove('monitoring_success');
        Session::remove('monitoring_error');

        $currentUserId = (int) (((array) Session::get('user', []))['id'] ?? 0);
        $selectedMonitoringId = (int) Request::get('case_id');
        $studentMonitoring = $this->monitoringService
            ->contextForStudent($id, $currentUserId, $selectedMonitoringId > 0 ? $selectedMonitoringId : null);
        if ((string) Request::get('create_case', '') === '1') {
            $studentMonitoring['intelligence_prefill'] = $this->intelligenceService
                ->casePrefillForAlert((string) Request::get('alert_type', 'students_attention'));
        }
        $studentIntelligence = $this->intelligenceService
            ->studentDashboard($id, $studentMonitoring);

        $this->view('pages/students/profile', [
            'title' => 'Perfil do Aluno - ' . app_name(),

            'student' => $student,

            'statistics' => $this->service
                ->statistics($id),

            'attendanceHistory' => $this->service
                ->attendanceHistory($id),

            'attendanceEvolution' => $attendanceEvolution,

            'chartMode' => $chartMode,

            'calendar' => $this->service->calendar(
                $id,
                $year,
                $month
            ),

            'calendarYear' => $year,

            'calendarMonth' => $month,

            'occurrences' => $this->occurrenceService
                ->byStudent($id),

            'occurrenceActions' => 
                 $this->occurrenceActionService
                     ->groupedByStudent($id),

            'occurrenceActionTypes' =>
                 $this->occurrenceActionService
                     ->actionTypes(),

            'occurrenceActionTypeIcons' =>
                 $this->occurrenceActionService
                     ->actionTypeIcons(),

            'totalOccurrences' => $this->occurrenceService
                ->countByStudent($id),

            'openOccurrences' => $this->occurrenceService
                ->countOpenByStudent($id),

            'resolvedOccurrences' => $this->occurrenceService
                ->countResolvedByStudent($id),

            'occurrenceTypes' => $this->occurrenceService
                ->types(),

            'occurrenceTypeCounts' =>
                 $this->occurrenceService
                     ->countByTypeForStudent($id),

            'occurrenceSeverities' =>
                 $this->occurrenceService
                     ->severities(),
            
            'occurrenceSeverityClasses' =>
                 $this->occurrenceService
                     ->severityClasses(),

            'occurrenceSeverityIcons' =>
                 $this->occurrenceService
                     ->severityIcons(),

            'occurrenceSeverityCounts' =>
                 $this->occurrenceService
                     ->countBySeverityForStudent($id),

            'occurrenceSuccess' => $occurrenceSuccess,

            'occurrenceError' => $occurrenceError,

            'occurrenceWarning' => $occurrenceWarning,

            'studentIntelligence' => $studentIntelligence,

            'studentMonitoring' => $studentMonitoring,

            'studentRecurrence' => $this->recurrenceService
                ->studentDashboard($id),

            'monitoringSuccess' => $monitoringSuccess,
            'monitoringError' => $monitoringError,
            'isStudentFavorite' => $this->favoriteService
                ->isFavorite($currentUserId, 'student', $id),
        ]);
    }

    public function report(): void
    {
        $this->guard();

        if (
            !$this->authorize(
                Permissions::STUDENTS_VIEW,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::get('id');

        if ($id <= 0) {
            Session::set(
                'student_error',
                'Aluno inválido para geração do relatório.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $student = $this->service->find($id);

        if (!$student) {
            Session::set(
                'student_error',
                'Aluno não encontrado.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $year = (int) Request::get(
            'ano',
            date('Y')
        );

        if (
            $year < 2000
            || $year > ((int) date('Y') + 1)
        ) {
            $year = (int) date('Y');
        }

        $this->view('pages/students/report', [
            'title' => 'Relatório do Aluno - ' . app_name(),

            'student' => $student,

            'statistics' => $this->service
                ->statistics($id),

            'attendanceHistory' => $this->service
                ->attendanceHistory($id),

            'attendanceEvolution' => $this->service
                ->attendanceEvolutionMonthly($id),

            'occurrences' => $this->occurrenceService
                ->byStudent($id),

            'occurrenceTypes' => $this->occurrenceService
                ->types(),

            'reportYear' => $year,
        ]);
    }
}