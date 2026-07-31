<!DOCTYPE html>
<html lang="pt-BR" data-tv-theme="<?= e((string)($tvConfig['theme'] ?? 'light')) ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title><?= e((string)($title ?? 'Painel TV')) ?></title>
<link rel="stylesheet" href="<?= asset('assets/css/pages/tv-panel.css?v=6') ?>">
</head>
<body class="tv-body"><?= $content ?><script src="<?= asset('assets/js/pages/tv-panel.js?v=3') ?>"></script></body>
</html>
