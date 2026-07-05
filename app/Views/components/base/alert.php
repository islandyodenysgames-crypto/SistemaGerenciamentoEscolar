<?php if (!empty($message)): ?>

    <div class="alert alert-<?= e($type ?? 'info') ?>">
        <?= e($message) ?>
    </div>

<?php endif; ?>