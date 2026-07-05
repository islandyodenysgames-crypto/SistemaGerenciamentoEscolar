<div class="stat-card">

    <div class="stat-card-icon">
        <i data-lucide="<?= e($icon ?? 'circle') ?>"></i>
    </div>

    <div class="stat-card-info">

        <span>
            <?= e($label ?? '') ?>
        </span>

        <h2>
            <?= e((string) ($value ?? 0)) ?>
        </h2>

    </div>

</div>