<?php

$student = $student ?? [];
$attendanceEvolution = $attendanceEvolution ?? [];
$chartMode = $chartMode ?? 'diario';

$calendarMonth = (int) (
    $calendarMonth ?? date('n')
);

$calendarYear = (int) (
    $calendarYear ?? date('Y')
);

if (!in_array($chartMode, ['diario', 'mensal'], true)) {
    $chartMode = 'diario';
}

$chartTitle = $chartMode === 'mensal'
    ? 'Evolução mensal da frequência'
    : 'Evolução diária da frequência';

$chartSubtitle = $chartMode === 'mensal'
    ? 'Comparativo da frequência do aluno em cada mês letivo.'
    : 'Evolução acumulada da frequência a cada chamada registrada.';

$monthShortLabels = [
    1 => 'Jan',
    2 => 'Fev',
    3 => 'Mar',
    4 => 'Abr',
    5 => 'Mai',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Ago',
    9 => 'Set',
    10 => 'Out',
    11 => 'Nov',
    12 => 'Dez',
];

$chartWidth = 720;
$chartHeight = 280;

$chartPaddingLeft = 54;
$chartPaddingRight = 32;
$chartPaddingTop = 28;
$chartPaddingBottom = 46;

$chartInnerWidth = $chartWidth
    - $chartPaddingLeft
    - $chartPaddingRight;

$chartInnerHeight = $chartHeight
    - $chartPaddingTop
    - $chartPaddingBottom;

$chartPoints = [];

if (!empty($attendanceEvolution)) {
    $count = count($attendanceEvolution);

    foreach ($attendanceEvolution as $index => $item) {
        $x = $count > 1
            ? $chartPaddingLeft
                + (
                    $index
                    * ($chartInnerWidth / ($count - 1))
                )
            : $chartPaddingLeft + ($chartInnerWidth / 2);

        $percentage = (float) (
            $item['percentage'] ?? 0
        );

        $percentage = max(
            0,
            min(100, $percentage)
        );

        $y = $chartPaddingTop
            + (
                (100 - $percentage)
                / 100
            )
            * $chartInnerHeight;

        if ($chartMode === 'mensal') {
            $label = (
                $monthShortLabels[
                    (int) ($item['month'] ?? 0)
                ] ?? ''
            )
            . '/'
            . ($item['year'] ?? '');
        } else {
            $label = !empty($item['date'])
                ? date(
                    'd/m',
                    strtotime($item['date'])
                )
                : '';
        }

        $chartPoints[] = [
            'x' => round($x, 2),
            'y' => round($y, 2),
            'label' => $label,
            'percentage' => $percentage,
        ];
    }
}

$polylinePoints = implode(
    ' ',
    array_map(
        static fn (array $point): string =>
            $point['x'] . ',' . $point['y'],
        $chartPoints
    )
);

$chartAxisY = [100, 85, 75, 50, 0];

?>

<div
    class="card student-evolution-card"
    id="studentEvolution"
>

    <div class="student-evolution-header">

        <div>
            <h3><?= e($chartTitle) ?></h3>
            <p><?= e($chartSubtitle) ?></p>
        </div>

        <div class="student-chart-toggle">

            <a
                href="<?= base_url(
                    'alunos/perfil?id='
                    . (int) ($student['id'] ?? 0)
                    . '&grafico=diario'
                    . '&mes=' . $calendarMonth
                    . '&ano=' . $calendarYear
                    . '#studentEvolution'
                ) ?>"
                class="<?= $chartMode === 'diario'
                    ? 'active'
                    : ''
                ?>"
            >
                Diário
            </a>

            <a
                href="<?= base_url(
                    'alunos/perfil?id='
                    . (int) ($student['id'] ?? 0)
                    . '&grafico=mensal'
                    . '&mes=' . $calendarMonth
                    . '&ano=' . $calendarYear
                    . '#studentEvolution'
                ) ?>"
                class="<?= $chartMode === 'mensal'
                    ? 'active'
                    : ''
                ?>"
            >
                Mensal
            </a>

        </div>

    </div>

    <?php if (empty($chartPoints)): ?>

        <div class="activity-empty">
            Dados insuficientes para gerar o gráfico.
        </div>

    <?php else: ?>

        <div class="student-evolution-chart">

            <svg
                viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight ?>"
                role="img"
                aria-label="<?= e($chartTitle) ?>"
            >

                <?php foreach ($chartAxisY as $axisValue): ?>

                    <?php

                    $axisY = $chartPaddingTop
                        + (
                            (100 - $axisValue)
                            / 100
                        )
                        * $chartInnerHeight;

                    ?>

                    <line
                        x1="<?= $chartPaddingLeft ?>"
                        y1="<?= round($axisY, 2) ?>"
                        x2="<?= $chartWidth - $chartPaddingRight ?>"
                        y2="<?= round($axisY, 2) ?>"
                        class="<?= $axisValue === 85
                            ? 'chart-guide'
                            : 'chart-grid-line'
                        ?>"
                    />

                    <text
                        x="<?= $chartPaddingLeft - 12 ?>"
                        y="<?= round($axisY + 4, 2) ?>"
                        text-anchor="end"
                        class="chart-label"
                    >
                        <?= $axisValue ?>%
                    </text>

                <?php endforeach; ?>

                <line
                    x1="<?= $chartPaddingLeft ?>"
                    y1="<?= $chartPaddingTop ?>"
                    x2="<?= $chartPaddingLeft ?>"
                    y2="<?= $chartPaddingTop + $chartInnerHeight ?>"
                    class="chart-axis"
                />

                <line
                    x1="<?= $chartPaddingLeft ?>"
                    y1="<?= $chartPaddingTop + $chartInnerHeight ?>"
                    x2="<?= $chartWidth - $chartPaddingRight ?>"
                    y2="<?= $chartPaddingTop + $chartInnerHeight ?>"
                    class="chart-axis"
                />

                <polyline
                    points="<?= e($polylinePoints) ?>"
                    class="chart-line"
                />

                <?php foreach ($chartPoints as $point): ?>

                    <circle
                        cx="<?= $point['x'] ?>"
                        cy="<?= $point['y'] ?>"
                        r="7"
                        class="chart-point"
                    />

                    <text
                        x="<?= $point['x'] ?>"
                        y="<?= max(
                            16,
                            $point['y'] - 14
                        ) ?>"
                        text-anchor="middle"
                        class="chart-percent"
                    >
                        <?= number_format(
                            $point['percentage'],
                            0,
                            ',',
                            '.'
                        ) ?>%
                    </text>

                    <text
                        x="<?= $point['x'] ?>"
                        y="<?= $chartHeight - 14 ?>"
                        text-anchor="middle"
                        class="chart-month"
                    >
                        <?= e($point['label']) ?>
                    </text>

                <?php endforeach; ?>

            </svg>

        </div>

    <?php endif; ?>

</div>