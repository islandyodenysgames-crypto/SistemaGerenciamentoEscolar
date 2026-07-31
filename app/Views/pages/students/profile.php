<?php

$student = $student ?? [];
$statistics = $statistics ?? [];
$attendanceHistory = $attendanceHistory ?? [];
$attendanceEvolution = $attendanceEvolution ?? [];
$calendar = $calendar ?? [];
$occurrences = $occurrences ?? [];
$occurrenceTypes = $occurrenceTypes ?? [];
$occurrenceTypeCounts = $occurrenceTypeCounts ?? [];
$totalOccurrences = (int) ($totalOccurrences ?? 0);
$openOccurrences = (int) ($openOccurrences ?? 0);
$resolvedOccurrences = (int) ($resolvedOccurrences ?? 0);

$chartMode = $chartMode ?? 'diario';

if (!in_array($chartMode, ['diario', 'mensal'], true)) {
    $chartMode = 'diario';
}

$attendancePercentage = (float) (
    $statistics['attendance_percentage'] ?? 0
);

$totalRecords = (int) (
    $statistics['total_records'] ?? 0
);

$totalFaltas = (int) (
    $statistics['total_faltas'] ?? 0
);

$totalJustificadas = (int) (
    $statistics['total_justificadas'] ?? 0
);

$calendarYear = (int) (
    $calendarYear ?? date('Y')
);

$calendarMonth = (int) (
    $calendarMonth ?? date('n')
);

component('base/page-header', [
    'title' => 'Perfil do Aluno',
    'subtitle' => e($student['name'] ?? 'Aluno'),
]);

component('favorites/page-toggle', [
    'type' => 'student',
    'id' => (int) ($student['id'] ?? 0),
    'active' => (bool) ($isStudentFavorite ?? false),
    'label' => 'Favoritar aluno',
]);

?>

<div class="student-profile-page">

    <?php component('students/profile/hero', [
        'student' => $student,
    ]); ?>

    <?php component('students/profile/stats', [
        'attendancePercentage' => $attendancePercentage,
        'totalRecords' => $totalRecords,
        'totalFaltas' => $totalFaltas,
        'totalJustificadas' => $totalJustificadas,
    ]); ?>

    <?php component('students/profile/tabs'); ?>

    <?php component('students/profile/information', [
        'student' => $student,
        'attendancePercentage' => $attendancePercentage,
        'totalRecords' => $totalRecords,
    ]); ?>

    <?php component('students/profile/monitoring', [
        'student' => $student,
        'studentMonitoring' => $studentMonitoring ?? [],
        'monitoringSuccess' => $monitoringSuccess ?? null,
        'monitoringError' => $monitoringError ?? null,
    ]); ?>

    <?php component('students/profile/intelligence', [
        'studentIntelligence' => $studentIntelligence ?? [],
        'studentMonitoring' => $studentMonitoring ?? [],
        'student' => $student,
    ]); ?>

    <?php component('students/profile/recurrence', [
        'studentRecurrence' => $studentRecurrence ?? [],
    ]); ?>

    <?php component('students/profile/evolution', [
        'student' => $student,
        'attendanceEvolution' => $attendanceEvolution,
        'chartMode' => $chartMode,
        'calendarMonth' => $calendarMonth,
        'calendarYear' => $calendarYear,
    ]); ?>

    <?php component('students/profile/calendar', [
        'student' => $student,
        'calendar' => $calendar,
        'calendarYear' => $calendarYear,
        'calendarMonth' => $calendarMonth,
        'chartMode' => $chartMode,
    ]); ?>

    <?php component('students/profile/occurrences', [
        'student' => $student,
        'occurrences' => $occurrences,
        'occurrenceTypes' => $occurrenceTypes,
        'occurrenceActions' => $occurrenceActions ?? [],
        'occurrenceActionTypes' => $occurrenceActionTypes ?? [],
        'occurrenceActionTypeIcons' => $occurrenceActionTypeIcons ?? [],
        'totalOccurrences' => $totalOccurrences,
        'openOccurrences' => $openOccurrences,
        'resolvedOccurrences' => $resolvedOccurrences,
        'occurrenceSuccess' => $occurrenceSuccess ?? null,
        'occurrenceError' => $occurrenceError ?? null,
        'occurrenceTypeCounts' => $occurrenceTypeCounts ?? [],
        'occurrenceSeverities' => $occurrenceSeverities ?? [],
        'occurrenceSeverityClasses' => $occurrenceSeverityClasses ?? [],
        'occurrenceSeverityIcons' => $occurrenceSeverityIcons ?? [],
        'occurrenceSeverityCounts' => $occurrenceSeverityCounts ?? [],
    ]); ?>

</div>