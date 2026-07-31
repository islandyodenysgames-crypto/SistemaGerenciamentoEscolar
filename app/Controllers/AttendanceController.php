<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceService;
use App\Services\SchoolClassService;

class AttendanceController extends BaseController
{
    public function __construct(
        private AttendanceService $service,
        private AttendanceAnalyticsService $analyticsService,
        private SchoolClassService $classService
    ) {
    }

    /*
     * Página principal da frequência.
     *
     * Pode ser acessada por quem possui permissão
     * para consultar frequência.
     */
    public function index(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_VIEW,
                base_url()
            )
        ) {
            return;
        }

        $today = date('Y-m-d');

        $attendanceSuccess = Session::get(
            'attendance_success'
        );

        $attendanceError = Session::get(
            'attendance_error'
        );

        Session::remove('attendance_success');
        Session::remove('attendance_error');

        $this->view('pages/attendance/index', [
            'title' => 'Frequência - ' . app_name(),

            'today' => $today,

            'central' => $this->analyticsService
                ->dailyCentral($today),

            'attendances' => $this->service->all(),

            'attendanceSuccess' => $attendanceSuccess,

            'attendanceError' => $attendanceError,
        ]);
    }

    /*
     * Formulário de nova chamada.
     *
     * Professor não pode acessar.
     * Secretaria pode acessar.
     */
    public function create(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_MANAGE,
                base_url()
            )
        ) {
            return;
        }

        $classId = (int) Request::get(
            'turma',
            0
        );

        $today = trim(
            (string) Request::get(
                'data',
                ''
            )
        );

        if ($today === '') {
            $today = date('Y-m-d');
        }

        $students = [];
        $classInfo = null;
        $history = [];
        $existingAttendance = null;
        $existingStatuses = [];

        if ($classId > 0) {
            $students = $this->service
                ->classStudents($classId);

            $classInfo = $this->service
                ->classInfo($classId);

            $history = $this->service
                ->classAttendanceHistory($classId);

            $existingAttendance = $this->service
                ->findByClassAndDate(
                    $classId,
                    $today
                );

            if ($existingAttendance) {
                $attendanceId = (int) (
                    $existingAttendance['id'] ?? 0
                );

                foreach (
                    $this->service->items(
                        $attendanceId
                    ) as $item
                ) {
                    $studentId = (int) (
                        $item['student_id'] ?? 0
                    );

                    if ($studentId <= 0) {
                        continue;
                    }

                    $existingStatuses[$studentId] =
                        (string) (
                            $item['status'] ?? ''
                        );
                }
            }
        }

        $attendanceError = Session::get(
            'attendance_error'
        );

        Session::remove('attendance_error');

        $this->view('pages/attendance/create', [
            'title' => 'Nova Chamada - ' . app_name(),

            'classes' => $this->classService->all(),

            'classId' => $classId,

            'classInfo' => $classInfo,

            'students' => $students,

            'history' => $history,

            'today' => $today,

            'existingAttendance' =>
                $existingAttendance,

            'existingStatuses' =>
                $existingStatuses,

            'statusOptions' =>
                AttendanceService::statusOptions(),

            'attendanceError' =>
                $attendanceError,
        ]);
    }

    /*
     * Salva uma nova chamada.
     */
    public function store(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_MANAGE,
                base_url()
            )
        ) {
            return;
        }

        $classId = (int) Request::post(
            'school_class_id',
            0
        );

        $date = trim(
            (string) Request::post(
                'attendance_date',
                ''
            )
        );

        if ($classId <= 0) {
            Session::set(
                'attendance_error',
                'Selecione uma turma válida.'
            );

            Response::redirect(
                base_url('frequencia/novo')
            );

            return;
        }

        if ($date === '') {
            Session::set(
                'attendance_error',
                'Informe a data da chamada.'
            );

            Response::redirect(
                base_url(
                    'frequencia/novo?turma='
                    . $classId
                )
            );

            return;
        }

        if (
            $this->service->existsForClassAndDate(
                $classId,
                $date
            )
        ) {
            Session::set(
                'attendance_error',
                'Já existe uma chamada registrada para esta turma nesta data.'
            );

            Response::redirect(
                base_url(
                    'frequencia/novo?turma='
                    . $classId
                    . '&data='
                    . urlencode($date)
                )
            );

            return;
        }

        $attendanceId = $this->service->create([
            'school_class_id' => $classId,

            'attendance_date' => $date,

            'notes' => trim(
                (string) Request::post(
                    'notes',
                    ''
                )
            ),
        ]);

        $statuses = Request::post(
            'status',
            []
        );

        if (!is_array($statuses)) {
            $statuses = [];
        }

        foreach ($statuses as $studentId => $status) {
            $studentId = (int) $studentId;
            $status = trim((string) $status);

            if ($studentId <= 0 || $status === '') {
                continue;
            }

            $this->service->insertAttendanceItem(
                $attendanceId,
                $studentId,
                $status
            );
        }

        Session::set(
            'attendance_success',
            'Chamada registrada com sucesso.'
        );

        Response::redirect(
            base_url('frequencia')
        );
    }

    /*
     * Visualização de uma chamada.
     */
    public function show(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_VIEW,
                base_url()
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
                'attendance_error',
                'Chamada inválida.'
            );

            Response::redirect(
                base_url('frequencia')
            );

            return;
        }

        $attendance = $this->service->find($id);

        if (!$attendance) {
            Session::set(
                'attendance_error',
                'Chamada não encontrada.'
            );

            Response::redirect(
                base_url('frequencia')
            );

            return;
        }

        $this->view('pages/attendance/show', [
            'title' => 'Visualizar Chamada - '
                . app_name(),

            'attendance' => $attendance,

            'items' => $this->service->items($id),

            'statusOptions' =>
                AttendanceService::statusOptions(),
        ]);
    }

    /*
     * Formulário de edição da chamada.
     */
    public function edit(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_MANAGE,
                base_url('frequencia')
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
                'attendance_error',
                'Chamada inválida.'
            );

            Response::redirect(
                base_url('frequencia')
            );

            return;
        }

        $attendance = $this->service->find($id);

        if (!$attendance) {
            Session::set(
                'attendance_error',
                'Chamada não encontrada.'
            );

            Response::redirect(
                base_url('frequencia')
            );

            return;
        }

        $this->view('pages/attendance/edit', [
            'title' => 'Editar Chamada - '
                . app_name(),

            'attendance' => $attendance,

            'items' => $this->service->items($id),

            'statusOptions' =>
                AttendanceService::statusOptions(),
        ]);
    }

    /*
     * Atualização da chamada.
     */
    public function update(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_MANAGE,
                base_url('frequencia')
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
                'attendance_error',
                'Chamada inválida.'
            );

            Response::redirect(
                base_url('frequencia')
            );

            return;
        }

        $attendance = $this->service->find($id);

        if (!$attendance) {
            Session::set(
                'attendance_error',
                'Chamada não encontrada.'
            );

            Response::redirect(
                base_url('frequencia')
            );

            return;
        }

        $this->service->update($id, [
            'attendance_date' => trim(
                (string) Request::post(
                    'attendance_date',
                    ''
                )
            ),

            'notes' => trim(
                (string) Request::post(
                    'notes',
                    ''
                )
            ),
        ]);

        $statuses = Request::post(
            'status',
            []
        );

        if (!is_array($statuses)) {
            $statuses = [];
        }

        foreach ($statuses as $itemId => $status) {
            $itemId = (int) $itemId;
            $status = trim((string) $status);

            if ($itemId <= 0 || $status === '') {
                continue;
            }

            $this->service->updateItemStatus(
                $itemId,
                $status
            );
        }

        Session::set(
            'attendance_success',
            'Chamada atualizada com sucesso.'
        );

        Response::redirect(
            base_url('frequencia')
        );
    }

    /*
     * Exclusão individual.
     */
    public function delete(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_MANAGE,
                base_url('frequencia')
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
                'attendance_error',
                'Chamada inválida.'
            );

            Response::redirect(
                base_url('frequencia')
            );

            return;
        }

        if ($this->service->delete($id)) {
            Session::set(
                'attendance_success',
                'Chamada excluída com sucesso.'
            );
        } else {
            Session::set(
                'attendance_error',
                'Não foi possível excluir a chamada.'
            );
        }

        Response::redirect(
            base_url('frequencia')
        );
    }

    /*
     * Histórico de frequências.
     */
    public function history(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_VIEW,
                base_url()
            )
        ) {
            return;
        }

        $date = trim(
            (string) Request::get(
                'data',
                ''
            )
        );

        $classId = (int) Request::get(
            'turma',
            0
        );

        $attendanceSuccess = Session::get(
            'attendance_success'
        );

        $attendanceError = Session::get(
            'attendance_error'
        );

        Session::remove('attendance_success');
        Session::remove('attendance_error');

        $this->view('pages/attendance/history', [
            'title' => 'Histórico de Frequências - '
                . app_name(),

            'date' => $date,

            'classId' => $classId,

            'classes' => $this->classService->all(),

            'history' => $this->service->history(
                $date !== ''
                    ? $date
                    : null,

                $classId > 0
                    ? $classId
                    : null
            ),

            'attendanceSuccess' =>
                $attendanceSuccess,

            'attendanceError' =>
                $attendanceError,
        ]);
    }

    /*
     * Exclusão de várias chamadas.
     */
    public function deleteSelected(): void
    {
        if (!$this->guard()) {
            return;
        }

        if (
            !$this->authorize(
                Permissions::ATTENDANCE_MANAGE,
                base_url('frequencia/historico')
            )
        ) {
            return;
        }

        $ids = Request::post(
            'ids',
            []
        );

        if (
            empty($ids)
            || !is_array($ids)
        ) {
            Session::set(
                'attendance_error',
                'Nenhuma frequência foi selecionada.'
            );

            Response::redirect(
                base_url('frequencia/historico')
            );

            return;
        }

        $deleted = 0;

        foreach ($ids as $id) {
            $attendanceId = (int) $id;

            if ($attendanceId <= 0) {
                continue;
            }

            if (
                $this->service->delete(
                    $attendanceId
                )
            ) {
                $deleted++;
            }
        }

        if ($deleted > 0) {
            Session::set(
                'attendance_success',
                $deleted
                . ' frequência(s) excluída(s) com sucesso.'
            );
        } else {
            Session::set(
                'attendance_error',
                'Nenhuma frequência pôde ser excluída.'
            );
        }

        Response::redirect(
            base_url('frequencia/historico')
        );
    }
}