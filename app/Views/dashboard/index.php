<?php

component('page-header', [
    'title' => 'Dashboard',
    'subtitle' => 'Painel executivo de monitoramento da frequência escolar'
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

<div class="card" style="margin-top:24px;">

    <div class="card-header">
        <h3>Painel Executivo de Frequência</h3>
    </div>

    <div
        style="
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:20px;
            margin-top:20px;
        "
    >

        <div style="text-align:center;">
            <?php component('attendance-progress-circle', [
                'percentage' => (float) ($schoolFrequencyToday['percentage'] ?? 0),
                'label' => 'Hoje',
                'size' => 150
            ]); ?>
        </div>

        <div style="text-align:center;">
            <?php component('attendance-progress-circle', [
                'percentage' => (float) ($schoolFrequencyWeek['percentage'] ?? 0),
                'label' => 'Semana',
                'size' => 150
            ]); ?>
        </div>

        <div style="text-align:center;">
            <?php component('attendance-progress-circle', [
                'percentage' => (float) ($schoolFrequencyMonth['percentage'] ?? 0),
                'label' => 'Mês',
                'size' => 150
            ]); ?>
        </div>

        <div style="text-align:center;">
            <?php component('attendance-progress-circle', [
                'percentage' => (float) ($schoolFrequencyYear['percentage'] ?? 0),
                'label' => 'Ano',
                'size' => 150
            ]); ?>
        </div>

    </div>

</div>

<div class="dashboard-content" style="margin-top:24px;">

    <?php component('daily-ranking', [
        'ranking' => $ranking ?? []
    ]); ?>

    <?php component('frequency-period-summary', [
        'today' => $schoolFrequencyToday ?? [],
        'week' => $schoolFrequencyWeek ?? [],
        'month' => $schoolFrequencyMonth ?? [],
        'year' => $schoolFrequencyYear ?? [],
    ]); ?>

    <?php component('classes-without-attendance', [
        'classesWithoutAttendance' => $classesWithoutAttendance ?? []
    ]); ?>

    <?php component('frequency-chart', [
        'frequencyLast30Days' => $frequencyLast30Days ?? []
    ]); ?>

</div>