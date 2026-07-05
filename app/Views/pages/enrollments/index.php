<?php

$enrollmentSuccess = \App\Core\Session::get('enrollment_success');
$enrollmentError = \App\Core\Session::get('enrollment_error');

\App\Core\Session::remove('enrollment_success');
\App\Core\Session::remove('enrollment_error');

component('base/page-header', [
    'title' => 'Matrículas',
    'subtitle' => 'Gerencie as matrículas dos alunos nas turmas'
]);

component('enrollments/page', [
    'enrollmentSuccess' => $enrollmentSuccess,
    'enrollmentError' => $enrollmentError,
    'enrollments' => $enrollments ?? [],
]);

?>