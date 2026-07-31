<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\EnrollmentService;
use App\Services\SchoolClassService;
use App\Services\StudentImportService;
use App\Services\StudentService;

class EnrollmentController extends BaseController
{
    public function __construct(
        private EnrollmentService $service,
        private StudentService $studentService,
        private SchoolClassService $classService,
        private StudentImportService $studentImportService
    ) {
    }

    public function index(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ENROLLMENTS_VIEW
            )
        ) {
            return;
        }

        $this->view('pages/enrollments/index', [
            'title' => 'Matrículas - ' . app_name(),

            'enrollments' => $this->service->all(),
        ]);
    }

    public function create(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::STUDENTS_MANAGE,
                base_url('matriculas')
            )
        ) {
            return;
        }

        Response::redirect(
            base_url(
                'alunos/novo?origem=matriculas'
            )
        );
    }

    public function store(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ENROLLMENTS_MANAGE,
                base_url('matriculas')
            )
        ) {
            return;
        }

        $studentId = (int) Request::post(
            'student_id'
        );

        $classId = (int) Request::post(
            'school_class_id'
        );

        if (
            $studentId <= 0
            || $classId <= 0
        ) {
            Session::set(
                'enrollment_error',
                'Aluno ou turma inválidos.'
            );

            Response::redirect(
                base_url('matriculas')
            );

            return;
        }

        if (
            $this->service->exists(
                $studentId,
                $classId
            )
        ) {
            Session::set(
                'enrollment_error',
                'Este aluno já está matriculado nesta turma.'
            );

            Response::redirect(
                base_url('matriculas')
            );

            return;
        }

        $this->service->create([
            'student_id' => $studentId,

            'school_class_id' => $classId,

            'enrollment_date' => trim(
                (string) Request::post(
                    'enrollment_date',
                    date('Y-m-d')
                )
            ),
        ]);

        Session::set(
            'enrollment_success',
            'Matrícula cadastrada com sucesso.'
        );

        Response::redirect(
            base_url('matriculas')
        );
    }

    public function import(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ENROLLMENTS_MANAGE,
                base_url('matriculas')
            )
        ) {
            return;
        }

        $this->view('pages/enrollments/import', [
            'title' => 'Importar Alunos - ' . app_name(),

            'classes' => $this->classService->all(),

            'preview' => Session::get(
                'student_import_preview'
            ) ?? [],

            'schoolClassId' => Session::get(
                'student_import_class_id'
            ),
        ]);
    }

    public function importStore(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ENROLLMENTS_MANAGE,
                base_url('matriculas')
            )
        ) {
            return;
        }

        $classId = (int) Request::post(
            'school_class_id'
        );

        $preview = $this->studentImportService
            ->preview(
                $_FILES['students_file'] ?? []
            );

        if (empty($preview)) {
            Session::set(
                'enrollment_error',
                'Não foi possível localizar alunos na planilha enviada.'
            );

            Response::redirect(
                base_url('matriculas/importar')
            );

            return;
        }

        Session::set(
            'student_import_preview',
            $preview
        );

        Session::set(
            'student_import_class_id',
            $classId
        );

        Response::redirect(
            base_url('matriculas/importar')
        );
    }

    public function importConfirm(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ENROLLMENTS_MANAGE,
                base_url('matriculas')
            )
        ) {
            return;
        }

        $preview = Session::get(
            'student_import_preview'
        ) ?? [];

        $classId = (int) Session::get(
            'student_import_class_id'
        );

        if (
            empty($preview)
            || $classId <= 0
        ) {
            Session::set(
                'enrollment_error',
                'Nenhuma pré-visualização encontrada para confirmar.'
            );

            Response::redirect(
                base_url('matriculas/importar')
            );

            return;
        }

        $result = $this->studentImportService
            ->confirm(
                $preview,
                $classId
            );

        Session::remove(
            'student_import_preview'
        );

        Session::remove(
            'student_import_class_id'
        );

        Session::set(
            'enrollment_success',
            'Importação concluída: '
            . $result['created']
            . ' aluno(s) novo(s), '
            . $result['existing']
            . ' já cadastrado(s), '
            . $result['enrolled']
            . ' matrícula(s) realizada(s).'
        );

        Response::redirect(
            base_url('matriculas')
        );
    }

    public function cancel(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ENROLLMENTS_MANAGE,
                base_url('matriculas')
            )
        ) {
            return;
        }

        $id = (int) Request::post('id');

        if ($id <= 0) {
            Session::set(
                'enrollment_error',
                'Matrícula inválida.'
            );

            Response::redirect(
                base_url('matriculas')
            );

            return;
        }

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

        Response::redirect(
            base_url('matriculas')
        );
    }
}