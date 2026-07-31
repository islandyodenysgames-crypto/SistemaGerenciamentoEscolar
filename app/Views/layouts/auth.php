<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? app_name() ?></title>
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('sge-theme');
                var theme = saved === 'dark' || saved === 'light'
                    ? saved
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.dataset.theme = theme;
                document.documentElement.classList.add(theme === 'dark' ? 'theme-dark' : 'theme-light');
                document.documentElement.style.colorScheme = theme;
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/design-system/index.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/core/index.css?v=7') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/pages/auth.css?v=7') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/core/dark-audit.css?v=2') ?>">
</head>
<body class="auth-body">
    <?= $content ?>
    <script src="<?= asset('assets/js/vendor/lucide-local.js') ?>"></script>
    <script src="<?= asset('assets/js/core/theme.js?v=3') ?>"></script>
    <script src="<?= asset('assets/js/core/app.js') ?>"></script>
</body>
</html>
