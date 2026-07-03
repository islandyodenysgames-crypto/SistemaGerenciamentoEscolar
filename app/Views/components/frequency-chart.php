<div class="card">

    <div class="card-header">
        <h3>Frequência dos últimos 30 dias</h3>
    </div>

    <?php if (empty($frequencyLast30Days)): ?>

        <div class="activity-empty">
            Ainda não há dados suficientes para gerar o gráfico.
        </div>

    <?php else: ?>

        <div style="display:flex;align-items:end;gap:8px;height:220px;margin-top:24px;">

            <?php foreach ($frequencyLast30Days as $day): ?>

                <?php
                    $percentage = (float) $day['percentage'];
                    $height = max(8, $percentage * 2);
                ?>

                <div title="<?= date('d/m/Y', strtotime($day['attendance_date'])) ?> - <?= $percentage ?>%"
                     style="flex:1;height:<?= $height ?>px;background:var(--primary);border-radius:8px 8px 0 0;">
                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>