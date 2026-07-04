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
        href="<?= asset('assets/css/variables.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/themes.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/app.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/layout.css') ?>">

    <!-- ======================================================
         COMPONENTES
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components/badge.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components/button.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components/cards.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components/content.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components/form.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components/header.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components/stat-card.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components/tables.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/components.css') ?>">

    <!-- ======================================================
         PÁGINAS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/pages/dashboard.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/pages/frequencia.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/pages/alunos.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/pages/turmas.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/pages/users.css') ?>">

    <!-- ======================================================
         UTILITÁRIOS
    ======================================================= -->

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/utilities.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/responsive.css') ?>">

    <link
        rel="stylesheet"
        href="<?= asset('assets/css/animations.css') ?>">

</head>

<body>

    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="app">

        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">

            <?= $content ?>

        </main>

    </div>

    <!-- ======================================================
         JAVASCRIPT GLOBAL
    ======================================================= -->

    <script src="<?= asset('assets/js/app.js') ?>"></script>

    <script src="<?= asset('assets/js/sidebar.js') ?>"></script>

    <script src="<?= asset('assets/js/theme.js') ?>"></script>

    <!-- ======================================================
         JAVASCRIPT DA PÁGINA
    ======================================================= -->

    <?php if ($pageScript = page_script()): ?>

        <script src="<?= asset('assets/js/' . $pageScript) ?>"></script>

    <?php endif; ?>

    <!-- ======================================================
         LUCIDE ICONS
    ======================================================= -->

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>

</body>

</html>