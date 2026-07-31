<?php

use App\Auth\Permissions;
use App\Core\Authorization;
use App\Core\Session;

$enrollmentSuccess = Session::get(
    'enrollment_success'
);

$enrollmentError = Session::get(
    'enrollment_error'
);

Session::remove('enrollment_success');
Session::remove('enrollment_error');

$canManageEnrollments = Authorization::can(
    Permissions::ENROLLMENTS_MANAGE
);

$canCreateStudents = Authorization::can(
    Permissions::STUDENTS_MANAGE
);

component('base/page-header', [
    'title' => 'Matrículas',

    'subtitle' => $canManageEnrollments
        ? 'Cadastre alunos, vincule-os às turmas e acompanhe as matrículas.'
        : 'Consulte as matrículas dos alunos nas turmas.',
]);

component('enrollments/page', [
    'enrollmentSuccess' => $enrollmentSuccess,

    'enrollmentError' => $enrollmentError,

    'enrollments' => $enrollments ?? [],

    'canManageEnrollments' =>
        $canManageEnrollments,

    'canCreateStudents' =>
        $canCreateStudents,
]);

?>