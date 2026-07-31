<?php

component('base/page-header', [
    'title' => 'Novo Aviso',
    'subtitle' => 'Publique um comunicado para a comunidade escolar.',
]);

?>

<?php if (!empty($noticeError)): ?>

    <div class="alert alert-danger">
        <?= e($noticeError) ?>
    </div>

<?php endif; ?>

<div class="card notice-form-card">

    <?php component('notices/form', [
        'notice' => $oldInput ?? [],
        'priorities' => $priorities ?? [],
        'targets' => $targets ?? [],
        'categories' => $categories ?? [],
        'attachments' => $attachments ?? [],
        'classes' => $classes ?? [],
        'formAction' => base_url('avisos'),
        'submitLabel' => 'Publicar aviso',
        'isEdit' => false,
    ]); ?>

</div>