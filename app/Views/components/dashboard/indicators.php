<?php $goalPercentage=(float)($goalPercentage??95); $attentionThreshold=max(0,$goalPercentage-5); ?>
<div class="card indicators-panel">

    <?php component('dashboard/panel-header', [
        'icon' => 'activity',
        'title' => 'Indicadores da escola',
        'subtitle' => 'Frequência por período',
        'badge' => 'Hoje • Semana • Mês • Ano',
        'badgeClass' => 'badge-success',
    ]); ?>

    <?php

    $periods = [

        'Hoje' => [
            'icon' => 'calendar-days',
            'data' => $today ?? [],
        ],

        'Semana' => [
            'icon' => 'calendar-range',
            'data' => $week ?? [],
        ],

        'Mês' => [
            'icon' => 'calendar',
            'data' => $month ?? [],
        ],

        'Ano' => [
            'icon' => 'calendar-check',
            'data' => $year ?? [],
        ],

    ];

    ?>

    <div class="indicator-grid">

        <?php foreach ($periods as $label => $period): ?>

            <?php

            $data = $period['data'];

            $percentage = (float) ($data['percentage'] ?? 0);

            $color = match (true) {

                $percentage >= $goalPercentage => 'var(--success)',

                $percentage >= $attentionThreshold => 'var(--warning)',

                default => 'var(--danger)',

            };

            ?>

            <div class="indicator-card">

                <div class="indicator-card-header">

                    <div>

                        <h3><?= e($label) ?></h3>

                        <span class="indicator-card-subtitle">
                            Frequência do período
                        </span>

                    </div>

                    <div class="indicator-card-icon">
                        <i data-lucide="<?= e($period['icon']) ?>"></i>
                    </div>

                </div>

                <?php component('base/progress', [
                    'percentage' => $percentage,
                    'value' => number_format($percentage, 1, ',', '.') . '%',
                    'label' => 'Frequência',
                    'size' => 120,
                    'color' => $color
                ]); ?>

                <div class="indicator-metrics">

                    <span>
                        👥 Registros
                        <strong><?= (int) ($data['total_students'] ?? 0) ?></strong>
                    </span>

                    <span>
                        ✅ Presentes
                        <strong><?= (int) ($data['presentes'] ?? 0) ?></strong>
                    </span>

                    <span>
                        ❌ Faltas
                        <strong><?= (int) ($data['faltas'] ?? 0) ?></strong>
                    </span>

                    <span>
                        🟡 Justificadas
                        <strong><?= (int) ($data['justificadas'] ?? 0) ?></strong>
                    </span>

                    <span>
                        🔵 Atestados
                        <strong><?= (int) ($data['atestados'] ?? 0) ?></strong>
                    </span>

                    <span>
                        🚌 Ônibus
                        <strong><?= (int) ($data['onibus'] ?? 0) ?></strong>
                    </span>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>