<?php

component('base/page-header', [
    'title' => 'Busca Inteligente',
    'subtitle' => 'Pesquise alunos, turmas e datas de frequência'
]);

component('search/page', [
    'term' => $term ?? '',
    'results' => $results ?? [],
]);

?>