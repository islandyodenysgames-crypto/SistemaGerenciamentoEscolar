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
$summary = $central['summary'] ?? [];
$classes = $central['classes'] ?? [];

$generalPercentage = (float) ($central['generalPercentage'] ?? 0);
$totalClasses = (int) ($central['totalClasses'] ?? 0);
$doneClasses = (int) ($central['doneClasses'] ?? 0);
$pendingClasses = (int) ($central['pendingClasses'] ?? 0);

?>

<style>
.attendance-central-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 24px;
}

.attendance-central-card {
    background: #fff;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
    text-align: center;
}

.attendance-central-card strong {
    display: block;
    font-size: 32px;
    margin-top: 8px;
}

.attendance-classes {
    display: grid;
    gap: 18px;
}

.attendance-class-card {
    background: #fff;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
    display: grid;
    grid-template-columns: 1fr 150px 190px;
    gap: 20px;
    align-items: center;
}

.attendance-class-info h3 {
    margin-bottom: 8px;
}

.attendance-class-numbers {
    margin-top: 12px;
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
}

.attendance-class-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: flex-end;
}

@media (max-width: 900px) {
    .attendance-central-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .attendance-class-card {
        grid-template-columns: 1fr;
    }

    .attendance-class-actions {
        justify-content: flex-start;
    }
}
</style>

<?php if ($attendanceSuccess): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($attendanceSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($attendanceError): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($attendanceError) ?>
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

<div class="card" style="margin-bottom:24px;">

    <div class="table-header">

        <div>
            <h3>Turmas de hoje</h3>
            <p style="margin-top:6px;color:#64748b;">
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