<?php

$userSuccess = \App\Core\Session::get('user_success');
$userError = \App\Core\Session::get('user_error');

\App\Core\Session::remove('user_success');
\App\Core\Session::remove('user_error');

component('base/page-header', [
    'title' => 'Usuários',
    'subtitle' => 'Gerencie os usuários do Sistema de Frequência Escolar'
]);

component('users/page', [
    'userSuccess' => $userSuccess,
    'userError' => $userError,
    'users' => $users ?? [],
]);

?>