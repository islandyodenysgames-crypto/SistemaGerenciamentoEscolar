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

        $students = [];

        if ($classId > 0) {
            $students = $this->service->classStudents($classId);
        }

        $this->view('frequencia/create', [
            'title'         => 'Nova Chamada - ' . app_name(),
            'classes'       => $classService->all(),
            'classId'       => $classId,
            'students'      => $students,
            'statusOptions' => AttendanceService::statusOptions(),
        ]);
    }

    public function store(): void
    {
        $this->guard();

        $attendanceId = $this->service->create([
            'school_class_id' => (int) Request::post('school_class_id'),
            'attendance_date' => trim((string) Request::post('attendance_date')),
            'notes'           => trim((string) Request::post('notes')),
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
            Response::redirect(base_url('frequencia'));
        }

        $this->view('frequencia/show', [
            'title'         => 'Visualizar Chamada - ' . app_name(),
            'attendance'    => $attendance,
            'items'         => $this->service->items($id),
            'statusOptions' => AttendanceService::statusOptions(),
        ]);
    }
}