<?php

$totalToday = (int) (
    $totalToday ?? 0
);

$totalOpen = (int) (
    $totalOpen ?? 0
);

$totalResolved = (int) (
    $totalResolved ?? 0
);

$totalCurrentMonth = (int) (
    $totalCurrentMonth ?? 0
);

$occurrenceIndicators =
    $occurrenceIndicators ?? [];

$mostFrequentTypes =
    $mostFrequentTypes ?? [];

$studentsRanking =
    $studentsRanking ?? [];

$classesRanking =
    $classesRanking ?? [];

$subjectsRanking =
    $subjectsRanking ?? [];

$recentOccurrences =
    $recentOccurrences ?? [];

$occurrenceTypes =
    $occurrenceTypes ?? [];

$occurrenceSeverities =
    $occurrenceSeverities ?? [];

$occurrenceSeverityClasses =
    $occurrenceSeverityClasses ?? [];

$occurrenceSeverityIcons =
    $occurrenceSeverityIcons ?? [];

$occurrenceStatuses =
    $occurrenceStatuses ?? [];

$occurrenceFilters =
    $occurrenceFilters ?? [];

$filterClasses =
    $filterClasses ?? [];

$filterSubjects =
    $filterSubjects ?? [];

$occurrenceCalendar =
    $occurrenceCalendar ?? [];

$calendarYear = (int) (
    $calendarYear ?? date('Y')
);

$calendarMonth = (int) (
    $calendarMonth ?? date('n')
);

$selectedDate = (string) (
    $selectedDate ?? ''
);

$selectedDateOccurrences =
    $selectedDateOccurrences ?? [];

$recurrence = is_array($recurrence ?? null) ? $recurrence : [];

component('base/page-header', [
    'title' => 'Ocorrências',

    'subtitle' =>
        'Acompanhe os registros, situações e indicadores da escola.',
]);

?>

<div class="occurrences-dashboard">

    <?php component(
        'occurrences/dashboard/cards',
        [
            'totalToday' =>
                $totalToday,

            'totalOpen' =>
                $totalOpen,

            'totalResolved' =>
                $totalResolved,

            'totalCurrentMonth' =>
                $totalCurrentMonth,
        ]
    ); ?>

    <?php component(
        'occurrences/dashboard/indicators',
        [
            'indicators' =>
                $occurrenceIndicators,
        ]
    ); ?>

    <?php component('dashboard/recurrence-card', [
        'recurrence' => $recurrence,
        'context' => 'occurrences',
    ]); ?>

    <?php component(
        'occurrences/dashboard/calendar',
        [
            'calendar' =>
                $occurrenceCalendar,

            'calendarYear' =>
                $calendarYear,

            'calendarMonth' =>
                $calendarMonth,

            'selectedDate' =>
                $selectedDate,

            'selectedDateOccurrences' =>
                $selectedDateOccurrences,

            'occurrenceTypes' =>
                $occurrenceTypes,

            /*
             * Os níveis de gravidade serão usados
             * somente na lista do dia selecionado.
             *
             * Os quadrados do calendário continuarão
             * exibindo apenas a quantidade de ocorrências.
             */
            'occurrenceSeverities' =>
                $occurrenceSeverities,

            'occurrenceSeverityClasses' =>
                $occurrenceSeverityClasses,

            'occurrenceSeverityIcons' =>
                $occurrenceSeverityIcons,
        ]
    ); ?>

    <div class="occurrences-dashboard-main-grid">

        <?php component(
            'occurrences/dashboard/types',
            [
                'items' =>
                    $mostFrequentTypes,
            ]
        ); ?>

        <?php component(
            'occurrences/dashboard/students-ranking',
            [
                'students' =>
                    $studentsRanking,
            ]
        ); ?>

    </div>

    <?php component(
        'occurrences/dashboard/classes-ranking',
        [
            'classes' =>
                $classesRanking,
        ]
    ); ?>

    <?php component(
        'occurrences/dashboard/subjects-ranking',
        [
            'subjects' =>
                $subjectsRanking,
        ]
    ); ?>

    <?php component(
        'occurrences/dashboard/recent',
        [
            'occurrences' =>
                $recentOccurrences,

            'occurrenceTypes' =>
                $occurrenceTypes,

            'occurrenceSeverities' =>
                $occurrenceSeverities,

            'occurrenceSeverityClasses' =>
                $occurrenceSeverityClasses,

            'occurrenceSeverityIcons' =>
                $occurrenceSeverityIcons,

            'occurrenceStatuses' =>
                $occurrenceStatuses,

            'filters' =>
                $occurrenceFilters,

            'classes' =>
                $filterClasses,

            'subjects' =>
                $filterSubjects,
        ]
    ); ?>

</div>