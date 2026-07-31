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

    <div class="table-header">

        <div>
            <h3>Turmas cadastradas</h3>
            <p>Esta área foi integrada ao menu Turmas.</p>
        </div>

        <div class="table-actions">
            <a href="<?= base_url('alunos') ?>" class="btn-secondary">
                Voltar para Turmas
            </a>

            <a href="<?= base_url('turmas/novo') ?>" class="btn-primary">
                + Nova turma
            </a>
        </div>

    </div>

    <?php component('classes/table', [
        'classes' => $classes ?? []
    ]); ?>

</div>