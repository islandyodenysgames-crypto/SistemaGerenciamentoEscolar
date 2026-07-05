<?php

$classSuccess = \App\Core\Session::get('class_success');
$classError = \App\Core\Session::get('class_error');

\App\Core\Session::remove('class_success');
\App\Core\Session::remove('class_error');

component('base/page-header', [
    'title' => 'Turmas',
    'subtitle' => 'Gerencie as turmas cadastradas no Sistema de Frequência Escolar'
]);

component('classes/page', [
    'classSuccess' => $classSuccess,
    'classError' => $classError,
    'classes' => $classes ?? [],
]);

?>