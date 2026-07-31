<?php

declare(strict_types=1);

component('base/page-header', [
    'title' =>
        'Nova Disciplina',

    'subtitle' =>
        'Cadastre uma nova disciplina para vincular aos professores e às ocorrências.',
]);

component('subjects/form', [
    'mode' =>
        'create',

    'subject' =>
        $oldInput ?? [],

    'subjectError' =>
        $subjectError ?? null,
]);