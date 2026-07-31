<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Occurrence\Multiple;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use InvalidArgumentException;
use Throwable;

trait OccurrenceMultipleActions
{
    /**
     * Exibe o formulário de ocorrência múltipla.
     */
    public function createMultiple(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::OCCURRENCES_CREATE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $classId = (int) Request::post(
            'school_class_id',
            Request::get('turma', 0)
        );

        $studentIds = Request::post(
            'student_ids',
            []
        );

        /*
         * Quando o formulário for reaberto após
         * um erro, recupera os alunos da sessão.
         */
        if (
            !is_array($studentIds)
            || empty($studentIds)
        ) {
            $studentIds = Session::get(
                'multiple_occurrence_student_ids',
                []
            );
        }

        if (!is_array($studentIds)) {
            $studentIds = [];
        }

        $students = $this->selectedStudents(
            $studentIds,
            $classId
        );

        if (empty($students)) {
            Session::set(
                'student_error',
                'Selecione pelo menos um aluno para registrar a ocorrência.'
            );

            Response::redirect(
                $this->studentsRedirect($classId)
            );

            return;
        }

        $selectedIds = array_map(
            static fn (array $student): int =>
                (int) ($student['id'] ?? 0),
            $students
        );

        Session::set(
            'multiple_occurrence_student_ids',
            $selectedIds
        );

        Session::set(
            'multiple_occurrence_class_id',
            $classId
        );

        $occurrenceError = Session::get(
            'occurrence_error'
        );

        $oldInput = Session::get(
            'multiple_occurrence_old_input',
            []
        );

        Session::remove('occurrence_error');

        Session::remove(
            'multiple_occurrence_old_input'
        );

        $this->view(
            'pages/occurrences/create-multiple',
            [
                'title' => 'Ocorrência Múltipla - '
                    . app_name(),

                'students' => $students,

                'studentIds' => $selectedIds,

                'classId' => $classId,

                'subjects' => $this->availableSubjects(),

                'types' => $this->service->types(),

                'severities' => $this->service->severities(),
            
                'severityClasses' => $this->service->severityClasses(),
            
                'severityIcons' => $this->service->severityIcons(),

                'statuses' => $this->service
                    ->statuses(),

                'titleSuggestions' => $this->service
                    ->titleSuggestions(),

                'occurrenceError' =>
                    $occurrenceError,

                'oldInput' => is_array($oldInput)
                    ? $oldInput
                    : [],
            ]
        );
    }

    /**
     * Salva a mesma ocorrência para
     * todos os alunos selecionados.
     */
    public function storeMultiple(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::OCCURRENCES_CREATE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $classId = (int) Request::post(
            'school_class_id',
            0
        );

        $studentIds = Request::post(
            'student_ids',
            []
        );

        if (
            !is_array($studentIds)
            || empty($studentIds)
        ) {
            $studentIds = Session::get(
                'multiple_occurrence_student_ids',
                []
            );
        }

        if (!is_array($studentIds)) {
            $studentIds = [];
        }

        $students = $this->selectedStudents(
            $studentIds,
            $classId
        );

        if (empty($students)) {
            Session::set(
                'occurrence_error',
                'Nenhum aluno válido foi selecionado.'
            );

            Response::redirect(
                $this->studentsRedirect($classId)
            );

            return;
        }

        $validStudentIds = array_map(
            static fn (array $student): int =>
                (int) ($student['id'] ?? 0),
            $students
        );

        $data = $this->requestData();

        Session::set(
            'multiple_occurrence_student_ids',
            $validStudentIds
        );

        Session::set(
            'multiple_occurrence_class_id',
            $classId
        );

        Session::set(
            'multiple_occurrence_old_input',
            $data
        );

        try {
            $this->validateSubjectAccess(
                (int) (
                    $data['subject_id'] ?? 0
                )
            );

            $created = $this->service
                ->createMultiple(
                    $validStudentIds,
                    [
                        ...$data,

                        'created_by' =>
                            $this->currentUserId(),

                        'created_by_name' =>
                            $this->currentUserName(),

                        'created_by_role' =>
                            $this->currentUserRole(),
                    ]
                );

            try {
                $userId = (int) ($this->currentUserId() ?? 0);

                if ($userId > 0) {
                    foreach ($students as $student) {
                        $studentId = (int) (
                            $student['id'] ?? 0
                        );

                        if ($studentId <= 0) {
                            continue;
                        }

                        $this->notificationService
                            ->notifyOccurrenceCreated(
                                userId: $userId,
                                studentId: $studentId,
                                studentName: (string) (
                                    $student['name']
                                    ?? $student['student_name']
                                    ?? 'Aluno não identificado'
                                ),
                                occurrenceSeverity: $data['severity']
                                    ?? 'LOW',
                                teacherName: (string) (
                                    $this->currentUserName()
                                    ?? 'Professor não identificado'
                                )
                            );

                        $studentName = (string) (
                            $student['name']
                            ?? $student['student_name']
                            ?? 'Aluno não identificado'
                        );

                        $this->notificationService
                            ->notifyFollowersOccurrenceCreated(
                                studentId: $studentId,
                                studentName: $studentName,
                                occurrenceSeverity: $data['severity'] ?? 'LOW',
                                teacherName: (string) ($this->currentUserName() ?? 'Professor não identificado'),
                                occurrenceId: null,
                                exceptUserId: $userId
                            );

                        $recurrence = (new \App\Services\RecurrenceIntelligenceService())
                            ->analyzeOccurrence(
                                $studentId,
                                (string) ($data['title'] ?? ''),
                                (int) ($data['subject_id'] ?? 0)
                            );

                        if (!empty($recurrence['is_recurrent'])) {
                            $this->notificationService->notifyRecurrenceFollowers(
                                studentId: $studentId,
                                studentName: $studentName,
                                occurrenceType: (string) ($recurrence['criterion_label'] ?? 'Mesmo título ou mesma disciplina'),
                                total: (int) ($recurrence['total'] ?? 0),
                                periodDays: (int) ($recurrence['period_days'] ?? 60),
                                occurrenceId: null,
                                exceptUserId: $userId
                            );
                        }

                        $this->notificationService
                            ->generateForStudent(
                                $userId,
                                $studentId
                            );

                        $this->notificationService
                            ->notifyCriticalClassForStudent($studentId, $userId);
                    }
                }
            } catch (Throwable) {
                /*
                 * As ocorrências já foram salvas. Uma falha
                 * de notificação não desfaz a operação.
                 */
            }

            Session::remove(
                'multiple_occurrence_student_ids'
            );

            Session::remove(
                'multiple_occurrence_class_id'
            );

            Session::remove(
                'multiple_occurrence_old_input'
            );

            Session::set(
                'student_success',
                $created
                . ' ocorrência(s) registrada(s) com sucesso.'
            );

            Response::redirect(
                $this->studentsRedirect($classId)
            );
        } catch (InvalidArgumentException $exception) {
            Session::set(
                'occurrence_error',
                $exception->getMessage()
            );

            Response::redirect(
                $this->multipleCreateUrl(
                    $classId
                )
            );

            return;
        } catch (Throwable $exception) {
            Session::set(
                'occurrence_error',
                'Não foi possível registrar a ocorrência para os alunos selecionados.'
            );

            Response::redirect(
                $this->multipleCreateUrl(
                    $classId
                )
            );

            return;
        }
    }
}