<?php

declare(strict_types=1);

use App\Core\Session;

$subjectSuccess = Session::get(
    'subject_success'
);

$subjectError = Session::get(
    'subject_error'
);

Session::remove(
    'subject_success'
);

Session::remove(
    'subject_error'
);

component('base/page-header', [
    'title' =>
        'Disciplinas',

    'subtitle' =>
        'Gerencie as disciplinas cadastradas no sistema.',
]);

component('subjects/page', [
    'subjects' =>
        $subjects ?? [],

    'subjectSuccess' =>
        $subjectSuccess,

    'subjectError' =>
        $subjectError,
]);