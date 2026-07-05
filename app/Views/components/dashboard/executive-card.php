<a
    href="<?= e($url ?? '#') ?>"
    class="manager-card manager-card-<?= e($color ?? 'blue') ?>"
>

    <div class="manager-card-icon">
        <i data-lucide="<?= e($icon ?? 'circle') ?>"></i>
    </div>

    <h3>
        <?= e($title ?? '') ?>

        <?php if (!empty($tooltip)): ?>
            <span title="<?= e($tooltip) ?>">i</span>
        <?php endif; ?>
    </h3>

    <?php component('base/progress', [
        'percentage' => (float) ($percentage ?? 0),
        'value' => (string) ($value ?? '0'),
        'label' => (string) ($progressLabel ?? ''),
        'size' => (int) ($size ?? 190),
        'color' => $progressColor ?? 'var(--info)'
    ]); ?>

    <div class="manager-card-status">
        <?= e($status ?? '') ?>
    </div>

    <div class="manager-card-meta">
        <?= e($meta ?? '') ?>
    </div>

    <div class="manager-card-action">

        <i data-lucide="<?= e($actionIcon ?? 'arrow-right') ?>"></i>

        <span><?= e($actionLabel ?? 'Acessar') ?></span>

        <i data-lucide="chevron-right"></i>

    </div>

</a>