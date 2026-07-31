<?php

$noticesPriority = strtoupper(
    (string) ($noticesPriority ?? 'INFO')
);

if (!in_array(
    $noticesPriority,
    ['INFO', 'IMPORTANT', 'URGENT'],
    true
)) {
    $noticesPriority = 'INFO';
}

$executive = $executive ?? [];

$schoolGoals = $schoolGoals
    ?? \App\Config\SchoolGoals::defaults();

$schoolFrequencyToday = $schoolFrequencyToday ?? [];

$occurrenceSummary = $occurrenceSummary ?? [];

$activeNotices = $activeNotices ?? [];

$activeNoticesCount = (int) (
    $activeNoticesCount ?? count($activeNotices)
);

$intelligence = is_array($intelligence ?? null) ? $intelligence : [];
$schoolCalendar = is_array($schoolCalendar ?? null) ? $schoolCalendar : [];

?>

<?php component('dashboard/favorites-panel', ['favorites' => $favorites ?? []]); ?>

<?php if (!empty($activeNotices)): ?>

    <?php component('dashboard/notices-panel', [
        'notices' => $activeNotices,
        'totalActive' => $activeNoticesCount,
        'priority' => $noticesPriority,
    ]); ?>

<?php endif; ?>

<?php if (!empty($intelligence['insights'])): ?>

    <?php component('intelligence/insights-panel', [
        'insights' => $intelligence['insights'],
        'compact' => true,
        'actionOriented' => true,
        'title' => 'O que merece atenção hoje',
        'subtitle' => 'Prioridades organizadas automaticamente para orientar as ações da gestão escolar.',
        'collapsible' => true,
        'collapsedByDefault' => false,
        'storageKey' => 'sfe.dashboard.attentionToday.collapsed',
    ]); ?>

<?php endif; ?>

<?php component('dashboard/executive-panel', array_merge($executive, ['schoolGoals' => $schoolGoals, 'schoolIndex' => $schoolIndex ?? []])); ?>


<?php component('dashboard/occurrence-panel', [
    'summary' => $occurrenceSummary,
]); ?>

<div class="dashboard-main-grid">

    <section class="dashboard-left">

        <?php component('dashboard/ranking', [
            'ranking' => $ranking ?? [],

            'generalPercentage' =>
                $executive['generalPercentage']
                ?? $generalPercentage
                ?? 0,

            'totalStudents' => $totalStudents ?? 0,

            'totalClasses' => $totalClasses ?? 0,

            'presentes' =>
                $schoolFrequencyToday['presentes'] ?? 0,

            'faltas' =>
                $schoolFrequencyToday['faltas'] ?? 0,
            'goalPercentage' => $schoolGoals['frequency_goal'] ?? 95,
        ]); ?>

    </section>

    <aside class="dashboard-right">

        <?php component('dashboard/indicators', [
            'today' => $schoolFrequencyToday,

            'week' => $schoolFrequencyWeek ?? [],

            'month' => $schoolFrequencyMonth ?? [],

            'year' => $schoolFrequencyYear ?? [],
            'goalPercentage' => $schoolGoals['frequency_goal'] ?? 95,
        ]); ?>

    </aside>

</div>

<div class="dashboard-bottom-grid">

    <section>

        <?php component('dashboard/attendance-chart', [
            'frequencyLast30Days' =>
                $frequencyLast30Days ?? [],

            'goalPercentage' =>
                $schoolGoals['frequency_goal'] ?? 95,
            'frequencyPeriod' => $frequencyPeriod ?? '30d',
        ]); ?>

    </section>

    <section>

        <?php component('dashboard/pending', [
            'classesWithoutAttendance' =>
                $classesWithoutAttendance ?? [],
        ]); ?>

    </section>

</div>
<?php component('dashboard/frequency-heatmap', ['heatmap' => $frequencyHeatmap ?? [], 'goalPercentage' => $schoolGoals['frequency_goal'] ?? 95]); ?>

<?php component('dashboard/school-calendar', ['calendar' => $schoolCalendar]); ?>
