<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? app_name() ?></title>

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

    <!-- ======================================================
         BASE
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/variables.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/themes.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/app.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/layout.css') ?>">

    <!-- ======================================================
         COMPONENTES
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components/badge.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components/button.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components/cards.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components/content.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components/form.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components/header.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components/stat-card.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components/tables.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components.css') ?>">

    <!-- ======================================================
         PÁGINAS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/pages/dashboard.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/pages/frequencia.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/pages/alunos.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/pages/turmas.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/pages/users.css') ?>">

    <!-- ======================================================
         UTILITÁRIOS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/utilities.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/responsive.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/animations.css') ?>">

</head>

<body>

    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="app">

        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">

            <?= $content ?>

        </main>

    </div>

    <script src="<?= base_url('assets/js/app.js') ?>"></script>

    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>

    <script src="<?= base_url('assets/js/theme.js') ?>"></script>

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>

</body>

</html>