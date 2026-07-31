<?php

$generalPercentage = (float) ($generalPercentage ?? 0);
$doneClasses = (int) ($doneClasses ?? 0);
$pendingClasses = (int) ($pendingClasses ?? 0);
$totalClasses = (int) ($totalClasses ?? 0);
$studentsInAlert = (int) ($studentsInAlert ?? 0);

$donePercentage = $totalClasses > 0
    ? round(($doneClasses / $totalClasses) * 100, 1)
    : 0;

$pendingPercentage = $totalClasses > 0
    ? round(($pendingClasses / $totalClasses) * 100, 1)
    : 0;

$schoolGoals = $schoolGoals ?? \App\Config\SchoolGoals::defaults();

$frequencyGoal = (float) ($schoolGoals['frequency_goal'] ?? 95);
$attentionThreshold = max(0, $frequencyGoal - 5);

?>

<section class="manager-panel">

    <header class="manager-panel-header">

        <div class="manager-panel-icon">
            <i data-lucide="clipboard-list"></i>
        </div>

        <div>
            <h2>Painel do Gestor</h2>

            <p>
                Situação operacional de hoje
            </p>
        </div>

    </header>

    <div class="manager-panel-grid">

        <?php component('dashboard/executive-card', [
            'url' => base_url('relatorios/diario?data=' . date('Y-m-d')),
            'color' => 'green',
            'icon' => 'chart-no-axes-combined',
            'title' => 'Frequência Geral',
            'tooltip' => 'Percentual geral de frequência registrado nas chamadas do dia.',
            'percentage' => $generalPercentage,
            'value' => number_format($generalPercentage, 1, ',', '.') . '%',
            'progressLabel' => 'Hoje',
            'progressColor' => 'var(--success)',
            'status' => $generalPercentage >= $frequencyGoal
                ? 'Excelente'
                : ($generalPercentage >= $attentionThreshold ? 'Atenção' : 'Crítico'),
            'meta' => 'Meta da escola: ≥ ' . number_format($frequencyGoal, 0, ',', '.') . '%',
            'actionIcon' => 'file-text',
            'actionLabel' => 'Ver relatório',
        ]); ?>

        <?php component('dashboard/school-index-card', ['index' => $schoolIndex ?? []]); ?>

        <?php component('dashboard/executive-card', [
            'url' => base_url('inteligencia/casos?tipo=low_attendance'),
            'color' => 'red',
            'icon' => 'triangle-alert',
            'title' => 'Alunos em Alerta',
            'tooltip' => 'Quantidade de alunos com frequência abaixo da meta de <?= number_format($frequencyGoal, 1, ',', '.') ?>%.',
            'percentage' => min(100, $studentsInAlert * 10),
            'value' => (string) $studentsInAlert,
            'progressLabel' => 'Alunos',
            'progressColor' => 'var(--danger)',
            'status' => $studentsInAlert === 0
                ? 'Estável'
                : ($studentsInAlert <= 10 ? 'Atenção' : 'Crítico'),
            'meta' => 'Frequência inferior a ' . number_format($frequencyGoal, 1, ',', '.') . '%',
            'actionIcon' => 'users',
            'actionLabel' => 'Ver alunos',
        ]); ?>

        <?php component('dashboard/executive-card', [
            'url' => base_url('frequencia/historico'),
            'color' => 'blue',
            'icon' => 'clipboard-check',
            'title' => 'Chamadas Hoje',
            'tooltip' => 'Quantidade de chamadas registradas nas turmas.',
            'percentage' => $donePercentage,
            'value' => $doneClasses . '/' . $totalClasses,
            'progressLabel' => number_format($donePercentage, 0, ',', '.') . '%',
            'progressColor' => 'var(--info)',
            'status' => $doneClasses >= $totalClasses && $totalClasses > 0
                ? 'Concluído'
                : 'Em andamento',
            'meta' => 'Chamadas concluídas hoje',
            'actionIcon' => 'calendar-check',
            'actionLabel' => 'Ver chamadas',
        ]); ?>

    </div>

    <?php component('dashboard/school-index-details', ['index' => $schoolIndex ?? []]); ?>

</section>