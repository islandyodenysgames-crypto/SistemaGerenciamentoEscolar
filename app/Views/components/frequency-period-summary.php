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

    <div
        style="
            display:grid;
            grid-template-columns:repeat(4,1fr);
            gap:18px;
            margin-top:18px;
        "
    >

        <?php foreach ($periods as $label => $data): ?>

            <div
                style="
                    background:#f8fafc;
                    border-radius:18px;
                    padding:20px;
                    text-align:center;
                "
            >

                <h3 style="margin-bottom:16px;">
                    <?= $label ?>
                </h3>

                <?php component('attendance-progress-circle', [
                    'percentage' => (float) ($data['percentage'] ?? 0),
                    'label' => 'Frequência',
                    'size' => 130
                ]); ?>

                <div
                    style="
                        margin-top:18px;
                        display:grid;
                        gap:8px;
                        text-align:left;
                        font-size:15px;
                    "
                >

                    <div>
                        👥 Registros:
                        <strong><?= (int) ($data['total_students'] ?? 0) ?></strong>
                    </div>

                    <div>
                        ✅ Presentes:
                        <strong><?= (int) ($data['presentes'] ?? 0) ?></strong>
                    </div>

                    <div>
                        ❌ Faltas:
                        <strong><?= (int) ($data['faltas'] ?? 0) ?></strong>
                    </div>

                    <div>
                        🟡 Justificadas:
                        <strong><?= (int) ($data['justificadas'] ?? 0) ?></strong>
                    </div>

                    <div>
                        🔵 Atestados:
                        <strong><?= (int) ($data['atestados'] ?? 0) ?></strong>
                    </div>

                    <div>
                        🟣 Ônibus:
                        <strong><?= (int) ($data['onibus'] ?? 0) ?></strong>
                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<style>
@media (max-width: 1100px) {
    .card > div[style*="grid-template-columns:repeat(4,1fr)"] {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

@media (max-width: 700px) {
    .card > div[style*="grid-template-columns:repeat(4,1fr)"] {
        grid-template-columns: 1fr !important;
    }
}
</style>