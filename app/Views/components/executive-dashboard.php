<?php

$generalPercentage = (float) ($generalPercentage ?? 0);
$doneClasses = (int) ($doneClasses ?? 0);
$pendingClasses = (int) ($pendingClasses ?? 0);
$totalClasses = (int) ($totalClasses ?? 0);
$studentsInAlert = (int) ($studentsInAlert ?? 0);

$frequencyColor = $generalPercentage >= 95
    ? 'green'
    : ($generalPercentage >= 90 ? 'yellow' : 'red');

$pendingColor = $pendingClasses === 0
    ? 'green'
    : ($pendingClasses <= 2 ? 'yellow' : 'red');

$alertColor = $studentsInAlert === 0
    ? 'green'
    : ($studentsInAlert <= 10 ? 'yellow' : 'red');

?>

<div class="card executive-panel">

    <div class="card-header">
        <h3>Painel do Gestor</h3>
    </div>

    <div class="executive-grid">

        <a
            href="<?= base_url('relatorios') ?>"
            class="executive-card executive-<?= $frequencyColor ?>"
        >
            <div class="executive-card-header">
                <span>📊</span>
                <strong>Frequência Geral</strong>
            </div>

            <?php component('attendance-progress-circle', [
                'percentage' => $generalPercentage,
                'label' => 'Hoje',
                'size' => 145
            ]); ?>

            <div class="executive-footer">
                Meta diária: ≥ 95%
            </div>
        </a>

        <?php component('metric-card', [
            'icon' => '🏫',
            'title' => 'Turmas Pendentes',
            'value' => $pendingClasses,
            'subtitle' => 'de ' . $totalClasses . ' turmas',
            'description' => 'Aguardando chamada',
            'color' => $pendingColor,
            'url' => base_url('frequencia'),
        ]); ?>

        <?php component('metric-card', [
            'icon' => '⚠️',
            'title' => 'Alunos em Alerta',
            'value' => $studentsInAlert,
            'subtitle' => 'abaixo de 85%',
            'description' => 'Necessitam acompanhamento',
            'color' => $alertColor,
            'url' => base_url('relatorios'),
        ]); ?>

        <?php component('metric-card', [
            'icon' => '📝',
            'title' => 'Chamadas Hoje',
            'value' => $doneClasses . ' / ' . $totalClasses,
            'subtitle' => 'realizadas',
            'description' => 'Frequências registradas hoje',
            'color' => 'blue',
            'url' => base_url('frequencia'),
        ]); ?>

    </div>

</div>