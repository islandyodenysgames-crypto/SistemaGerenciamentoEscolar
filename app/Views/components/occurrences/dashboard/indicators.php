<?php

$indicators = is_array($indicators ?? null)
    ? $indicators
    : [];

?>

<section class="occurrence-indicators" aria-labelledby="occurrence-indicators-title">

    <div class="occurrence-indicators-heading">
        <div>
            <span class="occurrence-indicators-eyebrow">Leitura automática</span>
            <h2 id="occurrence-indicators-title">Indicadores inteligentes</h2>
            <p>Sinais que ajudam a identificar prioridades de acompanhamento.</p>
        </div>

        <span class="occurrence-indicators-period">
            <i data-lucide="calendar-clock"></i>
            Atualizados com os dados atuais
        </span>
    </div>

    <div class="occurrence-indicators-grid">
        <?php foreach ($indicators as $indicator): ?>
            <?php
            $tone = preg_replace(
                '/[^a-z-]/',
                '',
                strtolower((string) ($indicator['tone'] ?? 'neutral'))
            );

            $icon = preg_replace(
                '/[^a-z0-9-]/',
                '',
                strtolower((string) ($indicator['icon'] ?? 'activity'))
            );
            ?>

            <article class="occurrence-indicator <?= e($tone) ?>">
                <div class="occurrence-indicator-icon">
                    <i data-lucide="<?= e($icon) ?>"></i>
                </div>

                <div class="occurrence-indicator-content">
                    <span><?= e((string) ($indicator['label'] ?? 'Indicador')) ?></span>

                    <strong>
                        <?= e((string) ($indicator['value'] ?? 0)) ?><?= e((string) ($indicator['suffix'] ?? '')) ?>
                    </strong>

                    <small><?= e((string) ($indicator['description'] ?? '')) ?></small>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

</section>
