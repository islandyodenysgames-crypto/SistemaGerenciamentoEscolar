<div class="table-header">

    <div>

        <h3><?= e($title ?? '') ?></h3>

        <?php if (!empty($subtitle)): ?>

            <p class="text-muted mt-8">
                <?= e($subtitle) ?>
            </p>

        <?php endif; ?>

    </div>

    <?php if (!empty($actionLabel) && !empty($actionUrl)): ?>

        <a
            href="<?= e($actionUrl) ?>"
            class="<?= e($actionClass ?? 'btn-primary') ?>"
        >
            <?= e($actionLabel) ?>
        </a>

    <?php endif; ?>

</div>