<?php

component('page-header', [
    'title' => 'Central de Frequência',
    'subtitle' => 'Acompanhe e registre as chamadas das turmas no dia'
]);

$attendanceSuccess = \App\Core\Session::get('attendance_success');
$attendanceError = \App\Core\Session::get('attendance_error');

\App\Core\Session::remove('attendance_success');
\App\Core\Session::remove('attendance_error');

$central = $central ?? [];
$classes = $central['classes'] ?? [];

$generalPercentage = (float) ($central['generalPercentage'] ?? 0);
$totalClasses = (int) ($central['totalClasses'] ?? 0);
$doneClasses = (int) ($central['doneClasses'] ?? 0);
$pendingClasses = (int) ($central['pendingClasses'] ?? 0);

?>

<?php if ($attendanceSuccess): ?>
    <div class="alert alert-success">
        <?= e($attendanceSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($attendanceError): ?>
    <div class="alert alert-danger">
        <?= e($attendanceError) ?>
    </div>
<?php endif; ?>

<div class="attendance-central-grid">

    <div class="attendance-central-card">
        <span>Total de turmas</span>
        <strong><?= $totalClasses ?></strong>
    </div>

    <div class="attendance-central-card">
        <span>Realizadas hoje</span>
        <strong><?= $doneClasses ?></strong>
    </div>

    <div class="attendance-central-card">
        <span>Pendentes hoje</span>
        <strong><?= $pendingClasses ?></strong>
    </div>

    <div class="attendance-central-card">
        <?php component('attendance-progress-circle', [
            'percentage' => $generalPercentage,
            'label' => 'Geral',
            'size' => 130
        ]); ?>
    </div>

</div>

<div class="card mb-24">

    <div class="table-header">

        <div>
            <h3>Turmas de hoje</h3>

            <p class="text-muted mt-8">
                Data: <?= date('d/m/Y', strtotime($today ?? date('Y-m-d'))) ?>
            </p>
        </div>

        <a href="<?= base_url('frequencia/novo') ?>" class="btn-primary">
            + Iniciar frequência
        </a>

    </div>

</div>

<div class="attendance-classes">

    <?php if (empty($classes)): ?>

        <div class="card">
            <div class="activity-empty">
                Nenhuma turma ativa cadastrada.
            </div>
        </div>

    <?php else: ?>

        <?php foreach ($classes as $class): ?>

            <?php component('attendance-class-card', [
                'class' => $class
            ]); ?>

        <?php endforeach; ?>

    <?php endif; ?>

</div>