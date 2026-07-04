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

            <input
                class="form-control"
                type="date"
                name="data"
                value="<?= e($date) ?>"
            >
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

<div class="mt-24">

    <?php component('frequency-period-summary', [
        'today' => $summary,
        'week' => [],
        'month' => [],
        'year' => [],
    ]); ?>

</div>

<div class="mt-24">

    <?php component('daily-ranking', [
        'ranking' => $ranking
    ]); ?>

</div>

<div class="mt-24">

    <?php component('classes-without-attendance', [
        'classesWithoutAttendance' => $classesWithoutAttendance
    ]); ?>

</div>