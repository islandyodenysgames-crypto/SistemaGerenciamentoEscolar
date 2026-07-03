<?php

component('page-header', [
    'title' => 'Dashboard',
    'subtitle' => 'Centro de monitoramento da frequência escolar'
]);

?>

<div class="dashboard-grid">

<?php

component('stat-card', [
    'icon' => 'graduation-cap',
    'label' => 'Alunos',
    'value' => $totalStudents ?? 0
]);

component('stat-card', [
    'icon' => 'school',
    'label' => 'Turmas',
    'value' => $totalClasses ?? 0
]);

component('stat-card', [
    'icon' => 'clipboard-check',
    'label' => 'Presentes Hoje',
    'value' => (int) ($schoolFrequencyToday['presentes'] ?? 0)
]);

component('stat-card', [
    'icon' => 'circle-x',
    'label' => 'Faltas Hoje',
    'value' => (int) ($schoolFrequencyToday['faltas'] ?? 0)
]);

?>

</div>

<div class="dashboard-grid" style="margin-top:24px;">

    <div class="card" style="text-align:center;">
        <?php component('attendance-progress-circle', [
            'percentage' => (float) ($schoolFrequencyToday['percentage'] ?? 0),
            'label' => 'Hoje',
            'size' => 140
        ]); ?>
    </div>

    <div class="card" style="text-align:center;">
        <?php component('attendance-progress-circle', [
            'percentage' => (float) ($schoolFrequencyWeek['percentage'] ?? 0),
            'label' => 'Semana',
            'size' => 140
        ]); ?>
    </div>

    <div class="card" style="text-align:center;">
        <?php component('attendance-progress-circle', [
            'percentage' => (float) ($schoolFrequencyMonth['percentage'] ?? 0),
            'label' => 'Mês',
            'size' => 140
        ]); ?>
    </div>

    <div class="card" style="text-align:center;">
        <?php component('attendance-progress-circle', [
            'percentage' => (float) ($schoolFrequencyYear['percentage'] ?? 0),
            'label' => 'Ano',
            'size' => 140
        ]); ?>
    </div>

</div>

<div class="dashboard-content">

    <?php component('school-frequency-summary', [
        'schoolFrequencyToday' => $schoolFrequencyToday ?? []
    ]); ?>

    <?php component('frequency-period-summary', [
        'today' => $schoolFrequencyToday ?? [],
        'week' => $schoolFrequencyWeek ?? [],
        'month' => $schoolFrequencyMonth ?? [],
        'year' => $schoolFrequencyYear ?? [],
    ]); ?>

    <?php component('daily-ranking', [
        'ranking' => $ranking ?? []
    ]); ?>

    <?php component('classes-without-attendance', [
        'classesWithoutAttendance' => $classesWithoutAttendance ?? []
    ]); ?>

    <?php component('frequency-chart', [
        'frequencyLast30Days' => $frequencyLast30Days ?? []
    ]); ?>

    <?php component('quick-actions'); ?>

    <?php component('calendar'); ?>

    <?php component('recent-activities'); ?>

</div>