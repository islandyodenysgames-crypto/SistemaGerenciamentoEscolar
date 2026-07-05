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

<div class="attendance-central-grid">

    <div class="attendance-central-card">
        <span>Total de turmas</span>
        <strong><?= (int) ($totalClasses ?? 0) ?></strong>
    </div>

    <div class="attendance-central-card">
        <span>Realizadas hoje</span>
        <strong><?= (int) ($doneClasses ?? 0) ?></strong>
    </div>

    <div class="attendance-central-card">
        <span>Pendentes hoje</span>
        <strong><?= (int) ($pendingClasses ?? 0) ?></strong>
    </div>

    <div class="attendance-central-card">

        <?php component('base/progress', [
            'percentage' => (float) ($generalPercentage ?? 0),
            'label' => 'Geral',
            'size' => 130
        ]); ?>

    </div>

</div>

<div class="card mb-24">

    <?php component('base/table-header', [
        'title' => 'Turmas de hoje',
        'subtitle' => 'Data: ' . date('d/m/Y', strtotime($today ?? date('Y-m-d'))),
        'actionLabel' => '+ Iniciar frequência',
        'actionUrl' => base_url('frequencia/novo'),
    ]); ?>

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

            <?php component('attendance/class-card-v2', [
                'class' => $class
            ]); ?>

        <?php endforeach; ?>

    <?php endif; ?>

</div>