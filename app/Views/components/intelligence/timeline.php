<?php
$timeline = is_array($timeline ?? null) ? $timeline : [];
$title = (string) ($title ?? 'Timeline inteligente');
$subtitle = (string) ($subtitle ?? 'Mudanças e sinais relevantes identificados automaticamente.');
?>
<section class="intelligence-timeline" aria-label="<?= e($title) ?>">
    <div class="intelligence-timeline__header">
        <div>
            <span><i data-lucide="history"></i> Acompanhamento</span>
            <h4><?= e($title) ?></h4>
            <p><?= e($subtitle) ?></p>
        </div>
        <span class="intelligence-timeline__count"><?= count($timeline) ?> evento(s)</span>
    </div>

    <div class="intelligence-timeline__list">
        <?php foreach ($timeline as $event): ?>
            <?php
            $level = strtolower((string) ($event['level'] ?? 'information'));
            $target = trim((string) ($event['target'] ?? ''));
            ?>
            <article class="intelligence-timeline__item intelligence-timeline__item--<?= e($level) ?>">
                <div class="intelligence-timeline__rail">
                    <span><i data-lucide="<?= e((string) ($event['icon'] ?? 'circle')) ?>"></i></span>
                </div>
                <div class="intelligence-timeline__content">
                    <small><?= e((string) ($event['date_label'] ?? 'Hoje')) ?></small>
                    <strong><?= e((string) ($event['title'] ?? 'Evento')) ?></strong>
                    <p><?= e((string) ($event['description'] ?? '')) ?></p>
                </div>
                <?php if ($target !== ''): ?>
                    <a href="<?= base_url($target) ?>" aria-label="Abrir detalhes de <?= e((string) ($event['title'] ?? 'evento')) ?>">
                        Abrir <i data-lucide="arrow-up-right"></i>
                    </a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
