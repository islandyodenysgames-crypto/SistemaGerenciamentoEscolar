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
         DESIGN SYSTEM
    ======================================================= -->

    <link rel="stylesheet" href="<?= asset('assets/css/design-system/index.css') ?>">

    <!-- ======================================================
         CORE
    ======================================================= -->

    <link rel="stylesheet" href="<?= asset('assets/css/core/index.css') ?>">

    <!-- ======================================================
         COMPONENTES
    ======================================================= -->

    <link rel="stylesheet" href="<?= asset('assets/css/components/index.css') ?>">

    <!-- ======================================================
         DASHBOARD
    ======================================================= -->

    <link rel="stylesheet" href="<?= asset('assets/css/dashboard/index.css') ?>">

    <!-- ======================================================
         PÁGINAS
    ======================================================= -->

    <link rel="stylesheet" href="<?= asset('assets/css/pages/index.css') ?>">

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
         LUCIDE
    ======================================================= -->

    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- ======================================================
         JAVASCRIPT GLOBAL
    ======================================================= -->

    <script src="<?= asset('assets/js/core/app.js') ?>"></script>
    <script src="<?= asset('assets/js/layout/sidebar.js') ?>"></script>
    <script src="<?= asset('assets/js/core/theme.js') ?>"></script>
    <script src="<?= asset('assets/js/core/notifications.js') ?>"></script>

    <!-- ======================================================
         JAVASCRIPT DA PÁGINA
    ======================================================= -->

    <?php if ($pageScript = page_script()): ?>

        <script src="<?= asset('assets/js/' . $pageScript) ?>"></script>

    <?php endif; ?>

</body>

</html>