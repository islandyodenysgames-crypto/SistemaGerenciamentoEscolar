<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Occurrence\Create;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use InvalidArgumentException;
use Throwable;

trait OccurrenceCreateActions
{
    /**
     * Exibe o formulário de ocorrência individual.
     */
    public function create(): void
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

        $studentId = (int) Request::get(
            'aluno',
            0
        );

        $classId = (int) Request::get(
            'turma',
            0
        );

        if ($studentId <= 0) {
            Session::set(
                'occurrence_error',
                'Aluno inválido para cadastro da ocorrência.'
            );

            Response::redirect(
                $this->studentsRedirect($classId)
            );

            return;
        }

        $student = $this->studentService->find(
            $studentId
        );

        if (!$student) {
            Session::set(
                'occurrence_error',
                'Aluno não encontrado.'
            );

            Response::redirect(
                $this->studentsRedirect($classId)
            );

            return;
        }

        $occurrenceError = Session::get(
            'occurrence_error'
        );

        $oldInput = Session::get(
            'occurrence_old_input',
            []
        );

        Session::remove('occurrence_error');
        Session::remove('occurrence_old_input');

        $this->view('pages/occurrences/create', [
            'title' => 'Nova Ocorrência - '
                . app_name(),

            'student' => $student,

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
        ]);
    }

    /**
     * Salva uma ocorrência individual.
     */
    public function store(): void
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

        $studentId = (int) Request::post(
            'student_id',
            0
        );

        $classId = (int) Request::post(
            'school_class_id',
            0
        );

        if ($studentId <= 0) {
            Session::set(
                'occurrence_error',
                'Aluno inválido.'
            );

            Response::redirect(
                $this->studentsRedirect($classId)
            );

            return;
        }

        $student = $this->studentService->find(
            $studentId
        );

        if (!$student) {
            Session::set(
                'occurrence_error',
                'Aluno não encontrado.'
            );

            Response::redirect(
                $this->studentsRedirect($classId)
            );

            return;
        }

        $data = $this->requestData();

        Session::set(
            'occurrence_old_input',
            $data
        );

        $this->validateSubjectAccess(
            (int) ($data['subject_id'] ?? 0)
        );

        try {
            $occurrenceId = $this->service->create([
                ...$data,

                'student_id' => $studentId,

                'created_by' =>
                    $this->currentUserId(),

                'created_by_name' =>
                    $this->currentUserName(),

                'created_by_role' =>
                    $this->currentUserRole(),
            ]);

            try {
                $this->attachmentService->uploadMany(
                    $occurrenceId,
                    $_FILES['attachments'] ?? [],
                    (int) ($this->currentUserId() ?? 0) ?: null,
                    (string) ($this->currentUserName() ?? '') ?: null
                );
            } catch (InvalidArgumentException $exception) {
                Session::set(
                    'occurrence_warning',
                    'A ocorrência foi cadastrada, mas os anexos não foram salvos: ' . $exception->getMessage()
                );
            }

            try {
                $userId = (int) ($this->currentUserId() ?? 0);

                if ($userId > 0) {
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
                            ),
                            occurrenceId: $occurrenceId
                        );

                    $this->notificationService
                        ->notifyFollowersOccurrenceCreated(
                            studentId: $studentId,
                            studentName: (string) ($student['name'] ?? $student['student_name'] ?? 'Aluno não identificado'),
                            occurrenceSeverity: $data['severity'] ?? 'LOW',
                            teacherName: (string) ($this->currentUserName() ?? 'Professor não identificado'),
                            occurrenceId: $occurrenceId,
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
                            studentName: (string) ($student['name'] ?? $student['student_name'] ?? 'Aluno não identificado'),
                            occurrenceType: (string) ($recurrence['criterion_label'] ?? 'Mesmo título ou mesma disciplina'),
                            total: (int) ($recurrence['total'] ?? 0),
                            periodDays: (int) ($recurrence['period_days'] ?? 60),
                            occurrenceId: $occurrenceId,
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
            } catch (Throwable) {
                /*
                 * A ocorrência já foi salva. Uma falha na
                 * notificação não deve desfazer o cadastro.
                 */
            }

            Session::remove(
                'occurrence_old_input'
            );

            Session::set(
                'occurrence_success',
                'Ocorrência cadastrada com sucesso.'
            );
        } catch (InvalidArgumentException $exception) {
            Session::set(
                'occurrence_error',
                $exception->getMessage()
            );

            Response::redirect(
                $this->individualCreateUrl(
                    $studentId,
                    $classId
                )
            );

            return;
        } catch (Throwable $exception) {
            Session::set(
                'occurrence_error',
                'Não foi possível cadastrar a ocorrência.'
            );

            Response::redirect(
                $this->individualCreateUrl(
                    $studentId,
                    $classId
                )
            );

            return;
        }

        Response::redirect(
            $this->studentProfileUrl(
                $studentId,
                $classId
            )
        );
    }
}