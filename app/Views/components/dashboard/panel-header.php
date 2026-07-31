<div class="dashboard-panel-header">

    <div class="dashboard-panel-title">

        <?php if (!empty($icon)): ?>

            <div class="dashboard-panel-icon">
                <i data-lucide="<?= e($icon) ?>"></i>
            </div>

        <?php endif; ?>

        <div>

            <h3><?= e($title ?? '') ?></h3>

            <?php if (!empty($subtitle)): ?>

                <p><?= e($subtitle) ?></p>

            <?php endif; ?>

        </div>

    </div>

    <?php if (!empty($badge)): ?>

        <span class="badge <?= e($badgeClass ?? 'badge-success') ?>"<?= !empty($badgeDataAttribute) ? ' data-' . e($badgeDataAttribute) : '' ?>>
            <?= e($badge) ?>
        </span>

    <?php endif; ?>

</div>