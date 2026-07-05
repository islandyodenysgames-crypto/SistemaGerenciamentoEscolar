<div class="card attendance-chart-panel">

    <div class="card-header">

        <h3>Frequência dos últimos 30 dias</h3>

    </div>

    <?php if (empty($frequencyLast30Days)): ?>

        <div class="activity-empty">

            Ainda não há dados suficientes para gerar o gráfico.

        </div>

    <?php else: ?>

        <div class="attendance-chart">

            <?php foreach ($frequencyLast30Days as $day): ?>

                <?php

                    $percentage = (float) ($day['percentage'] ?? 0);

                    $height = max(8, $percentage * 2);

                    $date = date(
                        'd/m/Y',
                        strtotime($day['attendance_date'])
                    );

                ?>

                <div
                    class="attendance-chart-bar"
                    style="--bar-height: <?= $height ?>px;"
                    title="<?= $date ?> — <?= number_format($percentage, 1, ',', '.') ?>%"
                    aria-label="<?= $date ?> - <?= number_format($percentage, 1, ',', '.') ?>%"
                >

                    <span class="attendance-chart-tooltip">

                        <?= number_format($percentage, 1, ',', '.') ?>%

                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>