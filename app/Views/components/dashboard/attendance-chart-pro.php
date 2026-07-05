<?php

$items = $frequencyLast30Days ?? [];

$goalPercentage = (float) ($goalPercentage ?? 95);

$values = array_map(
    fn ($item) => (float) ($item['percentage'] ?? 0),
    $items
);

$count = count($items);

$average = $count > 0
    ? array_sum($values) / $count
    : 0;

$best = $count > 0
    ? max($values)
    : 0;

$worst = $count > 0
    ? min($values)
    : 0;

$first = $values[0] ?? 0;
$last = $values[$count - 1] ?? 0;

$trend = $last >= $first
    ? 'Crescimento'
    : 'Queda';

$trendIcon = $last >= $first
    ? 'trending-up'
    : 'trending-down';

$trendClass = $last >= $first
    ? 'success'
    : 'danger';

$svgWidth = 900;
$svgHeight = 420;
$paddingX = 58;
$paddingY = 48;

$chartWidth = $svgWidth - ($paddingX * 2);
$chartHeight = $svgHeight - ($paddingY * 2);

$points = [];

if ($count > 0) {

    foreach ($items as $index => $item) {

        $percentage = (float) ($item['percentage'] ?? 0);

        $x = $count > 1
            ? $paddingX + (($chartWidth / ($count - 1)) * $index)
            : $paddingX;

        $y = $paddingY + ($chartHeight - (($percentage / 100) * $chartHeight));

        $points[] = [
            'x' => $x,
            'y' => $y,
            'percentage' => $percentage,
            'date' => date('d/m', strtotime($item['attendance_date'] ?? date('Y-m-d'))),
        ];

    }

}

$linePoints = implode(
    ' ',
    array_map(
        fn ($point) => $point['x'] . ',' . $point['y'],
        $points
    )
);

$areaPoints = $linePoints !== ''
    ? $paddingX . ',' . ($svgHeight - $paddingY) . ' ' . $linePoints . ' ' . ($svgWidth - $paddingX) . ',' . ($svgHeight - $paddingY)
    : '';

$goalY = $paddingY + ($chartHeight - (($goalPercentage / 100) * $chartHeight));

$gridValues = [100, 90, 80, 70, 60, 50, 40, 30, 20, 10, 0];

?>

<div class="card attendance-chart-panel attendance-chart-pro-panel">

    <?php component('dashboard/panel-header', [
        'icon' => 'chart-column-big',
        'title' => 'Frequência dos últimos 30 dias',
        'subtitle' => 'Evolução diária da presença escolar',
        'badge' => '30 dias',
        'badgeClass' => 'badge-success',
    ]); ?>

    <?php if (empty($items)): ?>

        <div class="activity-empty">
            Ainda não há dados suficientes para gerar o gráfico.
        </div>

    <?php else: ?>

        <div class="attendance-chart-pro">

            <svg
                viewBox="0 0 <?= $svgWidth ?> <?= $svgHeight ?>"
                class="attendance-chart-pro-svg"
                role="img"
                aria-label="Gráfico de linhas da frequência dos últimos 30 dias"
            >

                <?php foreach ($gridValues as $value): ?>

                    <?php
                        $gridY = $paddingY + (
                            $chartHeight -
                            (($value / 100) * $chartHeight)
                        );
                    ?>

                    <line
                        x1="<?= $paddingX ?>"
                        y1="<?= $gridY ?>"
                        x2="<?= $svgWidth - $paddingX ?>"
                        y2="<?= $gridY ?>"
                        class="attendance-chart-grid"
                    />

                    <text
                        x="<?= $paddingX - 12 ?>"
                        y="<?= $gridY + 5 ?>"
                        class="attendance-chart-grid-label"
                        text-anchor="end"
                    >
                        <?= $value ?>%
                    </text>

                <?php endforeach; ?>

                <line
                    x1="<?= $paddingX ?>"
                    y1="<?= $paddingY ?>"
                    x2="<?= $paddingX ?>"
                    y2="<?= $svgHeight - $paddingY ?>"
                    class="attendance-chart-axis"
                />

                <line
                    x1="<?= $paddingX ?>"
                    y1="<?= $svgHeight - $paddingY ?>"
                    x2="<?= $svgWidth - $paddingX ?>"
                    y2="<?= $svgHeight - $paddingY ?>"
                    class="attendance-chart-axis"
                />

                <line
                    x1="<?= $paddingX ?>"
                    y1="<?= $goalY ?>"
                    x2="<?= $svgWidth - $paddingX ?>"
                    y2="<?= $goalY ?>"
                    class="attendance-chart-pro-goal"
                />

                <text
                    x="<?= $svgWidth - $paddingX ?>"
                    y="<?= $goalY - 8 ?>"
                    class="attendance-chart-pro-goal-label"
                    text-anchor="end"
                >
                    Meta <?= number_format($goalPercentage, 0, ',', '.') ?>%
                </text>

                <?php foreach ($points as $point): ?>

                    <line
                        x1="<?= $point['x'] ?>"
                        y1="<?= $paddingY ?>"
                        x2="<?= $point['x'] ?>"
                        y2="<?= $svgHeight - $paddingY ?>"
                        class="attendance-chart-vertical-guide"
                    />

                <?php endforeach; ?>

                <?php if ($areaPoints !== ''): ?>

                    <polygon
                        points="<?= e($areaPoints) ?>"
                        class="attendance-chart-pro-area"
                    />

                <?php endif; ?>

                <?php if ($linePoints !== ''): ?>

                    <polyline
                        points="<?= e($linePoints) ?>"
                        class="attendance-chart-pro-line"
                    />

                <?php endif; ?>

                <?php foreach ($points as $point): ?>

                    <g class="attendance-chart-pro-point">

                        <circle
                            cx="<?= $point['x'] ?>"
                            cy="<?= $point['y'] ?>"
                            r="5"
                        />

                        <title>
                            <?= e($point['date']) ?> — <?= number_format($point['percentage'], 1, ',', '.') ?>%
                        </title>

                    </g>

                <?php endforeach; ?>

                <?php foreach ($points as $point): ?>

                    <text
                        x="<?= $point['x'] ?>"
                        y="<?= $svgHeight - 8 ?>"
                        class="attendance-chart-date"
                        text-anchor="middle"
                    >
                        <?= e($point['date']) ?>
                    </text>

                <?php endforeach; ?>

            </svg>

        </div>

        <div class="attendance-chart-pro-summary">

            <div>
                <span>Média</span>
                <strong><?= number_format($average, 1, ',', '.') ?>%</strong>
            </div>

            <div>
                <span>Melhor dia</span>
                <strong><?= number_format($best, 1, ',', '.') ?>%</strong>
            </div>

            <div>
                <span>Menor frequência</span>
                <strong><?= number_format($worst, 1, ',', '.') ?>%</strong>
            </div>

            <div class="attendance-chart-pro-trend attendance-chart-pro-trend-<?= e($trendClass) ?>">
                <span>Tendência</span>

                <strong>
                    <i data-lucide="<?= e($trendIcon) ?>"></i>
                    <?= e($trend) ?>
                </strong>
            </div>

        </div>

        <a
            href="<?= base_url('relatorios') ?>"
            class="attendance-chart-pro-action"
        >
            <span>
                <i data-lucide="file-text"></i>
                Ver relatório completo
            </span>

            <i data-lucide="chevron-right"></i>
        </a>

    <?php endif; ?>

</div>