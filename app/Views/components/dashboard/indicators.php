<div class="card indicators-panel">

    <div class="card-header">
        <h3>Indicadores da escola</h3>
    </div>

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

            <?php $data = $period['data']; ?>

            <div class="indicator-card">

                <div class="indicator-card-header">

                    <h3><?= e($label) ?></h3>

                    <div class="indicator-card-icon">
                        <i data-lucide="<?= e($period['icon']) ?>"></i>
                    </div>

                </div>

                <?php component('base/progress', [
                    'percentage' => (float) ($data['percentage'] ?? 0),
                    'label' => 'Frequência',
                    'size' => 120
                ]); ?>

                <div class="indicator-metrics">

                    <span>Registros <strong><?= (int) ($data['total_students'] ?? 0) ?></strong></span>
                    <span>Presentes <strong><?= (int) ($data['presentes'] ?? 0) ?></strong></span>
                    <span>Faltas <strong><?= (int) ($data['faltas'] ?? 0) ?></strong></span>
                    <span>Justificadas <strong><?= (int) ($data['justificadas'] ?? 0) ?></strong></span>
                    <span>Atestados <strong><?= (int) ($data['atestados'] ?? 0) ?></strong></span>
                    <span>Ônibus <strong><?= (int) ($data['onibus'] ?? 0) ?></strong></span>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>