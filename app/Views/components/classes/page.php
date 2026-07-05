<?php if (!empty($classSuccess)): ?>

    <div class="alert alert-success">
        <?= e($classSuccess) ?>
    </div>

<?php endif; ?>

<?php if (!empty($classError)): ?>

    <div class="alert alert-danger">
        <?= e($classError) ?>
    </div>

<?php endif; ?>

<div class="card">

    <?php component('base/table-header', [
        'title' => 'Turmas cadastradas',
        'actionLabel' => '+ Nova turma',
        'actionUrl' => base_url('turmas/novo'),
    ]); ?>

    <?php component('classes/table', [
        'classes' => $classes ?? []
    ]); ?>

</div>