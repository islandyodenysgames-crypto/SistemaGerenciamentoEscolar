<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? 'Sistema de Frequência Escolar' ?></title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="/SistemaFrequenciaEscolar/public/assets/css/main.css">

</head>

<body>

<?php require __DIR__.'/../partials/sidebar.php'; ?>

<div class="app">

<?php require __DIR__.'/../partials/header.php'; ?>

<main class="content">

<?= $content ?>

</main>

<?php require __DIR__.'/../partials/footer.php'; ?>

</div>

<script src="/SistemaFrequenciaEscolar/public/assets/js/app.js"></script>

</body>

</html>