<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Student;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use InvalidArgumentException;
use Throwable;

trait StudentCrudActions
{
    public function edit(): void
    {
        $this->guard();

        if (
            !$this->authorize(
                Permissions::STUDENTS_MANAGE,
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

        $currentEnrollment = $this->service
            ->activeEnrollment($id);

        $this->view('pages/students/edit', [
            'title' => 'Editar Aluno - ' . app_name(),

            'student' => $student,

            'classes' => $this->classService->all(),

            'currentEnrollment' =>
                $currentEnrollment,

            'enrollmentHistory' => $this->service
                ->enrollmentHistory($id),
        ]);
    }

    public function update(): void
    {
        $this->guard();

        if (
            !$this->authorize(
                Permissions::STUDENTS_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

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

        $registration = trim(
            (string) Request::post('registration')
        );

        $newClassId = (int) Request::post(
            'school_class_id',
            0
        );

        $transferDate = trim(
            (string) Request::post(
                'transfer_date',
                date('Y-m-d')
            )
        );

        if (
            $this->service->registrationExists(
                $registration,
                $id
            )
        ) {
            Session::set(
                'student_error',
                'Já existe outro aluno com esta matrícula.'
            );

            Response::redirect(
                base_url('alunos/editar?id=' . $id)
            );

            return;
        }

        if ($newClassId <= 0) {
            Session::set(
                'student_error',
                'Selecione a turma atual do aluno.'
            );

            Response::redirect(
                base_url('alunos/editar?id=' . $id)
            );

            return;
        }

        $class = $this->classService->find(
            $newClassId
        );

        if (!$class) {
            Session::set(
                'student_error',
                'A turma selecionada não foi encontrada.'
            );

            Response::redirect(
                base_url('alunos/editar?id=' . $id)
            );

            return;
        }

        try {
            $this->service->updateWithClassTransfer(
                $id,
                [
                    'name' => trim(
                        (string) Request::post('name')
                    ),

                    'registration' => $registration,

                    'birth_date' => trim(
                        (string) Request::post(
                            'birth_date'
                        )
                    ),

                    'guardian_name' => trim(
                        (string) Request::post(
                            'guardian_name'
                        )
                    ),

                    'guardian_phone' => trim(
                        (string) Request::post(
                            'guardian_phone'
                        )
                    ),

                    'active' => (int) Request::post(
                        'active',
                        0
                    ),
                ],
                $newClassId,
                $transferDate
            );

            $this->profilePhotoService->apply(
                'student',
                $id,
                (string) Request::post('photo_cropped_data', ''),
                (int) Request::post('remove_photo', 0) === 1,
                (string) ($student['photo_path'] ?? '')
            );

            Session::set(
                'student_success',
                'Aluno atualizado com sucesso.'
            );

            Response::redirect(
                base_url(
                    'alunos/perfil?id='
                    . $id
                    . '&turma='
                    . $newClassId
                )
            );
        } catch (InvalidArgumentException $exception) {
            Session::set(
                'student_error',
                $exception->getMessage()
            );

            Response::redirect(
                base_url('alunos/editar?id=' . $id)
            );
        } catch (Throwable $exception) {
            Session::set(
                'student_error',
                'Não foi possível atualizar o aluno ou alterar sua turma.'
            );

            Response::redirect(
                base_url('alunos/editar?id=' . $id)
            );
        }
    }

    public function delete(): void
    {
        $this->guard();

        if (
            !$this->authorize(
                Permissions::STUDENTS_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

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

        if ($this->service->delete($id)) {
            Session::set(
                'student_success',
                'Aluno excluído com sucesso.'
            );
        } else {
            Session::set(
                'student_error',
                'Não foi possível excluir o aluno.'
            );
        }

        Response::redirect(
            base_url('alunos')
        );
    }

    public function deleteSelected(): void
    {
        $this->guard();

        if (
            !$this->authorize(
                Permissions::STUDENTS_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $ids = Request::post('ids', []);

        if (
            empty($ids)
            || !is_array($ids)
        ) {
            Session::set(
                'student_error',
                'Nenhum aluno foi selecionado.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $deleted = 0;

        foreach ($ids as $id) {
            $studentId = (int) $id;

            if ($studentId <= 0) {
                continue;
            }

            if ($this->service->delete($studentId)) {
                $deleted++;
            }
        }

        if ($deleted > 0) {
            Session::set(
                'student_success',
                $deleted
                . ' aluno(s) excluído(s) com sucesso.'
            );
        } else {
            Session::set(
                'student_error',
                'Nenhum aluno pôde ser excluído.'
            );
        }

        Response::redirect(
            base_url('alunos')
        );
    }
}