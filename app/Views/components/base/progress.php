<?php

$percentage = max(0, min(100, (float) ($percentage ?? 0)));
$label = $label ?? '';
$size = (int) ($size ?? 120);

$value = $value ?? number_format($percentage, 1, ',', '.') . '%';

$color = $color ?? match (true) {
    $percentage >= 95 => 'var(--success)',
    $percentage >= 85 => 'var(--warning)',
    default => 'var(--danger)',
};

?>

<div
    class="progress-circle"
    style="
        --circle-size: <?= $size ?>px;
        --circle-progress: <?= $percentage ?>%;
        --circle-color: <?= e($color) ?>;
    "
>
    <div class="progress-circle-inner">

        <strong>
            <?= e((string) $value) ?>
        </strong>

        <?php if (!empty($label)): ?>

            <small>
                <?= e($label) ?>
            </small>

        <?php endif; ?>

    </div>
</div>