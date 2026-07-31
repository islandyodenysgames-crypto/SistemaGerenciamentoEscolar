<?php

declare(strict_types=1);

namespace App\Controllers\Concerns\Student;

use App\Auth\Permissions;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

trait StudentClassActions
{
    public function classStudents(): void
    {
        $this->guard();

        if (
            !$this->authorize(
                Permissions::STUDENTS_VIEW,
                base_url('alunos')
            )
        ) {
            return;
        }

        $classId = (int) Request::get('id');

        if ($classId <= 0) {
            Session::set(
                'student_error',
                'Turma inválida.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $class = $this->classService->find($classId);

        if (!$class) {
            Session::set(
                'student_error',
                'Turma não encontrada.'
            );

            Response::redirect(
                base_url('alunos')
            );

            return;
        }

        $students = $this->service->byClass($classId);

        $totalStudents = count($students);

        $totalRecords = 0;
        $totalPresentes = 0;
        $frequencyGoal = max(0.0, min(100.0, (float)$this->settings->get('school_goals.frequency_goal', 95.0)));
        $studentsInAlert = 0;
        $alertStudents = [];

        foreach ($students as $student) {
            $records = (int) (
                $student['total_records'] ?? 0
            );

            $presentes = (int) (
                $student['total_presentes'] ?? 0
            );

            $percentage = (float) (
                $student['attendance_percentage'] ?? 0
            );

            $totalRecords += $records;
            $totalPresentes += $presentes;

            if (
                $records > 0
                && $percentage < $frequencyGoal
            ) {
                $studentsInAlert++;
                $alertStudents[] = $student;
            }
        }

        $averageFrequency = $totalRecords > 0
            ? ($totalPresentes / $totalRecords) * 100
            : 0;

        $currentUserId = (int) (((array) Session::get('user', []))['id'] ?? 0);

        $this->view('pages/students/class', [
            'title' => 'Alunos da Turma - ' . app_name(),

            'classId' => $classId,

            'frequencyGoal' => $frequencyGoal,

            'students' => $students,

            'className' => $class['name'] ?? 'Turma',

            'classYear' => $class['year'] ?? '',

            'classShift' => $class['shift'] ?? '',

            'totalStudents' => $totalStudents,

            'totalRecords' => $totalRecords,

            'averageFrequency' => $averageFrequency,

            'studentsInAlert' => $studentsInAlert,

            'alertStudents' => $alertStudents,

            'classIntelligence' => $this->intelligenceService
                ->classDashboard($classId),

            'isClassFavorite' => $this->favoriteService
                ->isFavorite($currentUserId, 'class', $classId),
        ]);
    }
}