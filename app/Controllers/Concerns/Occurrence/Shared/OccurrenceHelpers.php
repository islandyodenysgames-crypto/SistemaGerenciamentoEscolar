<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Occurrence\Shared;

use App\Auth\Permissions;
use App\Core\Authorization;
use App\Core\Request;
use App\Core\Session;

trait OccurrenceHelpers
{
    /**
     * Verifica se o usuário atual pode editar
     * ou excluir determinada ocorrência.
     *
     * Usuários com OCCURRENCES_MANAGE podem administrar
     * qualquer registro.
     *
     * Professor pode administrar apenas ocorrência
     * criada por ele mesmo.
     */
    private function canManageOccurrence(
        array $occurrence
    ): bool {
        if (
            Authorization::can(
                Permissions::OCCURRENCES_MANAGE
            )
        ) {
            return true;
        }

        /*
         * Para ser considerado proprietário, o usuário
         * também precisa possuir permissão para criar
         * ocorrências.
         */
        if (
            !Authorization::can(
                Permissions::OCCURRENCES_CREATE
            )
        ) {
            return false;
        }

        $currentUserId = $this->currentUserId();

        $createdBy = (int) (
            $occurrence['created_by'] ?? 0
        );

        return $currentUserId !== null
            && $currentUserId > 0
            && $createdBy > 0
            && $currentUserId === $createdBy;
    }

    /**
     * Dados compartilhados pelos formulários
     * de ocorrência individual, múltipla e edição.
     */
    private function requestData(): array
    {
        return [
            'occurrence_date' => trim(
                (string) Request::post(
                    'occurrence_date',
                    ''
                )
            ),

            'subject_id' => (int) Request::post(
                'subject_id',
                0
            ),

            'type' => trim(
                (string) Request::post(
                    'type',
                    'OBSERVATION'
                )
            ),

            /*
             * Gravidade da ocorrência:
             *
             * LOW
             * MEDIUM
             * HIGH
             * CRITICAL
             */
            'severity' => trim(
                (string) Request::post(
                    'severity',
                    'LOW'
                )
            ),

            'title' => trim(
                (string) Request::post(
                    'title',
                    ''
                )
            ),

            'description' => trim(
                (string) Request::post(
                    'description',
                    ''
                )
            ),

            'actions_taken' => trim(
                (string) Request::post(
                    'actions_taken',
                    ''
                )
            ),

            'status' => trim(
                (string) Request::post(
                    'status',
                    'OPEN'
                )
            ),
        ];
    }

    /**
     * ID do usuário atualmente autenticado.
     */
    private function currentUserId(): ?int
    {
        $user = Session::get('user');

        if (is_array($user)) {
            $id = (int) (
                $user['id'] ?? 0
            );

            return $id > 0
                ? $id
                : null;
        }

        if (is_object($user)) {
            $id = (int) (
                $user->id ?? 0
            );

            return $id > 0
                ? $id
                : null;
        }

        return null;
    }

    /**
     * Nome do usuário atualmente autenticado.
     */
    private function currentUserName(): string
    {
        $user = Session::get('user');

        if (is_array($user)) {
            return trim(
                (string) (
                    $user['name'] ?? ''
                )
            );
        }

        if (is_object($user)) {
            return trim(
                (string) (
                    $user->name ?? ''
                )
            );
        }

        return '';
    }

    /**
     * Código interno do perfil do usuário.
     *
     * Exemplos:
     * ADMIN
     * DIRECTION
     * COORDINATION
     * SECRETARY
     * TEACHER
     */
    private function currentUserRoleCode(): string
    {
        $user = Session::get('user');

        if (is_array($user)) {
            return strtoupper(
                trim(
                    (string) (
                        $user['role'] ?? ''
                    )
                )
            );
        }

        if (is_object($user)) {
            return strtoupper(
                trim(
                    (string) (
                        $user->role ?? ''
                    )
                )
            );
        }

        return '';
    }

    /**
     * Nome amigável do perfil do usuário.
     *
     * Exemplos:
     * Administrador
     * Direção
     * Coordenação
     * Secretaria
     * Professor
     */
    private function currentUserRole(): string
    {
        $user = Session::get('user');

        if (is_array($user)) {
            return trim(
                (string) (
                    $user['role_label']
                    ?? $user['role']
                    ?? ''
                )
            );
        }

        if (is_object($user)) {
            return trim(
                (string) (
                    $user->role_label
                    ?? $user->role
                    ?? ''
                )
            );
        }

        return '';
    }

    /**
     * Retorna somente estudantes válidos.
     *
     * Quando a turma é informada, também garante
     * que todos estejam atualmente vinculados à turma.
     */
    private function selectedStudents(
        array $studentIds,
        int $classId
    ): array {
        $normalizedIds = [];

        foreach ($studentIds as $studentId) {
            $studentId = (int) $studentId;

            if ($studentId > 0) {
                $normalizedIds[$studentId] =
                    $studentId;
            }
        }

        if (empty($normalizedIds)) {
            return [];
        }

        if ($classId > 0) {
            $classStudents = $this->studentService
                ->byClass($classId);

            $available = [];

            foreach ($classStudents as $student) {
                $studentId = (int) (
                    $student['id'] ?? 0
                );

                if (
                    $studentId > 0
                    && isset(
                        $normalizedIds[$studentId]
                    )
                ) {
                    $available[$studentId] =
                        $student;
                }
            }

            return array_values($available);
        }

        $students = [];

        foreach ($normalizedIds as $studentId) {
            $student = $this->studentService
                ->find($studentId);

            if ($student) {
                $students[] = $student;
            }
        }

        return $students;
    }

    /**
     * Endereço da página da turma ou
     * da página geral de alunos.
     */
    private function studentsRedirect(
        int $classId = 0
    ): string {
        if ($classId > 0) {
            return base_url(
                'alunos/turma?id=' . $classId
            );
        }

        return base_url('alunos');
    }

    /**
     * Endereço do perfil do aluno,
     * direcionando para a seção de ocorrências.
     */
    private function studentProfileUrl(
        int $studentId,
        int $classId = 0
    ): string {
        $url = base_url(
            'alunos/perfil?id='
            . $studentId
        );

        if ($classId > 0) {
            $url .= '&turma=' . $classId;
        }

        return $url . '#studentOccurrences';
    }

    /**
     * Endereço do formulário de ocorrência individual.
     */
    private function individualCreateUrl(
        int $studentId,
        int $classId = 0
    ): string {
        $url = base_url(
            'ocorrencias/nova?aluno='
            . $studentId
        );

        if ($classId > 0) {
            $url .= '&turma=' . $classId;
        }

        return $url;
    }

    /**
     * Endereço do formulário de ocorrência múltipla.
     */
    private function multipleCreateUrl(
        int $classId = 0
    ): string {
        $url = base_url(
            'ocorrencias/multiplas/nova'
        );

        if ($classId > 0) {
            $url .= '?turma=' . $classId;
        }

        return $url;
    }

   /**
     * Retorna as disciplinas disponíveis
     * para o usuário autenticado.
     */
    private function availableSubjects(): array
    {
        $userId = $this->currentUserId();

        if ($userId === null || $userId <= 0) {
            return [];
        }

        return $this->subjectService
            ->availableForUser(
                $userId,
                $this->canAccessAllSubjects()
            );
    }

    /**
     * Informa se o usuário pode acessar
     * todas as disciplinas.
     */
    private function canAccessAllSubjects(): bool
    {
        return Authorization::can(
            Permissions::SUBJECTS_MANAGE
        );
    }
    
    /**
     * Verifica se a disciplina está disponível
     * para o usuário autenticado.
     */
    private function userCanUseSubject(
        int $subjectId
    ): bool {
        if ($subjectId <= 0) {
            return false;
        }

        foreach (
            $this->availableSubjects()
            as $subject
        ) {
            $availableSubjectId = (int) (
                $subject['id'] ?? 0
            );

            if ($availableSubjectId === $subjectId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Valida se o usuário autenticado pode
     * utilizar a disciplina informada.
     */
    private function validateSubjectAccess(
        int $subjectId
    ): void {
        if ($subjectId <= 0) {
            throw new \InvalidArgumentException(
                'Selecione uma disciplina.'
            );
        }

        if (!$this->userCanUseSubject($subjectId)) {
            throw new \InvalidArgumentException(
                'Você não possui acesso à disciplina selecionada.'
            );
        }
    }
}