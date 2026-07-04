<?php

$icon = $icon ?? '📌';
$title = $title ?? 'Indicador';
$value = $value ?? 0;
$subtitle = $subtitle ?? '';
$description = $description ?? '';
$color = $color ?? 'blue';
$url = $url ?? '#';

$colorClass = match ($color) {
    'green' => 'metric-green',
    'yellow' => 'metric-yellow',
    'red' => 'metric-red',
    default => 'metric-blue',
};

?>

<a
    href="<?= e($url) ?>"
    class="metric-card <?= $colorClass ?>"
>
    <div class="metric-card-header">
        <span><?= $icon ?></span>
        <strong><?= e($title) ?></strong>
    </div>

    <div class="metric-value">
        <?= e((string) $value) ?>
    </div>

    <?php if ($subtitle): ?>
        <div class="metric-subtitle">
            <?= e($subtitle) ?>
        </div>
    <?php endif; ?>

    <?php if ($description): ?>
        <div class="metric-description">
            <?= e($description) ?>
        </div>
    <?php endif; ?>
</a>