<?php

component('base/page-header', [
    'title' => 'Página Inicial',
    'subtitle' => 'Centro de Operações Escolar',
]);

component('dashboard/page', [

    /*
     * Painel executivo
     */
    'executive' => $executive ?? [],

    /*
     * Dados gerais
     */
    'totalStudents' => $totalStudents ?? 0,
    'totalClasses' => $totalClasses ?? 0,

    /*
     * Frequência
     */
    'schoolFrequencyToday' => $schoolFrequencyToday ?? [],
    'schoolFrequencyWeek' => $schoolFrequencyWeek ?? [],
    'schoolFrequencyMonth' => $schoolFrequencyMonth ?? [],
    'schoolFrequencyYear' => $schoolFrequencyYear ?? [],

    'frequencyLast30Days' => $frequencyLast30Days ?? [],
    'frequencyPeriod' => $frequencyPeriod ?? '30d',
    'frequencyHeatmap' => $frequencyHeatmap ?? [],

    'classesWithoutAttendance' => $classesWithoutAttendance ?? [],

    'ranking' => $ranking ?? [],

    /*
     * Ocorrências
     */
    'occurrenceSummary' => $occurrenceSummary ?? [],

    'occurrenceTypes' => $occurrenceTypes ?? [],

    'occurrenceStudentsRanking' =>
        $occurrenceStudentsRanking ?? [],

    'occurrenceClassesRanking' =>
        $occurrenceClassesRanking ?? [],

    'recentOccurrences' => $recentOccurrences ?? [],

    /*
     * Avisos da Gestão
     */
    'activeNotices' => $activeNotices ?? [],

    'activeNoticesCount' =>
        $activeNoticesCount ?? 0,
    
    'noticesPriority' => $noticesPriority ?? 'INFO',

    'schoolCalendar' => $schoolCalendar ?? [],

    /*
     * Inteligência escolar
     */
    'intelligence' => $intelligence ?? [],
    'favorites' => $favorites ?? [],
    'schoolGoals' => $schoolGoals ?? [],
    'schoolIndex' => $schoolIndex ?? [],

]);

?>