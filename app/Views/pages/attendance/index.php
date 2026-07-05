<?php

$attendanceSuccess = \App\Core\Session::get('attendance_success');
$attendanceError = \App\Core\Session::get('attendance_error');

\App\Core\Session::remove('attendance_success');
\App\Core\Session::remove('attendance_error');

$central = $central ?? [];
$classes = $central['classes'] ?? [];

component('base/page-header', [
    'title' => 'Central de Frequência',
    'subtitle' => 'Acompanhe e registre as chamadas das turmas no dia'
]);

component('attendance/page', [

    'attendanceSuccess' => $attendanceSuccess,

    'attendanceError' => $attendanceError,

    'today' => $today ?? date('Y-m-d'),

    'classes' => $classes,

    'totalClasses' => (int) ($central['totalClasses'] ?? 0),

    'doneClasses' => (int) ($central['doneClasses'] ?? 0),

    'pendingClasses' => (int) ($central['pendingClasses'] ?? 0),

    'generalPercentage' => (float) ($central['generalPercentage'] ?? 0),

]);

?>