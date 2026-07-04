<div class="card">

    <div class="card-header">
        <h3>Frequência dos últimos 30 dias</h3>
    </div>

    <?php if (empty($frequencyLast30Days)): ?>

        <div class="activity-empty">
            Ainda não há dados suficientes para gerar o gráfico.
        </div>

    <?php else: ?>

        <div class="frequency-chart">

            <?php foreach ($frequencyLast30Days as $day): ?>

                <?php
                    $percentage = (float) $day['percentage'];
                    $height = max(8, $percentage * 2);
                ?>

                <div
                    class="frequency-chart-bar"
                    title="<?= date('d/m/Y', strtotime($day['attendance_date'])) ?> - <?= number_format($percentage, 1, ',', '.') ?>%"
                    style="--bar-height: <?= $height ?>px;"
                ></div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>