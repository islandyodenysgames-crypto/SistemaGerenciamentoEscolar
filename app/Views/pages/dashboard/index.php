<?php

component('page-header', [
    'title' => 'Dashboard',
    'subtitle' => 'Centro de Operações da Frequência Escolar'
]);

?>

<!-- HERO EXECUTIVO -->

<?php component('executive-dashboard', $executive ?? []); ?>


<!-- KPIs -->

<div class="dashboard-kpis">

<?php

component('stat-card', [
    'icon' => 'graduation-cap',
    'label' => 'Alunos Ativos',
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


<!-- PRIMEIRA LINHA -->

<div class="dashboard-main-grid">

    <section class="dashboard-left">

        <?php component('daily-ranking', [
            'ranking' => $ranking ?? []
        ]); ?>

    </section>

    <aside class="dashboard-right">

        <?php component('frequency-period-summary', [
            'today' => $schoolFrequencyToday ?? [],
            'week' => $schoolFrequencyWeek ?? [],
            'month' => $schoolFrequencyMonth ?? [],
            'year' => $schoolFrequencyYear ?? [],
        ]); ?>

    </aside>

</div>


<!-- SEGUNDA LINHA -->

<div class="dashboard-bottom-grid">

    <section>

        <?php component('frequency-chart', [
            'frequencyLast30Days' => $frequencyLast30Days ?? []
        ]); ?>

    </section>

    <section>

        <?php component('classes-without-attendance', [
            'classesWithoutAttendance' => $classesWithoutAttendance ?? []
        ]); ?>

    </section>

</div>