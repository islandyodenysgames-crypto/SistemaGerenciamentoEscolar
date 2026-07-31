<?php
$url = (string) ($url ?? '#');
$label = (string) ($label ?? 'Indicador');
$value = (int) ($value ?? 0);
$icon = (string) ($icon ?? 'arrow-up-right');
$tone = (string) ($tone ?? 'primary');
$description = (string) ($description ?? 'Clique para visualizar os casos');
$tooltip = (string) ($tooltip ?? $description);
?>
<a class="intelligence-stat intelligence-stat--<?= e($tone) ?> intelligence-stat--clickable panel-hover"
   href="<?= e($url) ?>"
   aria-label="<?= e($label . ': ' . $value . '. Abrir casos') ?>"
   title="<?= e($tooltip) ?>"
   data-tooltip="<?= e($tooltip) ?>">
    <div class="intelligence-stat__icon"><i data-lucide="<?= e($icon) ?>"></i></div>
    <div class="intelligence-stat__content">
        <strong><?= $value ?></strong>
        <span><?= e($label) ?></span>
        <small><?= e($description) ?></small>
    </div>
    <span class="intelligence-stat__arrow" aria-hidden="true"><i data-lucide="arrow-up-right"></i></span>
</a>
