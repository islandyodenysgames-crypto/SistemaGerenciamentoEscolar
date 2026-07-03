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

<?php component('executive-dashboard', $executive ?? []); ?>

<div class="dashboard-row">

    <div class="dashboard-column">

        <?php component('daily-ranking', [
            'ranking' => $ranking ?? []
        ]); ?>

    </div>

    <div class="dashboard-column">

        <?php component('frequency-period-summary', [
            'today' => $schoolFrequencyToday ?? [],
            'week' => $schoolFrequencyWeek ?? [],
            'month' => $schoolFrequencyMonth ?? [],
            'year' => $schoolFrequencyYear ?? [],
        ]); ?>

    </div>

</div>

<div class="dashboard-row">

    <div class="dashboard-column">

        <?php component('classes-without-attendance', [
            'classesWithoutAttendance' => $classesWithoutAttendance ?? []
        ]); ?>

    </div>

    <div class="dashboard-column">

        <?php component('frequency-chart', [
            'frequencyLast30Days' => $frequencyLast30Days ?? []
        ]); ?>

    </div>

</div>

<style>

.dashboard-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:24px;
    margin-top:24px;
    align-items:start;
}

.dashboard-column{
    display:flex;
    flex-direction:column;
    gap:24px;
}

@media (max-width:1200px){

    .dashboard-row{
        grid-template-columns:1fr;
    }

}

</style>