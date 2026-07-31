<?php

$generalPercentage = (float) ($generalPercentage ?? 0);
$doneClasses = (int) ($doneClasses ?? 0);
$pendingClasses = (int) ($pendingClasses ?? 0);
$totalClasses = (int) ($totalClasses ?? 0);
$studentsInAlert = (int) ($studentsInAlert ?? 0);
$frequencyGoal = (float)($frequencyGoal ?? 95);
$attentionThreshold = max(0, $frequencyGoal - 5);

$statusLabel = 'Operação normal';
$statusClass = 'success';

if ($generalPercentage < $attentionThreshold) {
    $statusLabel = 'Situação crítica';
    $statusClass = 'danger';
} elseif ($generalPercentage < $frequencyGoal) {
    $statusLabel = 'Atenção necessária';
    $statusClass = 'warning';
}

?>

<section class="executive-hero">

    <div class="executive-hero-header">

        <div>
            <span class="executive-eyebrow">
                Centro de Operações
            </span>

            <h2>
                Monitoramento diário da frequência escolar
            </h2>

            <p>
                Acompanhe a situação geral da escola em tempo real.
            </p>
        </div>

        <div class="executive-status executive-status-<?= $statusClass ?>">
            <span></span>
            <?= $statusLabel ?>
        </div>

    </div>

    <div class="executive-hero-body">

        <div class="executive-frequency">

            <?php component('base/progress', [
                'percentage' => $generalPercentage,
                'label' => 'Frequência Geral',
                'size' => 175
            ]); ?>

            <div class="executive-frequency-caption">
                Meta diária: igual ou superior a <?= number_format($frequencyGoal, 1, ',', '.') ?>%
            </div>

        </div>

        <div class="executive-summary-grid">

            <a href="<?= base_url('frequencia') ?>" class="executive-summary-card">
                <div class="executive-summary-icon">
                    <i data-lucide="clipboard-check"></i>
                </div>

                <div>
                    <span>Chamadas hoje</span>
                    <strong><?= $doneClasses ?> / <?= $totalClasses ?></strong>
                    <small>turmas registradas</small>
                </div>
            </a>

            <a href="<?= base_url('frequencia') ?>" class="executive-summary-card">
                <div class="executive-summary-icon warning">
                    <i data-lucide="clock-alert"></i>
                </div>

                <div>
                    <span>Pendências</span>
                    <strong><?= $pendingClasses ?></strong>
                    <small>turmas sem chamada</small>
                </div>
            </a>

            <a href="<?= base_url('relatorios') ?>" class="executive-summary-card">
                <div class="executive-summary-icon danger">
                    <i data-lucide="triangle-alert"></i>
                </div>

                <div>
                    <span>Alunos em alerta</span>
                    <strong><?= $studentsInAlert ?></strong>
                    <small>abaixo de <?= number_format($frequencyGoal, 1, ',', '.') ?>%</small>
                </div>
            </a>

        </div>

    </div>

</section>