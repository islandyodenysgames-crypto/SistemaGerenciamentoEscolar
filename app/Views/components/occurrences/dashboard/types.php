<?php

$items = $items ?? [];

$typeClasses = [
    'OBSERVATION' => 'observation',
    'WARNING' => 'warning',
    'SUSPENSION' => 'suspension',
    'REFERRAL' => 'referral',
    'PRAISE' => 'praise',
    'OTHER' => 'other',
];

$typeIcons = [
    'OBSERVATION' => 'eye',
    'WARNING' => 'triangle-alert',
    'SUSPENSION' => 'ban',
    'REFERRAL' => 'send',
    'PRAISE' => 'award',
    'OTHER' => 'file-text',
];

$maximum = 0;

foreach ($items as $item) {
    $maximum = max(
        $maximum,
        (int) ($item['total'] ?? 0)
    );
}

?>

<section class="card occurrences-dashboard-widget">

    <div class="occurrences-dashboard-widget-header">

        <div>
            <h3>Tipos mais registrados</h3>
            <p>Distribuição das ocorrências por categoria.</p>
        </div>

        <i data-lucide="chart-no-axes-column-increasing"></i>

    </div>

    <?php if (empty($items)): ?>

        <div class="activity-empty">
            Nenhuma ocorrência registrada.
        </div>

    <?php else: ?>

        <div class="occurrences-types-list">

            <?php foreach ($items as $item): ?>

                <?php

                $type = (string) ($item['type'] ?? 'OTHER');

                $typeClass = $typeClasses[$type] ?? 'other';
                $typeIcon = $typeIcons[$type] ?? 'file-text';

                $total = (int) ($item['total'] ?? 0);

                $percentage = $maximum > 0
                    ? ($total / $maximum) * 100
                    : 0;

                ?>

                <div class="occurrences-type-item <?= e($typeClass) ?>">

                    <div class="occurrences-type-icon">
                        <i data-lucide="<?= e($typeIcon) ?>"></i>
                    </div>

                    <div class="occurrences-type-content">

                        <div class="occurrences-type-label">

                            <span>
                                <?= e(
                                    $item['type_label']
                                    ?? 'Outro'
                                ) ?>
                            </span>

                            <strong><?= $total ?></strong>

                        </div>

                        <div class="occurrences-type-progress">

                            <span
                                style="width: <?= number_format(
                                    $percentage,
                                    2,
                                    '.',
                                    ''
                                ) ?>%;"
                            ></span>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>