<?php

$variant = $variant ?? 'sidebar';

$name = $name ?? school('name', app_name());
$shortName = $shortName ?? school('short_name', 'SFE');
$logoPath = $logoPath ?? school('logo_path');

?>

<?php if ($variant === 'sidebar'): ?>

    <div class="logo">

        <?php if (!empty($logoPath)): ?>

            <div class="logo-image">
                <img
                    src="<?= asset($logoPath) ?>"
                    alt="<?= e($name) ?>"
                >
            </div>

        <?php else: ?>

            <div class="logo-icon">
                <i data-lucide="graduation-cap"></i>
            </div>

        <?php endif; ?>

        <div class="logo-text">
            <strong><?= e($shortName) ?></strong>
            <small><?= e($name) ?></small>
        </div>

    </div>

<?php elseif ($variant === 'ranking'): ?>

    <div class="school-branding-ranking">

        <?php if (!empty($logoPath)): ?>

            <div class="school-branding-ranking-logo">
                <img
                    src="<?= asset($logoPath) ?>"
                    alt="<?= e($name) ?>"
                >
            </div>

        <?php else: ?>

            <div class="school-branding-ranking-logo school-branding-ranking-logo-fallback">
                <i data-lucide="graduation-cap"></i>
            </div>

        <?php endif; ?>

        <div>
            <strong><?= e($name) ?></strong>
            <span><?= e($shortName) ?></span>
        </div>

    </div>

<?php elseif ($variant === 'report'): ?>

    <div class="school-branding-report">

        <?php if (!empty($logoPath)): ?>

            <div class="school-branding-report-logo">
                <img
                    src="<?= asset($logoPath) ?>"
                    alt="<?= e($name) ?>"
                >
            </div>

        <?php else: ?>

            <div class="school-branding-report-icon">
                <i data-lucide="graduation-cap"></i>
            </div>

        <?php endif; ?>

        <div>
            <strong><?= e($name) ?></strong>
            <span><?= e($shortName) ?></span>
        </div>

    </div>

<?php endif; ?>