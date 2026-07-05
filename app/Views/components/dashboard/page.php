<?php

$executive = $executive ?? [];

?>

<?php component('dashboard/executive-panel', $executive); ?>

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