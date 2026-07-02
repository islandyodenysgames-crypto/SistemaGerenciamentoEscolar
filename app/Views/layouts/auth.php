<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? app_name() ?></title>

    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>

<body class="auth-body">

    <?= $content ?>

    <script src="https://unpkg.com/lucide@latest"></script>

    <script>
        lucide.createIcons();
    </script>

</body>

</html>