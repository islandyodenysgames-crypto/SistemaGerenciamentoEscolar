<div class="card">

    <div class="card-header">
        <h3>Indicadores da Escola</h3>
    </div>

    <?php
        $periods = [
            'Hoje' => $today ?? [],
            'Semana' => $week ?? [],
            'Mês' => $month ?? [],
            'Ano' => $year ?? [],
        ];
    ?>

    <div class="period-grid">

        <?php foreach ($periods as $label => $data): ?>

            <div class="period-card">

                <h3><?= $label ?></h3>

                <?php component('attendance-progress-circle', [
                    'percentage' => (float) ($data['percentage'] ?? 0),
                    'label' => 'Frequência',
                    'size' => 125
                ]); ?>

                <div class="period-metrics">

                    <span>👥 <strong><?= (int) ($data['total_students'] ?? 0) ?></strong> registros</span>
                    <span>✅ <strong><?= (int) ($data['presentes'] ?? 0) ?></strong> presentes</span>
                    <span>❌ <strong><?= (int) ($data['faltas'] ?? 0) ?></strong> faltas</span>
                    <span>🟡 <strong><?= (int) ($data['justificadas'] ?? 0) ?></strong> justificadas</span>
                    <span>🔵 <strong><?= (int) ($data['atestados'] ?? 0) ?></strong> atestados</span>
                    <span>🟣 <strong><?= (int) ($data['onibus'] ?? 0) ?></strong> ônibus</span>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>