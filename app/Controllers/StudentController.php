<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\StudentService;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $service
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

        $this->view('pages/students/index', [
            'title' => 'Alunos - ' . app_name(),
            'students' => $this->service->all(),
        ]);
    }

    public function create(): void
    {
        $this->guard();

        $this->view('pages/students/create', [
            'title' => 'Novo Aluno - ' . app_name(),
        ]);
    }

    public function store(): void
    {
        $this->guard();

        $registration = trim((string) Request::post('registration'));

        if ($this->service->registrationExists($registration)) {
            Session::set(
                'student_error',
                'Já existe um aluno cadastrado com esta matrícula.'
            );

            Response::redirect(base_url('alunos/novo'));
        }

        $this->service->create([
            'name' => trim((string) Request::post('name')),
            'registration' => $registration,
            'birth_date' => trim((string) Request::post('birth_date')),
            'guardian_name' => trim((string) Request::post('guardian_name')),
            'guardian_phone' => trim((string) Request::post('guardian_phone')),
        ]);

        Session::set(
            'student_success',
            'Aluno cadastrado com sucesso.'
        );

        Response::redirect(base_url('alunos'));
    }

    public function edit(): void
    {
        $this->guard();

        $id = (int) Request::get('id');

        $student = $this->service->find($id);

        if (!$student) {
            Response::redirect(base_url('alunos'));
        }

        $this->view('pages/students/edit', [
            'title' => 'Editar Aluno - ' . app_name(),
            'student' => $student,
        ]);
    }

    public function update(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

        $registration = trim((string) Request::post('registration'));

        if ($this->service->registrationExists($registration, $id)) {
            Session::set(
                'student_error',
                'Já existe outro aluno com esta matrícula.'
            );

            Response::redirect(base_url('alunos/editar?id=' . $id));
        }

        $this->service->update($id, [
            'name' => trim((string) Request::post('name')),
            'registration' => $registration,
            'birth_date' => trim((string) Request::post('birth_date')),
            'guardian_name' => trim((string) Request::post('guardian_name')),
            'guardian_phone' => trim((string) Request::post('guardian_phone')),
            'active' => (int) Request::post('active', 0),
        ]);

        Session::set(
            'student_success',
            'Aluno atualizado com sucesso.'
        );

        Response::redirect(base_url('alunos'));
    }

    public function delete(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

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

        Response::redirect(base_url('alunos'));
    }
}