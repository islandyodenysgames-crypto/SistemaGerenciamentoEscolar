<?php

component('base/page-header', [
    'title' => 'Editar Aviso',
    'subtitle' => 'Atualize as informações do comunicado.',
]);

?>

<?php if (!empty($noticeError)): ?>

    <div class="alert alert-danger">
        <?= e($noticeError) ?>
    </div>

<?php endif; ?>

<div class="card notice-form-card">

    <?php component('notices/form', [
        'notice' => $notice ?? [],
        'priorities' => $priorities ?? [],
        'targets' => $targets ?? [],
        'categories' => $categories ?? [],
        'attachments' => $attachments ?? [],
        'classes' => $classes ?? [],
        'formAction' => base_url('avisos/editar'),
        'submitLabel' => 'Salvar alterações',
        'isEdit' => true,
    ]); ?>

</div>