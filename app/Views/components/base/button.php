<?php

$type = $type ?? 'button';

$href = $href ?? null;

$variant = $variant ?? 'primary';

$size = $size ?? '';

$icon = $icon ?? null;

$label = $label ?? '';

$class = trim("btn btn-{$variant} {$size}");

?>

<?php if ($href): ?>

    <a
        href="<?= e($href) ?>"
        class="<?= e($class) ?>"
    >

        <?php if ($icon): ?>

            <i data-lucide="<?= e($icon) ?>"></i>

        <?php endif; ?>

        <span><?= e($label) ?></span>

    </a>

<?php else: ?>

    <button
        type="<?= e($type) ?>"
        class="<?= e($class) ?>"
    >

        <?php if ($icon): ?>

            <i data-lucide="<?= e($icon) ?>"></i>

        <?php endif; ?>

        <span><?= e($label) ?></span>

    </button>

<?php endif; ?>