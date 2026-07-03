<?php

$percentage = (float) ($percentage ?? 0);
$label = $label ?? 'Frequência';
$size = (int) ($size ?? 120);

$percentage = max(0, min(100, $percentage));

$color = 'var(--success)';

if ($percentage < 75) {
    $color = 'var(--danger)';
} elseif ($percentage < 85) {
    $color = 'var(--secondary)';
} elseif ($percentage < 95) {
    $color = 'var(--warning)';
}

$fontSize = max(20, (int) ($size * 0.20));

?>

<div
    class="progress-circle"
    style="
        --circle-size: <?= $size ?>px;
        --circle-color: <?= $color ?>;
        --circle-progress: <?= $percentage ?>%;
        --circle-font-size: <?= $fontSize ?>px;
    "
>
    <div class="progress-circle-inner">

        <strong>
            <?= number_format($percentage, 1, ',', '.') ?>%
        </strong>

        <small>
            <?= htmlspecialchars($label) ?>
        </small>

    </div>
</div>