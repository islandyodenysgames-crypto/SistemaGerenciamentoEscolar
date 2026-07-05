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

?>

<section class="manager-panel">

    <header class="manager-panel-header">

        <div class="manager-panel-icon">
            <i data-lucide="clipboard-list"></i>
        </div>

        <div>
            <h2>Painel do Gestor</h2>

            <p>
                Resumo executivo da frequência escolar
            </p>
        </div>

    </header>

    <div class="manager-panel-grid">

        <?php component('dashboard/executive-card', [
            'url' => base_url('relatorios'),
            'color' => 'green',
            'icon' => 'chart-no-axes-combined',
            'title' => 'Frequência Geral',
            'tooltip' => 'Percentual geral de frequência registrado nas chamadas do dia.',
            'percentage' => $generalPercentage,
            'value' => number_format($generalPercentage, 1, ',', '.') . '%',
            'progressLabel' => 'Hoje',
            'progressColor' => 'var(--success)',
            'status' => $generalPercentage >= 95
                ? 'Excelente'
                : ($generalPercentage >= 90 ? 'Atenção' : 'Crítico'),
            'meta' => 'Meta da escola: ≥ ' . number_format($frequencyGoal, 0, ',', '.') . '%',
            'actionIcon' => 'file-text',
            'actionLabel' => 'Ver relatório',
        ]); ?>

        <?php component('dashboard/executive-card', [
            'url' => base_url('frequencia'),
            'color' => 'yellow',
            'icon' => 'school',
            'title' => 'Turmas Pendentes',
            'tooltip' => 'Turmas que ainda não registraram frequência hoje.',
            'percentage' => $pendingPercentage,
            'value' => (string) $pendingClasses,
            'progressLabel' => 'de ' . $totalClasses,
            'progressColor' => 'var(--warning)',
            'status' => $pendingClasses === 0
                ? 'Concluído'
                : ($pendingClasses <= 2 ? 'Atenção' : 'Crítico'),
            'meta' => number_format($pendingPercentage, 0, ',', '.') . '% das turmas',
            'actionIcon' => 'clipboard-list',
            'actionLabel' => 'Abrir Central',
        ]); ?>

        <?php component('dashboard/executive-card', [
            'url' => base_url('relatorios'),
            'color' => 'red',
            'icon' => 'triangle-alert',
            'title' => 'Alunos em Alerta',
            'tooltip' => 'Quantidade de alunos com frequência abaixo de 85%.',
            'percentage' => min(100, $studentsInAlert * 10),
            'value' => (string) $studentsInAlert,
            'progressLabel' => 'Alunos',
            'progressColor' => 'var(--danger)',
            'status' => $studentsInAlert === 0
                ? 'Estável'
                : ($studentsInAlert <= 10 ? 'Atenção' : 'Crítico'),
            'meta' => 'Frequência inferior a 85%',
            'actionIcon' => 'users',
            'actionLabel' => 'Ver alunos',
        ]); ?>

        <?php component('dashboard/executive-card', [
            'url' => base_url('frequencia'),
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

    <?php component('dashboard/school-objective', [
        'title' => 'Objetivo da Escola',
        'description' => 'Manter a frequência geral acima de 95%, registrar todas as chamadas diariamente e reduzir o número de alunos em situação de alerta.',
        'icon' => 'trophy',
    ]); ?>

</section>