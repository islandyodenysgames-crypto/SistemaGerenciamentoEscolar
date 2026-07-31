<?php

$summary = $summary ?? [];

$totalToday = (int) ($summary['today'] ?? 0);
$totalOpen = (int) ($summary['open'] ?? 0);
$totalResolved = (int) ($summary['resolved'] ?? 0);
$totalCurrentMonth = (int) ($summary['currentMonth'] ?? 0);

?>

<div class="dashboard-occurrence-kpis dashboard-occurrence-kpis-full">

    <a
        href="<?= base_url('ocorrencias?data_inicial=' . date('Y-m-d') . '&data_final=' . date('Y-m-d')) ?>"
        class="dashboard-occurrence-kpi today"
    >

        <i data-lucide="calendar-days"></i>

        <div>
            <span>Hoje</span>
            <strong><?= $totalToday ?></strong>
            <small>Registros realizados</small>
        </div>

    </a>

    <a
        href="<?= base_url('ocorrencias?status=OPEN') ?>"
        class="dashboard-occurrence-kpi open"
    >

        <i data-lucide="clock-alert"></i>

        <div>
            <span>Abertas</span>
            <strong><?= $totalOpen ?></strong>
            <small>Aguardando resolução</small>
        </div>

    </a>

    <a
        href="<?= base_url('ocorrencias?status=RESOLVED') ?>"
        class="dashboard-occurrence-kpi resolved"
    >

        <i data-lucide="circle-check-big"></i>

        <div>
            <span>Resolvidas</span>
            <strong><?= $totalResolved ?></strong>
            <small>Registros encerrados</small>
        </div>

    </a>

    <a
        href="<?= base_url('ocorrencias?data_inicial=' . date('Y-m-01') . '&data_final=' . date('Y-m-t')) ?>"
        class="dashboard-occurrence-kpi month"
    >

        <i data-lucide="calendar-range"></i>

        <div>
            <span>Este mês</span>
            <strong><?= $totalCurrentMonth ?></strong>
            <small>Total mensal</small>
        </div>

    </a>

</div>