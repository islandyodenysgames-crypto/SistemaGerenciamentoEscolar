<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title><?= $title ?? app_name() ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <!-- CSS da aplicação -->
    <link rel="stylesheet"
          href="<?= base_url('assets/css/app.css') ?>">

</head>

<body>

    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="app">

        <?php require __DIR__ . '/../partials/header.php'; ?>

        <main class="content">

            <?= $content ?>

        </main>

        <?php require __DIR__ . '/../partials/footer.php'; ?>

    </div>

    <!-- JavaScript da aplicação -->
    <script src="<?= base_url('assets/js/app.js') ?>"></script>

    <!-- Controle da Sidebar -->
    <script src="<?= base_url('assets/js/sidebar.js') ?>"></script>

    <!-- Futuro Dark Mode -->
    <script src="<?= base_url('assets/js/theme.js') ?>"></script>

    <!-- Biblioteca de ícones -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        lucide.createIcons();
    </script>

</body>

</html>