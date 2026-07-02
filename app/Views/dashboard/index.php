<?php

component('page-header', [

    'title' => 'Dashboard',

    'subtitle' => 'Visão geral do Sistema de Frequência Escolar'

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

    'icon' => 'check-circle',

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