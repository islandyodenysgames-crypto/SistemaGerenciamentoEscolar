<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AttendanceService;
use App\Services\SchoolClassService;

class AttendanceController extends Controller
{
    private AttendanceService $service;

    public function __construct()
    {
        $this->service = new AttendanceService();
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

        $this->view('frequencia/index', [
            'title' => 'Frequência - ' . app_name(),
            'attendances' => $this->service->all(),
        ]);
    }

    public function create(): void
    {
        $this->guard();

        $classService = new SchoolClassService();

        $classId = (int) Request::get('turma');
        $today = date('Y-m-d');

        $students = [];
        $classInfo = null;
        $history = [];
        $existingAttendance = null;

        if ($classId > 0) {
            $students = $this->service->classStudents($classId);
            $classInfo = $this->service->classInfo($classId);
            $history = $this->service->classAttendanceHistory($classId);
            $existingAttendance = $this->service->findByClassAndDate($classId, $today);
        }

        $this->view('frequencia/create', [
            'title' => 'Nova Chamada - ' . app_name(),
            'classes' => $classService->all(),
            'classId' => $classId,
            'classInfo' => $classInfo,
            'students' => $students,
            'history' => $history,
            'today' => $today,
            'existingAttendance' => $existingAttendance,
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

        $this->view('frequencia/show', [
            'title' => 'Visualizar Chamada - ' . app_name(),
            'attendance' => $attendance,
            'items' => $this->service->items($id),
            'statusOptions' => AttendanceService::statusOptions(),
        ]);
    }
}