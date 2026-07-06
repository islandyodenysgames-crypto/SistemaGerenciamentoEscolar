<?php

$executive = $executive ?? [];
$schoolGoals = $schoolGoals ?? \App\Config\SchoolGoals::defaults();

$schoolFrequencyToday = $schoolFrequencyToday ?? [];

?>

<?php component('dashboard/executive-panel', $executive); ?>

<div class="dashboard-main-grid">

    <section class="dashboard-left">

        <?php component('dashboard/ranking', [
            'ranking' => $ranking ?? [],
            'generalPercentage' => $executive['generalPercentage'] ?? $generalPercentage ?? 0,
            'totalStudents' => $totalStudents ?? 0,
            'totalClasses' => $totalClasses ?? 0,
            'presentes' => $schoolFrequencyToday['presentes'] ?? 0,
            'faltas' => $schoolFrequencyToday['faltas'] ?? 0,
        ]); ?>

    </section>

    <aside class="dashboard-right">

        <?php component('dashboard/indicators', [
            'today' => $schoolFrequencyToday,
            'week' => $schoolFrequencyWeek ?? [],
            'month' => $schoolFrequencyMonth ?? [],
            'year' => $schoolFrequencyYear ?? [],
        ]); ?>

    </aside>

</div>

<div class="dashboard-bottom-grid">

    <section>

        <?php component('dashboard/attendance-chart', [
            'frequencyLast30Days' => $frequencyLast30Days ?? [],
            'goalPercentage' => $schoolGoals['frequency_goal'] ?? 95,
        ]); ?>

    </section>

    <section>

        <?php component('dashboard/pending', [
            'classesWithoutAttendance' => $classesWithoutAttendance ?? []
        ]); ?>

    </section>

</div>