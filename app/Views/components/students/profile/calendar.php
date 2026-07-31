<?php

$student = $student ?? [];
$calendar = $calendar ?? [];
$chartMode = $chartMode ?? 'diario';

$calendarYear = (int) (
    $calendarYear ?? date('Y')
);

$calendarMonth = (int) (
    $calendarMonth ?? date('n')
);

$monthStart = sprintf(
    '%04d-%02d-01',
    $calendarYear,
    $calendarMonth
);

$daysInMonth = (int) date(
    't',
    strtotime($monthStart)
);

$firstWeekDay = (int) date(
    'w',
    strtotime($monthStart)
);

$monthNames = [
    1 => 'Janeiro',
    2 => 'Fevereiro',
    3 => 'Março',
    4 => 'Abril',
    5 => 'Maio',
    6 => 'Junho',
    7 => 'Julho',
    8 => 'Agosto',
    9 => 'Setembro',
    10 => 'Outubro',
    11 => 'Novembro',
    12 => 'Dezembro',
];

$monthLabel = $monthNames[$calendarMonth] ?? '';

$prevMonth = $calendarMonth === 1
    ? 12
    : $calendarMonth - 1;

$prevYear = $calendarMonth === 1
    ? $calendarYear - 1
    : $calendarYear;

$nextMonth = $calendarMonth === 12
    ? 1
    : $calendarMonth + 1;

$nextYear = $calendarMonth === 12
    ? $calendarYear + 1
    : $calendarYear;

$statusCalendarMap = [
    'P' => 'present',
    'F' => 'absence',
    'FJ' => 'justified',
    'AM' => 'medical',
    'FO' => 'bus',
];

?>

<div
    class="card student-calendar-card"
    id="studentCalendar"
>

    <div class="student-calendar-header">

        <div>
            <h3>Calendário escolar</h3>

            <p>
                Visualização mensal da frequência do aluno.
            </p>
        </div>

        <div class="student-calendar-nav">

            <a href="<?= base_url(
                'alunos/perfil?id='
                . (int) ($student['id'] ?? 0)
                . '&mes=' . $prevMonth
                . '&ano=' . $prevYear
                . '&grafico=' . urlencode($chartMode)
                . '#studentCalendar'
            ) ?>">
                ‹
            </a>

            <strong>
                <?= e($monthLabel) ?>
                <?= $calendarYear ?>
            </strong>

            <a href="<?= base_url(
                'alunos/perfil?id='
                . (int) ($student['id'] ?? 0)
                . '&mes=' . $nextMonth
                . '&ano=' . $nextYear
                . '&grafico=' . urlencode($chartMode)
                . '#studentCalendar'
            ) ?>">
                ›
            </a>

        </div>

    </div>

    <div class="student-calendar-weekdays">
        <span>Dom</span>
        <span>Seg</span>
        <span>Ter</span>
        <span>Qua</span>
        <span>Qui</span>
        <span>Sex</span>
        <span>Sáb</span>
    </div>

    <div class="student-calendar-grid">

        <?php for (
            $index = 0;
            $index < $firstWeekDay;
            $index++
        ): ?>

            <div class="student-calendar-day empty"></div>

        <?php endfor; ?>

        <?php for (
            $day = 1;
            $day <= $daysInMonth;
            $day++
        ): ?>

            <?php

            $date = sprintf(
                '%04d-%02d-%02d',
                $calendarYear,
                $calendarMonth,
                $day
            );

            $status = $calendar[$date] ?? null;

            $statusClass = $status
                ? (
                    $statusCalendarMap[$status]
                    ?? 'default'
                )
                : 'none';

            ?>

            <div
                class="student-calendar-day <?= e(
                    $statusClass
                ) ?>"
            >
                <strong><?= $day ?></strong>
            </div>

        <?php endfor; ?>

    </div>

    <div class="student-calendar-legend">

        <span>
            <i class="present"></i>
            Presença
        </span>

        <span>
            <i class="absence"></i>
            Falta
        </span>

        <span>
            <i class="justified"></i>
            Justificada
        </span>

        <span>
            <i class="medical"></i>
            Atestado
        </span>

        <span>
            <i class="bus"></i>
            Ônibus
        </span>

        <span>
            <i class="none"></i>
            Sem registro
        </span>

    </div>

</div>