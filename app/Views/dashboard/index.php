<?php

component('page-header', [
    'title' => 'Dashboard',
    'subtitle' => 'Bem-vindo ao Sistema de Frequência Escolar'
]);

?>

<div class="dashboard-grid">

<?php

component('stat-card', [
    'icon' => 'graduation-cap',
    'label' => 'Alunos',
    'value' => 0
]);

component('stat-card', [
    'icon' => 'school',
    'label' => 'Turmas',
    'value' => 0
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

<div class="dashboard-content">

    <?php component('school-frequency-summary', [
        'schoolFrequencyToday' => $schoolFrequencyToday ?? []
    ]); ?>

    <?php component('daily-ranking', [
        'ranking' => $ranking ?? []
    ]); ?>

    <?php component('frequency-chart', [
        'frequencyLast30Days' => $frequencyLast30Days ?? []
    ]); ?>

    <?php component('quick-actions'); ?>

    <?php component('calendar'); ?>

    <?php component('recent-activities'); ?>

</div>