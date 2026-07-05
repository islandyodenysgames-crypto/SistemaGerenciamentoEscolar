<?php

$studentSuccess = \App\Core\Session::get('student_success');
$studentError = \App\Core\Session::get('student_error');

\App\Core\Session::remove('student_success');
\App\Core\Session::remove('student_error');

component('base/page-header', [
    'title' => 'Alunos',
    'subtitle' => 'Gerencie os alunos cadastrados no Sistema de Frequência Escolar'
]);

component('students/page', [
    'studentSuccess' => $studentSuccess,
    'studentError' => $studentError,
    'students' => $students ?? [],
]);

?>