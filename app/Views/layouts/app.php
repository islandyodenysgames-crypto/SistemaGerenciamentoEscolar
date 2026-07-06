<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? app_name() ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="<?= asset('assets/css/design-system/index.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/core/index.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/components/index.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/dashboard/index.css') ?>">
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

    <script src="https://unpkg.com/lucide@latest"></script>

    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

    <script src="<?= asset('assets/js/core/app.js') ?>"></script>
    <script src="<?= asset('assets/js/core/export.js') ?>"></script>
    <script src="<?= asset('assets/js/layout/sidebar.js') ?>"></script>
    <script src="<?= asset('assets/js/core/theme.js') ?>"></script>
    <script src="<?= asset('assets/js/core/notifications.js') ?>"></script>

    <?php $pageScript = page_script(); ?>

    <?php if ($pageScript): ?>

        <script src="<?= asset('assets/js/' . $pageScript) ?>"></script>

    <?php endif; ?>

    <?php

    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $isDashboardPage =
        str_contains($currentPath, '/dashboard') ||
        str_ends_with($currentPath, '/public') ||
        str_ends_with($currentPath, '/public/');

    ?>

    <?php if ($isDashboardPage && $pageScript !== 'pages/dashboard.js'): ?>

        <script src="<?= asset('assets/js/pages/dashboard.js') ?>"></script>

    <?php endif; ?>

</body>

</html>