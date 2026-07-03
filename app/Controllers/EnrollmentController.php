<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\EnrollmentService;
use App\Services\SchoolClassService;
use App\Services\StudentService;

class EnrollmentController extends Controller
{
    private EnrollmentService $service;

    public function __construct()
    {
        $this->service = new EnrollmentService();
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

        $this->view('matriculas/index', [
            'title' => 'Matrículas - ' . app_name(),
            'enrollments' => $this->service->all(),
        ]);
    }

    public function create(): void
    {
        $this->guard();

        $studentService = new StudentService();
        $classService = new SchoolClassService();

        $students = $studentService->availableForEnrollment();

        if (empty($students)) {

            Session::set(
                'enrollment_error',
                'Não existem alunos disponíveis para matrícula. Todos os alunos ativos já estão vinculados a uma turma.'
            );

            Response::redirect(base_url('matriculas'));

            return;
        }

        $this->view('matriculas/create', [
            'title' => 'Nova Matrícula - ' . app_name(),
            'students' => $students,
            'classes' => $classService->all(),
        ]);
    }

    public function store(): void
    {
        $this->guard();

        $studentId = (int) Request::post('student_id');
        $classId = (int) Request::post('school_class_id');

        if ($this->service->exists($studentId, $classId)) {

            Session::set(
                'enrollment_error',
                'Este aluno já está matriculado nesta turma.'
            );

            Response::redirect(base_url('matriculas/novo'));

            return;
        }

        $this->service->create([
            'student_id' => $studentId,
            'school_class_id' => $classId,
            'enrollment_date' => trim((string) Request::post('enrollment_date')),
        ]);

        Session::set(
            'enrollment_success',
            'Matrícula cadastrada com sucesso.'
        );

        Response::redirect(base_url('matriculas'));
    }

    public function cancel(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

        if ($this->service->cancel($id)) {

            Session::set(
                'enrollment_success',
                'Matrícula cancelada com sucesso.'
            );

        } else {

            Session::set(
                'enrollment_error',
                'Não foi possível cancelar a matrícula.'
            );

        }

        Response::redirect(base_url('matriculas'));
    }
}