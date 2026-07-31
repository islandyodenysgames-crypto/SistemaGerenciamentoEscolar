<?php

$classes = $classes ?? [];

$presentes = 0;
$faltas = 0;
$justificadas = 0;
$atestados = 0;
$onibus = 0;

foreach ($classes as $class) {
    $presentes += (int) ($class['presentes'] ?? $class['present_count'] ?? 0);
    $faltas += (int) ($class['faltas'] ?? $class['absences'] ?? $class['faltas_count'] ?? 0);
    $justificadas += (int) ($class['justificadas'] ?? $class['justified'] ?? 0);
    $atestados += (int) ($class['atestados'] ?? $class['medical'] ?? 0);
    $onibus += (int) ($class['onibus'] ?? $class['bus'] ?? 0);
}

?>

<?php if (!empty($attendanceSuccess)): ?>

    <div class="alert alert-success">
        <?= e($attendanceSuccess) ?>
    </div>

<?php endif; ?>

<?php if (!empty($attendanceError)): ?>

    <div class="alert alert-danger">
        <?= e($attendanceError) ?>
    </div>

<?php endif; ?>

<div class="attendance-central-v3">

    <section class="attendance-central-main">

        <div class="attendance-kpi-grid">

            <div class="attendance-kpi-card">
                <span>Total de turmas</span>
                <strong><?= (int) ($totalClasses ?? 0) ?></strong>
                <small>Turmas ativas</small>
            </div>

            <div class="attendance-kpi-card attendance-kpi-success">
                <span>Realizadas</span>
                <strong><?= (int) ($doneClasses ?? 0) ?></strong>
                <small>Chamadas feitas</small>
            </div>

            <div class="attendance-kpi-card attendance-kpi-warning">
                <span>Pendentes</span>
                <strong><?= (int) ($pendingClasses ?? 0) ?></strong>
                <small>Aguardando chamada</small>
            </div>

            <div class="attendance-kpi-card attendance-kpi-info">
                <span>Frequência geral</span>
                <strong><?= number_format((float) ($generalPercentage ?? 0), 1, ',', '.') ?>%</strong>
                <small>Hoje</small>
            </div>

        </div>

        <div class="attendance-action-card">

            <div>
                <h3>Turmas de hoje</h3>
                <p>Data: <?= date('d/m/Y', strtotime($today ?? date('Y-m-d'))) ?></p>
            </div>

            <div class="attendance-action-buttons">

                <a href="<?= base_url('frequencia/historico') ?>" class="btn-primary">
                    <i data-lucide="calendar-days"></i>
                    Histórico
                </a>

                <a href="<?= base_url('frequencia/novo') ?>" class="btn-primary">
                    + Iniciar frequência
                </a>

            </div>

        </div>

        <div class="attendance-classes-v3">

            <?php if (empty($classes)): ?>

                <div class="card">
                    <div class="activity-empty">
                        Nenhuma turma ativa cadastrada.
                    </div>
                </div>

            <?php else: ?>

                <?php foreach ($classes as $class): ?>

                    <?php component('attendance/class-card-v2', [
                        'class' => $class
                    ]); ?>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </section>

    <aside class="attendance-summary-panel">

        <h3>Resumo do dia</h3>

        <div class="attendance-summary-list">

            <div>
                <span>🟢 Presentes</span>
                <strong><?= $presentes ?></strong>
            </div>

            <div>
                <span>🔴 Faltas</span>
                <strong><?= $faltas ?></strong>
            </div>

            <div>
                <span>🟠 Justificadas</span>
                <strong><?= $justificadas ?></strong>
            </div>

            <div>
                <span>🔵 Atestados</span>
                <strong><?= $atestados ?></strong>
            </div>

            <div>
                <span>🟣 Ônibus</span>
                <strong><?= $onibus ?></strong>
            </div>

        </div>

    </aside>

</div>