<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Subject;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use InvalidArgumentException;
use Throwable;

trait SubjectCrudActions
{
    /**
     * Lista todas as disciplinas.
     */
    public function index(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::SUBJECTS_VIEW,
                base_url()
            )
        ) {
            return;
        }

        $this->view(
            'pages/subjects/index',
            [
                'title' =>
                    'Disciplinas - '
                    . app_name(),

                'subjects' =>
                    $this->service->all(),
            ]
        );
    }

    /**
     * Exibe o formulário de cadastro.
     */
    public function create(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::SUBJECTS_MANAGE,
                base_url('disciplinas')
            )
        ) {
            return;
        }

        $subjectError = Session::get(
            'subject_error'
        );

        $oldInput = Session::get(
            'subject_old_input',
            []
        );

        Session::remove(
            'subject_error'
        );

        Session::remove(
            'subject_old_input'
        );

        $this->view(
            'pages/subjects/create',
            [
                'title' =>
                    'Nova Disciplina - '
                    . app_name(),

                'subjectError' =>
                    $subjectError,

                'oldInput' =>
                    is_array($oldInput)
                        ? $oldInput
                        : [],
            ]
        );
    }

    /**
     * Salva uma nova disciplina.
     */
    public function store(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::SUBJECTS_MANAGE,
                base_url('disciplinas')
            )
        ) {
            return;
        }

        $data = $this->subjectRequestData();

        Session::set(
            'subject_old_input',
            $data
        );

        try {
            $this->service->create($data);

            Session::remove(
                'subject_old_input'
            );

            Session::set(
                'subject_success',
                'Disciplina cadastrada com sucesso.'
            );

            Response::redirect(
                base_url('disciplinas')
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            Session::set(
                'subject_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url('disciplinas/nova')
            );
        } catch (Throwable $exception) {
            Session::set(
                'subject_error',
                'Não foi possível cadastrar a disciplina.'
            );

            Response::redirect(
                base_url('disciplinas/nova')
            );
        }
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::SUBJECTS_MANAGE,
                base_url('disciplinas')
            )
        ) {
            return;
        }

        $id = (int) Request::get(
            'id',
            0
        );

        $subject = $this->service->find(
            $id
        );

        if (!$subject) {
            Session::set(
                'subject_error',
                'Disciplina não encontrada.'
            );

            Response::redirect(
                base_url('disciplinas')
            );

            return;
        }

        $subjectError = Session::get(
            'subject_error'
        );

        $oldInput = Session::get(
            'subject_old_input',
            []
        );

        Session::remove(
            'subject_error'
        );

        Session::remove(
            'subject_old_input'
        );

        if (
            is_array($oldInput)
            && !empty($oldInput)
        ) {
            $subject = array_merge(
                $subject,
                $oldInput
            );
        }

        $this->view(
            'pages/subjects/edit',
            [
                'title' =>
                    'Editar Disciplina - '
                    . app_name(),

                'subject' =>
                    $subject,

                'subjectError' =>
                    $subjectError,
            ]
        );
    }

    /**
     * Atualiza uma disciplina.
     */
    public function update(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::SUBJECTS_MANAGE,
                base_url('disciplinas')
            )
        ) {
            return;
        }

        $id = (int) Request::post(
            'id',
            0
        );

        $subject = $this->service->find(
            $id
        );

        if (!$subject) {
            Session::set(
                'subject_error',
                'Disciplina não encontrada.'
            );

            Response::redirect(
                base_url('disciplinas')
            );

            return;
        }

        $data = $this->subjectRequestData();

        Session::set(
            'subject_old_input',
            $data
        );

        try {
            $this->service->update(
                $id,
                $data
            );

            Session::remove(
                'subject_old_input'
            );

            Session::set(
                'subject_success',
                'Disciplina atualizada com sucesso.'
            );

            Response::redirect(
                base_url('disciplinas')
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            Session::set(
                'subject_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url(
                    'disciplinas/editar?id='
                    . $id
                )
            );
        } catch (Throwable $exception) {
            Session::set(
                'subject_error',
                'Não foi possível atualizar a disciplina.'
            );

            Response::redirect(
                base_url(
                    'disciplinas/editar?id='
                    . $id
                )
            );
        }
    }

    /**
     * Ativa uma disciplina.
     */
    public function activate(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::SUBJECTS_MANAGE,
                base_url('disciplinas')
            )
        ) {
            return;
        }

        $id = (int) Request::post(
            'id',
            0
        );

        try {
            $this->service->activate($id);

            Session::set(
                'subject_success',
                'Disciplina ativada com sucesso.'
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            Session::set(
                'subject_error',
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            Session::set(
                'subject_error',
                'Não foi possível ativar a disciplina.'
            );
        }

        Response::redirect(
            base_url('disciplinas')
        );
    }

    /**
     * Inativa uma disciplina.
     */
    public function deactivate(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::SUBJECTS_MANAGE,
                base_url('disciplinas')
            )
        ) {
            return;
        }

        $id = (int) Request::post(
            'id',
            0
        );

        try {
            $this->service->deactivate($id);

            Session::set(
                'subject_success',
                'Disciplina inativada com sucesso.'
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            Session::set(
                'subject_error',
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            Session::set(
                'subject_error',
                'Não foi possível inativar a disciplina.'
            );
        }

        Response::redirect(
            base_url('disciplinas')
        );
    }

    /**
     * Exclui uma disciplina sem vínculos.
     */
    public function delete(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::SUBJECTS_MANAGE,
                base_url('disciplinas')
            )
        ) {
            return;
        }

        $id = (int) Request::post(
            'id',
            0
        );

        try {
            $this->service->delete($id);

            Session::set(
                'subject_success',
                'Disciplina excluída com sucesso.'
            );
        } catch (
            InvalidArgumentException $exception
        ) {
            Session::set(
                'subject_error',
                $exception->getMessage()
            );
        } catch (Throwable $exception) {
            Session::set(
                'subject_error',
                'Não foi possível excluir a disciplina.'
            );
        }

        Response::redirect(
            base_url('disciplinas')
        );
    }

    /**
     * Dados recebidos pelos formulários
     * de cadastro e edição.
     */
    private function subjectRequestData(): array
    {
        return [
            'name' => trim(
                (string) Request::post(
                    'name',
                    ''
                )
            ),

            'code' => trim(
                (string) Request::post(
                    'code',
                    ''
                )
            ),

            'description' => trim(
                (string) Request::post(
                    'description',
                    ''
                )
            ),

            'active' => (int) Request::post(
                'active',
                0
            ),
        ];
    }
}