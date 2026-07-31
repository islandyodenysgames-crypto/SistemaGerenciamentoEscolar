<?php

$variant = $variant ?? 'sidebar';

$schoolData = school();

$name = trim((string) ($name ?? ($schoolData['name'] ?? app_name())));
$shortName = trim((string) ($shortName ?? ($schoolData['short_name'] ?? 'SFE')));
$logoPath = $logoPath ?? ($schoolData['logo_path'] ?? null);

if ($name === '') {
    $name = app_name();
}

if ($shortName === '') {
    $shortName = 'SFE';
}

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