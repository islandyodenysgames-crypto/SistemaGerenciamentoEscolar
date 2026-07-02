<?php

component('page-title', [
    'title' => 'Dashboard',
    'subtitle' => 'Bem-vindo ao Sistema de Frequência Escolar'
]);

?>

<div class="dashboard-grid">

<?php

component('stat-card', [
    'icon' => '👨‍🎓',
    'label' => 'Alunos',
    'value' => '0'
]);

component('stat-card', [
    'icon' => '🏫',
    'label' => 'Turmas',
    'value' => '0'
]);

component('stat-card', [
    'icon' => '✅',
    'label' => 'Presentes',
    'value' => '0'
]);

component('stat-card', [
    'icon' => '❌',
    'label' => 'Faltas',
    'value' => '0'
]);

?>

</div>