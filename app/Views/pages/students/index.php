<?php

use App\Auth\Permissions;
use App\Core\Authorization;

$canManageStudents = Authorization::can(
    Permissions::STUDENTS_MANAGE
);

component('base/page-header', [
    'title' => 'Turmas',

    'subtitle' => $canManageStudents
        ? 'Gerencie as turmas, acesse os alunos matriculados e organize as listas por turma.'
        : 'Consulte as turmas e os alunos matriculados.',
]);

component('students/page', [
    'studentSuccess' => $studentSuccess ?? null,

    'studentError' => $studentError ?? null,

    'classSuccess' => $classSuccess ?? null,

    'classError' => $classError ?? null,

    'students' => $students ?? [],

    'classes' => $classes ?? [],
]);

?>