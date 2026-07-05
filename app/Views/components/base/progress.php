<?php

declare(strict_types=1);

$percentage = (float) ($percentage ?? 0);
$label = $label ?? 'Frequência';
$size = max(80, (int) ($size ?? 120));

$percentage = max(0, min(100, round($percentage, 1)));

$color = match (true) {
    $percentage < 75 => 'var(--danger)',
    $percentage < 85 => 'var(--secondary)',
    $percentage < 95 => 'var(--warning)',
    default => 'var(--success)',
};

$fontSize = max(20, (int) ($size * 0.20));

?>

<div
    class="progress-circle"
    style="
        --circle-size: <?= $size ?>px;
        --circle-progress: <?= $percentage ?>%;
        --circle-color: <?= $color ?>;
        --circle-font-size: <?= $fontSize ?>px;
    "
>

    <div class="progress-circle-inner">

        <strong>
            <?= number_format($percentage, 1, ',', '.') ?>%
        </strong>

        <small>
            <?= e($label) ?>
        </small>

    </div>

</div>