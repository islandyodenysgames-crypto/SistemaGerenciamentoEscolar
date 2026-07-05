<?php if (!empty($enrollmentSuccess)): ?>

    <div class="alert alert-success">
        <?= e($enrollmentSuccess) ?>
    </div>

<?php endif; ?>

<?php if (!empty($enrollmentError)): ?>

    <div class="alert alert-danger">
        <?= e($enrollmentError) ?>
    </div>

<?php endif; ?>

<div class="card">

    <?php component('base/table-header', [
        'title' => 'Matrículas cadastradas',
        'actionLabel' => '+ Nova matrícula',
        'actionUrl' => base_url('matriculas/novo'),
    ]); ?>

    <?php component('enrollments/table', [
        'enrollments' => $enrollments ?? []
    ]); ?>

</div>