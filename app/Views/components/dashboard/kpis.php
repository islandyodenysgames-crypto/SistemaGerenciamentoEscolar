<div class="dashboard-kpi-panel">

    <div class="dashboard-kpi-panel-header">
        <h3>Indicadores da escola</h3>
        <span>Resumo do dia</span>
    </div>

    <div class="dashboard-kpi-panel-grid">

        <?php component('base/stat-card', [
            'icon' => 'graduation-cap',
            'label' => 'Alunos Ativos',
            'value' => $totalStudents ?? 0
        ]); ?>

        <?php component('base/stat-card', [
            'icon' => 'school',
            'label' => 'Turmas',
            'value' => $totalClasses ?? 0
        ]); ?>

        <?php component('base/stat-card', [
            'icon' => 'clipboard-check',
            'label' => 'Presentes Hoje',
            'value' => (int) ($schoolFrequencyToday['presentes'] ?? 0)
        ]); ?>

        <?php component('base/stat-card', [
            'icon' => 'circle-x',
            'label' => 'Faltas Hoje',
            'value' => (int) ($schoolFrequencyToday['faltas'] ?? 0)
        ]); ?>

    </div>

</div>