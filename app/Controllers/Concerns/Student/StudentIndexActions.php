<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Student;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use InvalidArgumentException;
use Throwable;

trait StudentIndexActions
{
    public function index(): void
    {
        $this->guard();

        if (
            !$this->authorize(
                Permissions::STUDENTS_VIEW
            )
        ) {
            return;
        }

        $studentSuccess = Session::get(
            'student_success'
        );

        $studentError = Session::get(
            'student_error'
        );

        $classSuccess = Session::get(
            'class_success'
        );

        $classError = Session::get(
            'class_error'
        );

        Session::remove('student_success');
        Session::remove('student_error');
        Session::remove('class_success');
        Session::remove('class_error');

        $this->view('pages/students/index', [
            'title' => 'Turmas - ' . app_name(),

            'students' => $this->service->all(),

            'classes' => $this->classService->all(),

            'studentSuccess' => $studentSuccess,

            'studentError' => $studentError,

            'classSuccess' => $classSuccess,

            'classError' => $classError,
        ]);
    }

    public function create(): void
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

        $selectedClassId = (int) Request::get(
            'turma',
            0
        );

        $origin = trim(
            (string) Request::get(
                'origem',
                ''
            )
        );

        if (
            !in_array(
                $origin,
                ['turma', 'matriculas', 'alunos'],
                true
            )
        ) {
            $origin = $selectedClassId > 0
                ? 'turma'
                : 'alunos';
        }

        $selectedClass = null;

        if ($selectedClassId > 0) {
            $selectedClass = $this->classService
                ->find($selectedClassId);

            if (!$selectedClass) {
                Session::set(
                    'student_error',
                    'A turma informada não foi encontrada.'
                );

                Response::redirect(
                    base_url('alunos')
                );

                return;
            }
        }

        $oldInput = Session::get(
            'student_old_input',
            []
        );

        Session::remove('student_old_input');

        if (!is_array($oldInput)) {
            $oldInput = [];
        }

        if (
            !empty($oldInput['school_class_id'])
        ) {
            $selectedClassId = (int) (
                $oldInput['school_class_id']
            );
        }

        if (!empty($oldInput['origin'])) {
            $origin = (string) $oldInput['origin'];
        }

        $generatedRegistration = !empty(
            $oldInput['registration']
        )
            ? (string) $oldInput['registration']
            : $this->service->randomRegistration();

        $this->view('pages/students/create', [
            'title' => 'Novo Aluno - ' . app_name(),

            'classes' => $this->classService->all(),

            'selectedClassId' => $selectedClassId,

            'selectedClass' => $selectedClass,

            'lockClass' => (
                $origin === 'turma'
                && $selectedClassId > 0
            ),

            'origin' => $origin,

            'oldInput' => $oldInput,

            'generatedRegistration' =>
                $generatedRegistration,
        ]);
    }

    public function store(): void
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

        $registration = trim(
            (string) Request::post('registration')
        );

        $classId = (int) Request::post(
            'school_class_id',
            0
        );

        $origin = trim(
            (string) Request::post(
                'origin',
                'alunos'
            )
        );

        if (
            !in_array(
                $origin,
                ['turma', 'matriculas', 'alunos'],
                true
            )
        ) {
            $origin = 'alunos';
        }

        $data = [
            'name' => trim(
                (string) Request::post('name')
            ),

            'registration' => $registration,

            'birth_date' => trim(
                (string) Request::post('birth_date')
            ),

            'guardian_name' => trim(
                (string) Request::post('guardian_name')
            ),

            'guardian_phone' => trim(
                (string) Request::post('guardian_phone')
            ),
        ];

        Session::set('student_old_input', [
            ...$data,

            'school_class_id' => $classId,

            'origin' => $origin,
        ]);

        $createUrl = $this->studentCreateUrl(
            $classId,
            $origin
        );

        if ($registration === '') {
            Session::set(
                'student_error',
                'Informe ou gere a matrícula do aluno.'
            );

            Response::redirect($createUrl);

            return;
        }

        if ($classId <= 0) {
            Session::set(
                'student_error',
                'Selecione a turma do aluno.'
            );

            Response::redirect($createUrl);

            return;
        }

        $class = $this->classService->find(
            $classId
        );

        if (!$class) {
            Session::set(
                'student_error',
                'A turma selecionada não foi encontrada.'
            );

            Response::redirect($createUrl);

            return;
        }

        if (
            $this->service->registrationExists(
                $registration
            )
        ) {
            Session::set(
                'student_error',
                'Já existe um aluno cadastrado com esta matrícula.'
            );

            Response::redirect($createUrl);

            return;
        }

        try {
            $studentId = $this->service
                ->createWithEnrollment(
                    $data,
                    $classId,
                    date('Y-m-d')
                );

            Session::remove(
                'student_old_input'
            );

            Session::set(
                'student_success',
                'Aluno cadastrado e vinculado à turma com sucesso.'
            );

            if ($origin === 'turma') {
                Response::redirect(
                    base_url(
                        'alunos/turma?id=' . $classId
                    )
                );

                return;
            }

            if ($origin === 'matriculas') {
                Session::set(
                    'enrollment_success',
                    'Aluno cadastrado e matriculado com sucesso.'
                );

                Response::redirect(
                    base_url('matriculas')
                );

                return;
            }

            Response::redirect(
                base_url(
                    'alunos/perfil?id='
                    . $studentId
                    . '&turma='
                    . $classId
                )
            );
        } catch (InvalidArgumentException $exception) {
            Session::set(
                'student_error',
                $exception->getMessage()
            );

            Response::redirect($createUrl);
        } catch (Throwable $exception) {
            Session::set(
                'student_error',
                'Não foi possível cadastrar e matricular o aluno.'
            );

            Response::redirect($createUrl);
        }
    }

    private function studentCreateUrl(
        int $classId,
        string $origin
    ): string {
        $parameters = [
            'origem=' . urlencode($origin),
        ];

        if ($classId > 0) {
            $parameters[] = 'turma=' . $classId;
        }

        return base_url(
            'alunos/novo?' . implode(
                '&',
                $parameters
            )
        );
    }
}