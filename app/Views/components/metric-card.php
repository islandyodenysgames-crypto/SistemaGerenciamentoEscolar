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
    href="<?= htmlspecialchars($url) ?>"
    class="metric-card <?= $colorClass ?>"
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