<?php

component('base/page-header', [
    'title' => 'Dashboard',
    'subtitle' => 'Centro de Operações Escolar'
]);

component('dashboard/page', [

    'executive' => $executive ?? [],

    'totalStudents' => $totalStudents ?? 0,

    'totalClasses' => $totalClasses ?? 0,

    'schoolFrequencyToday' => $schoolFrequencyToday ?? [],

    'schoolFrequencyWeek' => $schoolFrequencyWeek ?? [],

    'schoolFrequencyMonth' => $schoolFrequencyMonth ?? [],

    'schoolFrequencyYear' => $schoolFrequencyYear ?? [],

    'frequencyLast30Days' => $frequencyLast30Days ?? [],

    'classesWithoutAttendance' => $classesWithoutAttendance ?? [],

    'ranking' => $ranking ?? [],

]);

?>