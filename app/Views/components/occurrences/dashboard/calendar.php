<?php

$calendar = $calendar ?? [];

$selectedDateOccurrences =
    $selectedDateOccurrences ?? [];

$occurrenceTypes =
    $occurrenceTypes ?? [];

$occurrenceSeverities =
    $occurrenceSeverities ?? [];

$occurrenceSeverityClasses =
    $occurrenceSeverityClasses ?? [];

$occurrenceSeverityIcons =
    $occurrenceSeverityIcons ?? [];

$calendarYear = (int) (
    $calendarYear ?? date('Y')
);

$calendarMonth = (int) (
    $calendarMonth ?? date('n')
);

$selectedDate = (string) (
    $selectedDate ?? ''
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

$typeClasses = [
    'OBSERVATION' => 'observation',
    'WARNING' => 'warning',
    'SUSPENSION' => 'suspension',
    'REFERRAL' => 'referral',
    'PRAISE' => 'praise',
    'OTHER' => 'other',
];

$typeIcons = [
    'OBSERVATION' => 'eye',
    'WARNING' => 'triangle-alert',
    'SUSPENSION' => 'ban',
    'REFERRAL' => 'send',
    'PRAISE' => 'award',
    'OTHER' => 'file-text',
];

$monthStart = sprintf(
    '%04d-%02d-01',
    $calendarYear,
    $calendarMonth
);

$monthTimestamp = strtotime(
    $monthStart
);

$daysInMonth =
    $monthTimestamp !== false
        ? (int) date(
            't',
            $monthTimestamp
        )
        : 30;

$firstWeekDay =
    $monthTimestamp !== false
        ? (int) date(
            'w',
            $monthTimestamp
        )
        : 0;

$previousMonth =
    $calendarMonth === 1
        ? 12
        : $calendarMonth - 1;

$previousYear =
    $calendarMonth === 1
        ? $calendarYear - 1
        : $calendarYear;

$nextMonth =
    $calendarMonth === 12
        ? 1
        : $calendarMonth + 1;

$nextYear =
    $calendarMonth === 12
        ? $calendarYear + 1
        : $calendarYear;

$monthLabel =
    $monthNames[$calendarMonth]
    ?? 'Mês';

$selectedDateLabel = '';

if ($selectedDate !== '') {
    $selectedTimestamp = strtotime(
        $selectedDate
    );

    if ($selectedTimestamp !== false) {
        $selectedDateLabel = date(
            'd/m/Y',
            $selectedTimestamp
        );
    }
}

?>

<section
    class="card occurrences-calendar-widget"
>

    <div
        class="occurrences-dashboard-widget-header"
    >

        <div>

            <h3>Calendário de ocorrências</h3>

            <p>
                Visualize a quantidade de registros por dia.
            </p>

        </div>

        <i data-lucide="calendar-days"></i>

    </div>

    <div class="occurrences-calendar-layout">

        <div class="occurrences-calendar-main">

            <div
                class="occurrences-calendar-navigation"
            >

                <a
                    href="<?= base_url(
                        'ocorrencias?mes='
                        . $previousMonth
                        . '&ano='
                        . $previousYear
                        . '#occurrencesCalendar'
                    ) ?>"
                    aria-label="Mês anterior"
                >
                    <i data-lucide="chevron-left"></i>
                </a>

                <strong>
                    <?= e($monthLabel) ?>
                    <?= $calendarYear ?>
                </strong>

                <a
                    href="<?= base_url(
                        'ocorrencias?mes='
                        . $nextMonth
                        . '&ano='
                        . $nextYear
                        . '#occurrencesCalendar'
                    ) ?>"
                    aria-label="Próximo mês"
                >
                    <i data-lucide="chevron-right"></i>
                </a>

            </div>

            <div
                class="occurrences-calendar"
                id="occurrencesCalendar"
            >

                <div
                    class="occurrences-calendar-weekdays"
                >

                    <span>Dom</span>
                    <span>Seg</span>
                    <span>Ter</span>
                    <span>Qua</span>
                    <span>Qui</span>
                    <span>Sex</span>
                    <span>Sáb</span>

                </div>

                <div
                    class="occurrences-calendar-grid"
                >

                    <?php for (
                        $empty = 0;
                        $empty < $firstWeekDay;
                        $empty++
                    ): ?>

                        <div
                            class="
                                occurrences-calendar-day
                                empty
                            "
                        ></div>

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

                        $dayData =
                            $calendar[$date]
                            ?? [];

                        $total = (int) (
                            $dayData['total']
                            ?? 0
                        );

                        $open = (int) (
                            $dayData['open']
                            ?? 0
                        );

                        $resolved = (int) (
                            $dayData['resolved']
                            ?? 0
                        );

                        $intensityClass =
                            match (true) {
                                $total >= 6 =>
                                    'very-high',

                                $total >= 4 =>
                                    'high',

                                $total >= 2 =>
                                    'medium',

                                $total === 1 =>
                                    'low',

                                default =>
                                    'none',
                            };

                        $isSelected =
                            $selectedDate === $date;

                        ?>

                        <a
                            href="<?= base_url(
                                'ocorrencias?mes='
                                . $calendarMonth
                                . '&ano='
                                . $calendarYear
                                . '&dia='
                                . urlencode($date)
                                . '#occurrencesCalendar'
                            ) ?>"
                            class="
                                occurrences-calendar-day
                                <?= e(
                                    $intensityClass
                                ) ?>
                                <?= $isSelected
                                    ? 'selected'
                                    : ''
                                ?>
                            "
                        >

                            <span
                                class="
                                    occurrences-calendar-day-number
                                "
                            >
                                <?= $day ?>
                            </span>

                            <?php if ($total > 0): ?>

                                <strong>
                                    <?= $total ?>
                                </strong>

                                <small>
                                    <?= $total === 1
                                        ? 'registro'
                                        : 'registros'
                                    ?>
                                </small>

                                <div
                                    class="
                                        occurrences-calendar-day-status
                                    "
                                >

                                    <?php if (
                                        $open > 0
                                    ): ?>

                                        <span class="open">
                                            <?= $open ?>
                                            aberta(s)
                                        </span>

                                    <?php endif; ?>

                                    <?php if (
                                        $resolved > 0
                                    ): ?>

                                        <span class="resolved">
                                            <?= $resolved ?>
                                            resolvida(s)
                                        </span>

                                    <?php endif; ?>

                                </div>

                            <?php else: ?>

                                <small>
                                    Sem registros
                                </small>

                            <?php endif; ?>

                        </a>

                    <?php endfor; ?>

                </div>

            </div>
                        <div
                class="occurrences-calendar-legend"
            >

                <span>

                    <i
                        class="legend low"
                    ></i>

                    1 registro

                </span>

                <span>

                    <i
                        class="legend medium"
                    ></i>

                    2–3 registros

                </span>

                <span>

                    <i
                        class="legend high"
                    ></i>

                    4–5 registros

                </span>

                <span>

                    <i
                        class="legend very-high"
                    ></i>

                    6 ou mais

                </span>

            </div>

        </div>

        <aside
            class="occurrences-calendar-sidebar"
        >

            <div
                class="occurrences-calendar-sidebar-header"
            >

                <div>

                    <h4>
                        <?= $selectedDateLabel !== ''
                            ? e(
                                $selectedDateLabel
                            )
                            : 'Selecione um dia'
                        ?>
                    </h4>

                    <p>

                        <?= $selectedDateLabel !== ''
                            ? 'Ocorrências registradas nesta data.'
                            : 'Clique em um dia do calendário para visualizar os registros.'
                        ?>

                    </p>

                </div>

                <div class="occurrences-calendar-sidebar-actions">

                    <?php if ($selectedDate !== ''): ?>

                        <a
                            class="occurrences-calendar-clear"
                            href="<?= base_url(
                                'ocorrencias?mes='
                                . $calendarMonth
                                . '&ano='
                                . $calendarYear
                                . '#occurrencesCalendar'
                            ) ?>"
                        >
                            <i data-lucide="x"></i>
                            Limpar seleção
                        </a>

                    <?php endif; ?>

                    <i data-lucide="calendar-search"></i>

                </div>

            </div>

            <?php if (
                empty(
                    $selectedDateOccurrences
                )
            ): ?>

                <div
                    class="activity-empty"
                >

                    Nenhuma ocorrência para a data selecionada.

                </div>

            <?php else: ?>

                <div
                    class="occurrences-calendar-occurrences"
                >

                    <?php foreach (
                        $selectedDateOccurrences
                        as $occurrence
                    ): ?>

                        <?php

                        $type = strtoupper(
                            trim(
                                (string) (
                                    $occurrence['type']
                                    ?? 'OTHER'
                                )
                            )
                        );

                        $typeClass =
                            $typeClasses[$type]
                            ?? 'other';

                        $typeIcon =
                            $typeIcons[$type]
                            ?? 'file-text';

                        $typeLabel =
                            $occurrence['type_label']
                            ?? (
                                $occurrenceTypes[
                                    $type
                                ]
                                ?? 'Outro'
                            );

                        $status = strtoupper(
                            trim(
                                (string) (
                                    $occurrence['status']
                                    ?? 'OPEN'
                                )
                            )
                        );

                        $isResolved =
                            $status === 'RESOLVED';

                        $severity = strtoupper(
                            trim(
                                (string) (
                                    $occurrence[
                                        'severity'
                                    ]
                                    ?? 'LOW'
                                )
                            )
                        );

                        $severityLabel =
                            $occurrence[
                                'severity_label'
                            ]
                            ?? (
                                $occurrenceSeverities[
                                    $severity
                                ]
                                ?? 'Baixa'
                            );

                        $severityClass =
                            $occurrence[
                                'severity_class'
                            ]
                            ?? (
                                $occurrenceSeverityClasses[
                                    $severity
                                ]
                                ?? 'severity-low'
                            );

                        $severityIcon =
                            $occurrence[
                                'severity_icon'
                            ]
                            ?? (
                                $occurrenceSeverityIcons[
                                    $severity
                                ]
                                ?? 'circle-check'
                            );

                        $classParts = [];

                        if (!empty(
                            $occurrence['class_name']
                        )) {
                            $classParts[] =
                                $occurrence['class_name'];
                        }

                        if (!empty(
                            $occurrence['class_year']
                        )) {
                            $classParts[] =
                                $occurrence[
                                    'class_year'
                                ]
                                . 'º Ano';
                        }

                        if (!empty(
                            $occurrence['class_shift']
                        )) {
                            $classParts[] =
                                $occurrence[
                                    'class_shift'
                                ];
                        }

                        $classLabel =
                            !empty($classParts)
                                ? implode(
                                    ' • ',
                                    $classParts
                                )
                                : 'Sem turma';

                        $authorName = trim(
                            (string) (
                                $occurrence[
                                    'created_by_name'
                                ]
                                ?? ''
                            )
                        );

                        $authorRole = trim(
                            (string) (
                                $occurrence[
                                    'created_by_role'
                                ]
                                ?? ''
                            )
                        );

                        if (
                            $authorName === ''
                        ) {
                            $authorName =
                                'Sistema';
                        }

                        $createdAt =
                            !empty(
                                $occurrence[
                                    'created_at'
                                ]
                            )
                                ? strtotime(
                                    (string)
                                    $occurrence[
                                        'created_at'
                                    ]
                                )
                                : false;

                        ?>
                                                <article
                            class="
                                occurrences-calendar-occurrence
                                <?= e($typeClass) ?>
                                <?= e($severityClass) ?>
                            "
                        >

                            <div
                                class="
                                    occurrences-calendar-occurrence-icon
                                "
                            >

                                <i
                                    data-lucide="<?= e(
                                        $typeIcon
                                    ) ?>"
                                ></i>

                            </div>

                            <div
                                class="
                                    occurrences-calendar-occurrence-content
                                "
                            >

                                <span>
                                    <?= e($typeLabel) ?>
                                </span>

                                <h5>
                                    <?= e(
                                        $occurrence['title']
                                        ?? 'Ocorrência'
                                    ) ?>
                                </h5>

                                <div
                                    class="
                                        occurrences-calendar-occurrence-badges
                                    "
                                >

                                    <span
                                        class="
                                            occurrence-severity-badge
                                            <?= e(
                                                $severityClass
                                            ) ?>
                                        "
                                    >

                                        <i
                                            data-lucide="<?= e(
                                                $severityIcon
                                            ) ?>"
                                        ></i>

                                        <?= e(
                                            $severityLabel
                                        ) ?>

                                    </span>

                                    <span
                                        class="badge <?= $isResolved
                                            ? 'badge-success'
                                            : 'badge-warning'
                                        ?>"
                                    >
                                        <?= $isResolved
                                            ? 'Resolvida'
                                            : 'Aberta'
                                        ?>
                                    </span>

                                </div>

                                <p
                                    class="
                                        occurrences-calendar-occurrence-student
                                    "
                                >

                                    <strong>
                                        <?= e(
                                            $occurrence[
                                                'student_name'
                                            ]
                                            ?? 'Aluno'
                                        ) ?>
                                    </strong>

                                    <small>
                                        <?= e(
                                            $classLabel
                                        ) ?>
                                    </small>

                                </p>

                                <?php if (!empty($occurrence['subject_name'])): ?>

                                    <div
                                        class="
                                            occurrences-calendar-occurrence-subject
                                        "
                                    >

                                        <i
                                            data-lucide="book-open"
                                        ></i>

                                        <strong>
                                            Disciplina:
                                        </strong>

                                        <span>
                                            <?= e(
                                                $occurrence['subject_name']
                                            ) ?>
                                        </span>

                                    </div>

                                <?php endif; ?>

                                <div
                                    class="
                                        occurrences-calendar-author
                                    "
                                >

                                    <div>

                                        <i
                                            data-lucide="user-round"
                                        ></i>

                                        <strong>
                                            <?= e(
                                                $authorName
                                            ) ?>
                                        </strong>

                                        <?php if (
                                            $authorRole !== ''
                                        ): ?>

                                            <small>
                                                • <?= e(
                                                    $authorRole
                                                ) ?>
                                            </small>

                                        <?php endif; ?>

                                    </div>

                                    <span>

                                        <i
                                            data-lucide="clock-3"
                                        ></i>

                                        <?= $createdAt !== false
                                            ? date(
                                                'd/m/Y H:i',
                                                $createdAt
                                            )
                                            : '-'
                                        ?>

                                    </span>

                                </div>

                            </div>

                            <a
                                href="<?= base_url(
                                    'alunos/perfil?id='
                                    . (int) (
                                        $occurrence[
                                            'student_id'
                                        ] ?? 0
                                    )
                                    . '#studentOccurrences'
                                ) ?>"
                                class="btn-secondary"
                            >
                                Ver aluno
                            </a>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </aside>
            </div>

</section>