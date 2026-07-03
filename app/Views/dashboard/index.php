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

    'label' => 'Presentes',

    'value' => 0

]);

component('stat-card', [

    'icon' => 'circle-x',

    'label' => 'Faltas',

    'value' => 0

]);

?>

</div>

<div class="dashboard-content">

    <?php component('dashboard-chart'); ?>

    <?php component('quick-actions'); ?>

    <?php component('daily-ranking', [
        'ranking' => $ranking ?? []
    ]); ?>

    <?php component('calendar'); ?>

    <?php component('recent-activities'); ?>

</div>