<?php

component('base/page-header', [
    'title' => 'Página Inicial',
    'subtitle' => 'Centro de Operações Escolar',
]);
$academicYear=$academicContext['year']??null;$academicPeriod=$academicContext['period']??null;$academicDays=$academicContext['days']??[];
if($academicYear): ?>
<div class="card" style="margin-bottom:20px">
 <div class="settings-summary">
  <div class="settings-summary-card"><span>Ano letivo</span><strong><?=e($academicYear['name'])?></strong></div>
  <div class="settings-summary-card"><span>Período atual</span><strong><?=e($academicPeriod['name']??'Entre períodos')?></strong></div>
  <div class="settings-summary-card"><span>Dias letivos restantes</span><strong><?=(int)($academicDays['remaining']??0)?></strong></div>
 </div>
</div>
<?php endif;

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
