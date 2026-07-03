<?php

$icon = $icon ?? '📌';
$title = $title ?? 'Indicador';
$value = $value ?? 0;
$subtitle = $subtitle ?? '';
$description = $description ?? '';
$color = $color ?? 'blue';
$url = $url ?? '#';

$colors = [
    'green' => '#16a34a',
    'yellow' => '#d97706',
    'red' => '#dc2626',
    'blue' => '#2563eb',
];

$mainColor = $colors[$color] ?? $colors['blue'];

?>

<a
    href="<?= $url ?>"
    class="metric-card"
    style="border-top-color:<?= $mainColor ?>;"
>
    <div class="metric-card-header">
        <span><?= $icon ?></span>
        <strong><?= htmlspecialchars($title) ?></strong>
    </div>

    <div class="metric-value">
        <?= htmlspecialchars((string) $value) ?>
    </div>

    <?php if ($subtitle): ?>
        <div class="metric-subtitle">
            <?= htmlspecialchars($subtitle) ?>
        </div>
    <?php endif; ?>

    <?php if ($description): ?>
        <div class="metric-description">
            <?= htmlspecialchars($description) ?>
        </div>
    <?php endif; ?>
</a>