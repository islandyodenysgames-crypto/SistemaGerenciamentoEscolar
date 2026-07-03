<div class="card">

    <div class="card-header">
        <h3>Frequência Geral da Escola — Hoje</h3>
    </div>

    <div class="dashboard-grid">

        <?php
        component('stat-card', [
            'icon' => 'clipboard-check',
            'label' => 'Presentes',
            'value' => (int) ($schoolFrequencyToday['presentes'] ?? 0)
        ]);

        component('stat-card', [
            'icon' => 'circle-x',
            'label' => 'Faltas',
            'value' => (int) ($schoolFrequencyToday['faltas'] ?? 0)
        ]);

        component('stat-card', [
            'icon' => 'file-check',
            'label' => 'Justificadas',
            'value' => (int) ($schoolFrequencyToday['justificadas'] ?? 0)
        ]);

        component('stat-card', [
            'icon' => 'bus',
            'label' => 'Ônibus',
            'value' => (int) ($schoolFrequencyToday['onibus'] ?? 0)
        ]);
        ?>

    </div>

    <div style="margin-top:20px;font-size:24px;font-weight:800;">
        Frequência Geral: <?= $schoolFrequencyToday['percentage'] ?? 0 ?>%
    </div>

</div>