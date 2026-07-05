<?php

component('base/alert', [
    'type' => 'success',
    'message' => $studentSuccess ?? null
]);

component('base/alert', [
    'type' => 'danger',
    'message' => $studentError ?? null
]);

?>

<div class="card">

    <?php component('base/table-header', [
        'title' => 'Alunos cadastrados',
        'actionLabel' => '+ Novo aluno',
        'actionUrl' => base_url('alunos/novo'),
    ]); ?>

    <?php component('students/table', [
        'students' => $students ?? []
    ]); ?>

</div>