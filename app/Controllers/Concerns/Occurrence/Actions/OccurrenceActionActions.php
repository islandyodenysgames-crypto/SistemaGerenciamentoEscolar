<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Occurrence\Actions;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use InvalidArgumentException;
use Throwable;

trait OccurrenceActionActions
{
    /**
     * Exibe o formulário para registrar
     * uma nova providência.
     */
    public function actionCreate(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::OCCURRENCES_VIEW,
                base_url('alunos')
            )
        ) {
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

        $actionError = Session::get(
            'occurrence_action_error'
        );

        $oldInput = Session::get(
            'occurrence_action_old_input',
            []
        );

        Session::remove(
            'occurrence_action_error'
        );

        Session::remove(
            'occurrence_action_old_input'
        );

        $this->view(
            'pages/occurrences/action',
            [
                'title' => 'Nova Providência - '
                    . app_name(),

                'occurrence' => $occurrence,

                'statuses' => $this->actionService
                    ->statuses(),

                'actionTypes' => $this->actionService
                    ->actionTypes(),

                'actionTypeIcons' => $this->actionService
                    ->actionTypeIcons(),

                'actionError' => $actionError,

                'oldInput' => is_array($oldInput)
                    ? $oldInput
                    : [],
            ]
        );
    }

    /**
     * Salva uma providência e atualiza
     * a situação da ocorrência.
     */
    public function actionStore(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::OCCURRENCES_VIEW,
                base_url('alunos')
            )
        ) {
            return;
        }

        $occurrenceId = (int) Request::post(
            'occurrence_id',
            0
        );

        $occurrence = $this->service->find(
            $occurrenceId
        );

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

        $data = [
            'occurrence_id' =>
                $occurrenceId,

            'action_type' => trim(
                (string) Request::post(
                    'action_type',
                    'OTHER'
                )
            ),

            'description' => trim(
                (string) Request::post(
                    'description',
                    ''
                )
            ),

            'status_after' => trim(
                (string) Request::post(
                    'status_after',
                    'OPEN'
                )
            ),
        ];

        Session::set(
            'occurrence_action_old_input',
            $data
        );

        try {
            $actionId = $this->actionService->create([
                ...$data,

                'created_by' =>
                    $this->currentUserId(),

                'created_by_name' =>
                    $this->currentUserName(),

                'created_by_role' =>
                    $this->currentUserRole(),
            ]);

            if (isset($_FILES['attachments'])) {
                $this->actionAttachmentService->uploadMany(
                    $actionId,
                    $_FILES['attachments'],
                    $this->currentUserId(),
                    $this->currentUserName()
                );
            }

            Session::remove(
                'occurrence_action_old_input'
            );

            Session::set(
                'occurrence_success',
                $data['status_after'] === 'RESOLVED'
                    ? 'Providência registrada e ocorrência resolvida com sucesso.'
                    : 'Providência registrada com sucesso.'
            );

            Response::redirect(
                $this->studentProfileUrl(
                    $studentId
                )
            );
        } catch (InvalidArgumentException $exception) {
            Session::set(
                'occurrence_action_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url(
                    'ocorrencias/providencia?id='
                    . $occurrenceId
                )
            );
        } catch (Throwable $exception) {
            Session::set(
                'occurrence_action_error',
                'Não foi possível registrar a providência.'
            );

            Response::redirect(
                base_url(
                    'ocorrencias/providencia?id='
                    . $occurrenceId
                )
            );
        }
    }

    public function actionAttachmentDelete(): void
    {
        if (!$this->guard()) return;
        if (!$this->authorize(Permissions::OCCURRENCES_MANAGE, base_url('alunos'))) return;

        $attachmentId=(int)Request::post('attachment_id',0);
        $studentId=(int)Request::post('student_id',0);
        try {
            if (!$this->actionAttachmentService->remove($attachmentId)) {
                throw new InvalidArgumentException('Anexo não encontrado.');
            }
            Session::set('occurrence_success','Anexo da providência removido com sucesso.');
        } catch (Throwable $exception) {
            Session::set('occurrence_error',$exception->getMessage());
        }
        Response::redirect($this->studentProfileUrl($studentId));
    }
}
