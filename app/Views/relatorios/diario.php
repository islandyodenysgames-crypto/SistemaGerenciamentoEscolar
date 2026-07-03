<?php

component('page-header', [
    'title' => 'Relatório Diário de Frequência',
    'subtitle' => 'Resumo da frequência escolar por data'
]);

?>

<div class="card">

    <form method="GET" action="<?= base_url('relatorios/diario') ?>" class="user-form">

        <div class="form-group">
            <label>Data</label>
            <input class="form-control" type="date" name="data" value="<?= htmlspecialchars($date) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Filtrar
            </button>

            <a href="<?= base_url('relatorios') ?>" class="btn-secondary">
                Voltar
            </a>
        </div>

    </form>

</div>

<div style="margin-top:24px;">

    <?php component('school-frequency-summary', [
        'schoolFrequencyToday' => $summary
    ]); ?>

</div>

<div style="margin-top:24px;">

    <?php component('daily-ranking', [
        'ranking' => $ranking
    ]); ?>

</div>

<div style="margin-top:24px;">

    <?php component('classes-without-attendance', [
        'classesWithoutAttendance' => $classesWithoutAttendance
    ]); ?>

</div>