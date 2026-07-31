<?php

component('base/page-header', [
    'title' => 'Busca Inteligente',
    'subtitle' => 'Pesquise alunos, turmas, ocorrências, acompanhamentos, avisos e usuários'
]);

component('search/page', [
    'term' => $term ?? '',
    'results' => $results ?? [],
]);

?>