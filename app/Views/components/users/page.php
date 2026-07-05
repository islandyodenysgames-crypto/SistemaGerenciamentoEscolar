<?php if (!empty($userSuccess)): ?>

    <div class="alert alert-success">
        <?= e($userSuccess) ?>
    </div>

<?php endif; ?>

<?php if (!empty($userError)): ?>

    <div class="alert alert-danger">
        <?= e($userError) ?>
    </div>

<?php endif; ?>

<div class="card">

    <?php component('base/table-header', [
        'title' => 'Usuários cadastrados',
        'actionLabel' => '+ Novo usuário',
        'actionUrl' => base_url('usuarios/novo'),
    ]); ?>

    <?php component('users/table', [
        'users' => $users ?? []
    ]); ?>

</div>