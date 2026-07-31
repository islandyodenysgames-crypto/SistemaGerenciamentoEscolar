<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Occurrence\Management;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use InvalidArgumentException;
use Throwable;

trait OccurrenceManageActions
{
    /**
     * Exibe o formulário de edição.
     *
     * Gestão pode editar qualquer ocorrência.
     * Professor pode editar somente ocorrência própria.
     */
    public function edit(): void
    {
        if (!$this->guard()) {
            return;
        }

        $id = (int) Request::get(
            'id',
            0
        );

        if ($id <= 0) {
            Session::set(
                'occurrence_error',
                'Ocorrência inválida.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $occurrence = $this->service->find($id);

        if (!$occurrence) {
            Session::set(
                'occurrence_error',
                'Ocorrência não encontrada.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $studentId = (int) (
            $occurrence['student_id'] ?? 0
        );

        if (
            !$this->canManageOccurrence(
                $occurrence
            )
        ) {
            Session::set(
                'occurrence_error',
                'Você não possui permissão para editar esta ocorrência.'
            );

            Response::redirect(
                $this->studentProfileUrl(
                    $studentId
                )
            );

            return;
        }

        $occurrenceError = Session::get('occurrence_error');
        $occurrenceWarning = Session::get('occurrence_warning');
        Session::remove('occurrence_error');
        Session::remove('occurrence_warning');

        $this->view('pages/occurrences/edit', [
            'title' => 'Editar Ocorrência - '
                . app_name(),

            'occurrence' => $occurrence,

            'occurrenceError' => $occurrenceError,

            'occurrenceWarning' => $occurrenceWarning,

            'subjects' => $this->availableSubjects(),

            'types' => $this->service->types(),

            'severities' => $this->service->severities(),
            
            'severityClasses' => $this->service->severityClasses(),
            
            'severityIcons' => $this->service->severityIcons(),

            'statuses' => $this->service
                ->statuses(),

            'titleSuggestions' => $this->service
                ->titleSuggestions(),
        ]);
    }

    /**
     * Atualiza uma ocorrência.
     *
     * A autoria original permanece inalterada.
     */
    public function update(): void
    {
        if (!$this->guard()) {
            return;
        }

        $id = (int) Request::post(
            'id',
            0
        );

        if ($id <= 0) {
            Session::set(
                'occurrence_error',
                'Ocorrência inválida.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $occurrence = $this->service->find($id);

        if (!$occurrence) {
            Session::set(
                'occurrence_error',
                'Ocorrência não encontrada.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $studentId = (int) (
            $occurrence['student_id'] ?? 0
        );

        if (
            !$this->canManageOccurrence(
                $occurrence
            )
        ) {
            Session::set(
                'occurrence_error',
                'Você não possui permissão para alterar esta ocorrência.'
            );

            Response::redirect(
                $this->studentProfileUrl(
                    $studentId
                )
            );

            return;
        }

        try {
            $data = $this->requestData();

                $this->validateSubjectAccess(
                    (int) (
                        $data['subject_id'] ?? 0
                    )
                );

                $this->service->update($id, [
                    ...$data,

                'student_id' => $studentId,
            ]);

            try {
                $this->attachmentService->uploadMany(
                    $id,
                    $_FILES['attachments'] ?? [],
                    (int) ($this->currentUserId() ?? 0) ?: null,
                    (string) ($this->currentUserName() ?? '') ?: null
                );
            } catch (InvalidArgumentException $exception) {
                Session::set(
                    'occurrence_warning',
                    'A ocorrência foi atualizada, mas os novos anexos não foram salvos: ' . $exception->getMessage()
                );
            }

            Session::set(
                'occurrence_success',
                'Ocorrência atualizada com sucesso.'
            );

        } catch (InvalidArgumentException $exception) {
            Session::set(
                'occurrence_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url(
                    'ocorrencias/editar?id='
                    . $id
                )
            );

            return;
        } catch (Throwable $exception) {
            Session::set(
                'occurrence_error',
                'Não foi possível atualizar a ocorrência.'
            );

            Response::redirect(
                base_url(
                    'ocorrencias/editar?id='
                    . $id
                )
            );

            return;
        }

        Response::redirect(
            $this->studentProfileUrl(
                $studentId
            )
        );
    }

    /**
     * Marca uma ocorrência como resolvida.
     *
     * Este método será substituído no próximo Sprint
     * pelo formulário de providências por etapas.
     */
    public function resolve(): void
    {
        if (!$this->guard()) {
            return;
        }

        /*
         * Enquanto as providências por etapas ainda
         * não foram implementadas, mantemos a regra
         * administrativa atual.
         */
        if (
            !$this->authorize(
                Permissions::OCCURRENCES_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::post(
            'id',
            0
        );

        if ($id <= 0) {
            Session::set(
                'occurrence_error',
                'Ocorrência inválida.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $occurrence = $this->service->find($id);

        if (!$occurrence) {
            Session::set(
                'occurrence_error',
                'Ocorrência não encontrada.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $studentId = (int) (
            $occurrence['student_id'] ?? 0
        );

        if ($this->service->resolve($id)) {
            Session::set(
                'occurrence_success',
                'Ocorrência marcada como resolvida.'
            );
        } else {
            Session::set(
                'occurrence_error',
                'Não foi possível resolver a ocorrência.'
            );
        }

        Response::redirect(
            $this->studentProfileUrl(
                $studentId
            )
        );
    }

    /**
     * Reabre uma ocorrência resolvida.
     */
    public function reopen(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::OCCURRENCES_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::post(
            'id',
            0
        );

        if ($id <= 0) {
            Session::set(
                'occurrence_error',
                'Ocorrência inválida.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $occurrence = $this->service->find($id);

        if (!$occurrence) {
            Session::set(
                'occurrence_error',
                'Ocorrência não encontrada.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $studentId = (int) (
            $occurrence['student_id'] ?? 0
        );

        if ($this->service->reopen($id)) {
            Session::set(
                'occurrence_success',
                'Ocorrência reaberta com sucesso.'
            );
        } else {
            Session::set(
                'occurrence_error',
                'Não foi possível reabrir a ocorrência.'
            );
        }

        Response::redirect(
            $this->studentProfileUrl(
                $studentId
            )
        );
    }

    /**
     * Exclui uma ocorrência.
     *
     * Gestão pode excluir qualquer ocorrência.
     * Professor pode excluir somente ocorrência própria.
     */
    public function delete(): void
    {
        if (!$this->guard()) {
            return;
        }

        $id = (int) Request::post(
            'id',
            0
        );

        if ($id <= 0) {
            Session::set(
                'occurrence_error',
                'Ocorrência inválida.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $occurrence = $this->service->find($id);

        if (!$occurrence) {
            Session::set(
                'occurrence_error',
                'Ocorrência não encontrada.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $studentId = (int) (
            $occurrence['student_id'] ?? 0
        );

        if (
            !$this->canManageOccurrence(
                $occurrence
            )
        ) {
            Session::set(
                'occurrence_error',
                'Você não possui permissão para excluir esta ocorrência.'
            );

            Response::redirect(
                $this->studentProfileUrl(
                    $studentId
                )
            );

            return;
        }

        $this->attachmentService->removeAll($id);

        if ($this->service->delete($id)) {
            Session::set(
                'occurrence_success',
                'Ocorrência excluída com sucesso.'
            );
        } else {
            Session::set(
                'occurrence_error',
                'Não foi possível excluir a ocorrência.'
            );
        }

        Response::redirect(
            $this->studentProfileUrl(
                $studentId
            )
        );
    }

    public function deleteAttachment(): void
    {
        if (!$this->guard()) {
            return;
        }

        $attachmentId = (int) Request::post('attachment_id', 0);
        $attachment = $this->attachmentService->find($attachmentId);

        if (!$attachment) {
            Session::set('occurrence_error', 'O anexo informado não foi encontrado.');
            Response::redirect(base_url('ocorrencias'));
            return;
        }

        // O vínculo é obtido do próprio registro, evitando depender de um
        // occurrence_id enviado pela tela e impedindo divergências entre fluxos.
        $occurrenceId = (int) ($attachment['occurrence_id'] ?? 0);
        $occurrence = $this->service->find($occurrenceId);

        if (!$occurrence || !$this->canManageOccurrence($occurrence)) {
            Session::set('occurrence_error', 'Você não possui permissão para remover este anexo.');
            Response::redirect(base_url('ocorrencias'));
            return;
        }

        $studentId = (int) ($occurrence['student_id'] ?? 0);
        $defaultReturn = base_url('ocorrencias/editar?id=' . $occurrenceId . '#occurrenceAttachmentsSection');
        $profileReturn = $studentId > 0
            ? $this->studentProfileUrl($studentId)
            : base_url('ocorrencias');

        $returnTo = trim((string) Request::post('return_to', ''));
        $allowedPrefixes = [
            base_url('ocorrencias/editar'),
            base_url('alunos/perfil'),
            base_url('ocorrencias'),
        ];

        $isAllowedReturn = false;
        foreach ($allowedPrefixes as $prefix) {
            if ($returnTo !== '' && str_starts_with($returnTo, $prefix)) {
                $isAllowedReturn = true;
                break;
            }
        }

        if (!$isAllowedReturn) {
            $returnTo = Request::post('source', '') === 'student_profile'
                ? $profileReturn
                : $defaultReturn;
        }

        if ($this->attachmentService->remove($attachmentId, $occurrenceId)) {
            Session::set('occurrence_success', 'Anexo removido com sucesso.');
        } else {
            Session::set('occurrence_error', 'Não foi possível remover o anexo.');
        }

        Response::redirect($returnTo);
    }

}