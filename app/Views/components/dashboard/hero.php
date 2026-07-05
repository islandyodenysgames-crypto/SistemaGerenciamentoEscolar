<?php

$generalPercentage = (float) ($generalPercentage ?? 0);
$doneClasses = (int) ($doneClasses ?? 0);
$pendingClasses = (int) ($pendingClasses ?? 0);
$totalClasses = (int) ($totalClasses ?? 0);
$studentsInAlert = (int) ($studentsInAlert ?? 0);

$statusLabel = 'Operação normal';
$statusClass = 'success';

if ($generalPercentage < 90) {
    $statusLabel = 'Situação crítica';
    $statusClass = 'danger';
} elseif ($generalPercentage < 95) {
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
                Meta diária: igual ou superior a 95%
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
                    <small>abaixo de 85%</small>
                </div>
            </a>

        </div>

    </div>

</section>