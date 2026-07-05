<?php

$executive = $executive ?? [];

?>

<?php component('dashboard/hero', $executive); ?>

<div class="dashboard-kpis">

<?php

component('base/stat-card', [
    'icon' => 'graduation-cap',
    'label' => 'Alunos Ativos',
    'value' => $totalStudents ?? 0
]);

component('base/stat-card', [
    'icon' => 'school',
    'label' => 'Turmas',
    'value' => $totalClasses ?? 0
]);

component('base/stat-card', [
    'icon' => 'clipboard-check',
    'label' => 'Presentes Hoje',
    'value' => (int) ($schoolFrequencyToday['presentes'] ?? 0)
]);

component('base/stat-card', [
    'icon' => 'circle-x',
    'label' => 'Faltas Hoje',
    'value' => (int) ($schoolFrequencyToday['faltas'] ?? 0)
]);

?>

</div>

<div class="dashboard-main-grid">

    <section class="dashboard-left">

        <?php component('dashboard/ranking', [
            'ranking' => $ranking ?? []
        ]); ?>

    </section>

    <aside class="dashboard-right">

        <?php component('dashboard/indicators', [
            'today' => $schoolFrequencyToday ?? [],
            'week' => $schoolFrequencyWeek ?? [],
            'month' => $schoolFrequencyMonth ?? [],
            'year' => $schoolFrequencyYear ?? [],
        ]); ?>

    </aside>

</div>

<div class="dashboard-bottom-grid">

    <section>

        <?php component('dashboard/attendance-chart', [
            'frequencyLast30Days' => $frequencyLast30Days ?? []
        ]); ?>

    </section>

    <section>

        <?php component('dashboard/pending', [
            'classesWithoutAttendance' => $classesWithoutAttendance ?? []
        ]); ?>

    </section>

</div>