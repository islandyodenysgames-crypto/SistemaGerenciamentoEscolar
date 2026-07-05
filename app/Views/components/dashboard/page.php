<?php

$executive = $executive ?? [];

?>

<?php component('dashboard/hero', $executive); ?>

<?php component('dashboard/kpis', [
    'totalStudents' => $totalStudents ?? 0,
    'totalClasses' => $totalClasses ?? 0,
    'schoolFrequencyToday' => $schoolFrequencyToday ?? [],
]); ?>

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