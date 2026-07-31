<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Controllers\Concerns\AuthorizesAccess;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SchoolClassService;

class SchoolClassController extends Controller
{
    use AuthorizesAccess;

    public function __construct(
        private SchoolClassService $service
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

        if (
            !$this->authorizeAccess(
                Permissions::STUDENTS_VIEW
            )
        ) {
            return;
        }

        Response::redirect(base_url('alunos'));
    }

    public function create(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::STUDENTS_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $this->view('pages/classes/create', [
            'title' => 'Nova Turma - ' . app_name(),
        ]);
    }

    public function store(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::STUDENTS_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $name = trim(
            (string) Request::post('name')
        );

        $year = (int) Request::post('year');

        if ($this->service->exists($name, $year)) {
            Session::set(
                'class_error',
                'Já existe uma turma com este nome neste ano letivo.'
            );

            Response::redirect(base_url('turmas/novo'));
            return;
        }

        $photoPath = null;

        try {
            $croppedPhoto = trim((string) Request::post('class_photo_cropped_data', ''));
            if ($croppedPhoto !== '') {
                $photoPath = $this->service->saveCroppedPhoto($croppedPhoto);
            } elseif (isset($_FILES['class_photo']) && (int)($_FILES['class_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                // Compatibilidade com formulários antigos.
                $photoPath = $this->service->savePhoto($_FILES['class_photo']);
            }

            $this->service->create([
                'name' => $name,
                'year' => $year,

                'shift' => trim(
                    (string) Request::post('shift')
                ),
                'photo_path' => $photoPath,
            ]);
        } catch (\InvalidArgumentException $exception) {
            if ($photoPath !== null) {
                $this->service->removePhoto($photoPath);
            }
            Session::set('class_error', $exception->getMessage());
            Response::redirect(base_url('turmas/novo'));
            return;
        }

        Session::set(
            'class_success',
            'Turma cadastrada com sucesso.'
        );

        Response::redirect(base_url('alunos'));
    }

    public function edit(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::STUDENTS_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::get('id');

        $class = $this->service->find($id);

        if (!$class) {
            Session::set(
                'class_error',
                'Turma não encontrada.'
            );

            Response::redirect(base_url('alunos'));
            return;
        }

        $this->view('pages/classes/edit', [
            'title' => 'Editar Turma - ' . app_name(),
            'class' => $class,
        ]);
    }

    public function update(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::STUDENTS_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        $class = $this->service->find($id);

        if (!$class) {
            Session::set(
                'class_error',
                'Turma não encontrada.'
            );

            Response::redirect(base_url('alunos'));
            return;
        }

        $name = trim(
            (string) Request::post('name')
        );

        $year = (int) Request::post('year');

        if ($this->service->exists(
            $name,
            $year,
            $id
        )) {
            Session::set(
                'class_error',
                'Já existe outra turma com este nome neste ano letivo.'
            );

            Response::redirect(
                base_url('turmas/editar?id=' . $id)
            );

            return;
        }

        $photoPath = (string)($class['photo_path'] ?? '');
        $photoChanged = false;

        try {
            if (!empty(Request::post('remove_class_photo'))) {
                $this->service->removePhoto($photoPath);
                $photoPath = '';
                $photoChanged = true;
            }

            $croppedPhoto = trim((string) Request::post('class_photo_cropped_data', ''));
            if ($croppedPhoto !== '') {
                $photoPath = $this->service->saveCroppedPhoto($croppedPhoto, $photoPath);
                $photoChanged = true;
            } elseif (isset($_FILES['class_photo']) && (int)($_FILES['class_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                // Compatibilidade com formulários antigos.
                $photoPath = $this->service->savePhoto($_FILES['class_photo'], $photoPath);
                $photoChanged = true;
            }

            $this->service->update($id, [
                'name' => $name,
                'year' => $year,

                'shift' => trim(
                    (string) Request::post('shift')
                ),

                'active' => (int) Request::post(
                    'active',
                    0
                ),
                'photo_path' => $photoPath !== '' ? $photoPath : null,
                'photo_changed' => $photoChanged,
            ]);
        } catch (\InvalidArgumentException $exception) {
            Session::set('class_error', $exception->getMessage());
            Response::redirect(base_url('turmas/editar?id=' . $id));
            return;
        }

        Session::set(
            'class_success',
            'Turma atualizada com sucesso.'
        );

        Response::redirect(base_url('alunos'));
    }

    public function delete(): void
    {
        $this->guard();

        if (
            !$this->authorizeAccess(
                Permissions::STUDENTS_MANAGE,
                base_url('alunos')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        $class = $this->service->find($id);

        if (!$class) {
            Session::set(
                'class_error',
                'Turma não encontrada.'
            );

            Response::redirect(base_url('alunos'));
            return;
        }

        $photoPath = (string)($class['photo_path'] ?? '');

        if ($this->service->delete($id)) {
            if ($photoPath !== '') {
                $this->service->removePhoto($photoPath);
            }
            Session::set(
                'class_success',
                'Turma excluída com sucesso.'
            );
        } else {
            Session::set(
                'class_error',
                'Não foi possível excluir a turma.'
            );
        }

        Response::redirect(base_url('alunos'));
    }
}