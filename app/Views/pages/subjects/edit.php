<?php

declare(strict_types=1);

component('base/page-header', [
    'title' =>
        'Editar Disciplina',

    'subtitle' =>
        'Atualize os dados da disciplina selecionada.',
]);

component('subjects/form', [
    'mode' =>
        'edit',

    'subject' =>
        $subject ?? [],

    'subjectError' =>
        $subjectError ?? null,
]);