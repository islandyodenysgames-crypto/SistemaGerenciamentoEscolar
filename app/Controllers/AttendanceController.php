<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AttendanceAnalyticsService;
use App\Services\AttendanceService;
use App\Services\SchoolClassService;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $service,
        private AttendanceAnalyticsService $analyticsService,
        private SchoolClassService $classService
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

        $today = date('Y-m-d');

        $this->view('pages/attendance/index', [
            'title' => 'Frequência - ' . app_name(),
            'today' => $today,
            'central' => $this->analyticsService->dailyCentral($today),
            'attendances' => $this->service->all(),
        ]);
    }

    public function create(): void
    {
        $this->guard();

        $classId = (int) Request::get('turma');
        $today = date('Y-m-d');

        $students = [];
        $classInfo = null;
        $history = [];
        $existingAttendance = null;
        $existingStatuses = [];

        if ($classId > 0) {
            $students = $this->service->classStudents($classId);
            $classInfo = $this->service->classInfo($classId);
            $history = $this->service->classAttendanceHistory($classId);
            $existingAttendance = $this->service->findByClassAndDate($classId, $today);

            if ($existingAttendance) {
                foreach ($this->service->items((int) $existingAttendance['id']) as $item) {
                    $existingStatuses[(int) $item['student_id']] = $item['status'];
                }
            }
        }

        $this->view('pages/attendance/create', [
            'title' => 'Nova Chamada - ' . app_name(),
            'classes' => $this->classService->all(),
            'classId' => $classId,
            'classInfo' => $classInfo,
            'students' => $students,
            'history' => $history,
            'today' => $today,
            'existingAttendance' => $existingAttendance,
            'existingStatuses' => $existingStatuses,
            'statusOptions' => AttendanceService::statusOptions(),
        ]);
    }

    public function store(): void
    {
        $this->guard();

        $classId = (int) Request::post('school_class_id');
        $date = trim((string) Request::post('attendance_date'));

        if ($this->service->existsForClassAndDate($classId, $date)) {
            Session::set(
                'attendance_error',
                'Já existe uma chamada registrada para esta turma nesta data.'
            );

            Response::redirect(base_url('frequencia/novo?turma=' . $classId));
            return;
        }

        $attendanceId = $this->service->create([
            'school_class_id' => $classId,
            'attendance_date' => $date,
            'notes' => trim((string) Request::post('notes')),
        ]);

        $statuses = $_POST['status'] ?? [];

        foreach ($statuses as $studentId => $status) {
            $this->service->insertAttendanceItem(
                $attendanceId,
                (int) $studentId,
                (string) $status
            );
        }

        Session::set(
            'attendance_success',
            'Chamada registrada com sucesso.'
        );

        Response::redirect(base_url('frequencia'));
    }

    public function show(): void
    {
        $this->guard();

        $id = (int) Request::get('id');

        $attendance = $this->service->find($id);

        if (!$attendance) {
            Session::set(
                'attendance_error',
                'Chamada não encontrada.'
            );

            Response::redirect(base_url('frequencia'));
            return;
        }

        $this->view('pages/attendance/show', [
            'title' => 'Visualizar Chamada - ' . app_name(),
            'attendance' => $attendance,
            'items' => $this->service->items($id),
            'statusOptions' => AttendanceService::statusOptions(),
        ]);
    }

    public function edit(): void
    {
        $this->guard();

        $id = (int) Request::get('id');

        $attendance = $this->service->find($id);

        if (!$attendance) {
            Session::set(
                'attendance_error',
                'Chamada não encontrada.'
            );

            Response::redirect(base_url('frequencia'));
            return;
        }

        $this->view('pages/attendance/edit', [
            'title' => 'Editar Chamada - ' . app_name(),
            'attendance' => $attendance,
            'items' => $this->service->items($id),
            'statusOptions' => AttendanceService::statusOptions(),
        ]);
    }

    public function update(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

        $this->service->update($id, [
            'attendance_date' => trim((string) Request::post('attendance_date')),
            'notes' => trim((string) Request::post('notes')),
        ]);

        $statuses = $_POST['status'] ?? [];

        foreach ($statuses as $itemId => $status) {
            $this->service->updateItemStatus(
                (int) $itemId,
                (string) $status
            );
        }

        Session::set(
            'attendance_success',
            'Chamada atualizada com sucesso.'
        );

        Response::redirect(base_url('frequencia'));
    }

    public function delete(): void
    {
        $this->guard();

        $id = (int) Request::post('id');

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

        Response::redirect(base_url('frequencia'));
    }
}