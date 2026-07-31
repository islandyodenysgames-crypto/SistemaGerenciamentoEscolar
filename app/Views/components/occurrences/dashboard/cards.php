<?php

$totalToday = (int) ($totalToday ?? 0);
$totalOpen = (int) ($totalOpen ?? 0);
$totalResolved = (int) ($totalResolved ?? 0);
$totalCurrentMonth = (int) ($totalCurrentMonth ?? 0);

?>

<div class="occurrences-dashboard-cards">

    <div class="occurrences-dashboard-card today">

        <div class="occurrences-dashboard-card-icon">
            <i data-lucide="calendar-days"></i>
        </div>

        <div>
            <span>Hoje</span>
            <strong><?= $totalToday ?></strong>
            <small>Ocorrências registradas</small>
        </div>

    </div>

    <div class="occurrences-dashboard-card open">

        <div class="occurrences-dashboard-card-icon">
            <i data-lucide="clock-alert"></i>
        </div>

        <div>
            <span>Abertas</span>
            <strong><?= $totalOpen ?></strong>
            <small>Aguardando resolução</small>
        </div>

    </div>

    <div class="occurrences-dashboard-card resolved">

        <div class="occurrences-dashboard-card-icon">
            <i data-lucide="circle-check-big"></i>
        </div>

        <div>
            <span>Resolvidas</span>
            <strong><?= $totalResolved ?></strong>
            <small>Ocorrências encerradas</small>
        </div>

    </div>

    <div class="occurrences-dashboard-card month">

        <div class="occurrences-dashboard-card-icon">
            <i data-lucide="calendar-range"></i>
        </div>

        <div>
            <span>Este mês</span>
            <strong><?= $totalCurrentMonth ?></strong>
            <small>Total mensal</small>
        </div>

    </div>

</div>