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

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/app.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/themes.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/cards.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/components.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/dashboard.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/forms.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/tables.css') ?>">

    <link
        rel="stylesheet"
        href="<?= base_url('assets/css/attendance.css') ?>">

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
        lucide.createIcons();
    </script>

</body>

</html>