<?php

$student = $student ?? [];
$statistics = $statistics ?? [];
$attendanceEvolution = $attendanceEvolution ?? [];
$occurrences = $occurrences ?? [];
$occurrenceTypes = $occurrenceTypes ?? [];

$reportYear = (int) ($reportYear ?? date('Y'));

$attendancePercentage = (float) (
    $statistics['attendance_percentage'] ?? 0
);

$totalRecords = (int) (
    $statistics['total_records'] ?? 0
);

$totalPresentes = (int) (
    $statistics['total_presentes'] ?? 0
);

$totalFaltas = (int) (
    $statistics['total_faltas'] ?? 0
);

$totalJustificadas = (int) (
    $statistics['total_justificadas'] ?? 0
);

$monthLabels = [
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

$filteredEvolution = array_values(
    array_filter(
        $attendanceEvolution,
        static fn (array $item): bool =>
            (int) ($item['year'] ?? 0) === $reportYear
    )
);

$filteredOccurrences = array_values(
    array_filter(
        $occurrences,
        static function (array $occurrence) use ($reportYear): bool {
            if (empty($occurrence['occurrence_date'])) {
                return false;
            }

            $timestamp = strtotime(
                (string) $occurrence['occurrence_date']
            );

            if ($timestamp === false) {
                return false;
            }

            return (int) date('Y', $timestamp) === $reportYear;
        }
    )
);

$occurrenceSummary = [
    'adverse' => 0,
    'responses' => 0,
    'positive' => 0,
];

foreach ($filteredOccurrences as $occurrence) {
    $nature = strtoupper((string) ($occurrence['nature'] ?? 'ADVERSE'));

    if ($nature === 'POSITIVE' || strtoupper((string) ($occurrence['type'] ?? '')) === 'PRAISE') {
        $occurrenceSummary['positive']++;
    } elseif ($nature === 'RESPONSE') {
        $occurrenceSummary['responses']++;
    } else {
        $occurrenceSummary['adverse']++;
    }
}

component('base/page-header', [
    'title' => 'Relatório Individual do Aluno',
    'subtitle' => 'Relatório de frequência escolar',
]);

?>

<div class="student-report-actions no-print">

    <a
        href="<?= base_url(
            'alunos/perfil?id=' . (int) ($student['id'] ?? 0)
        ) ?>"
        class="btn-secondary"
    >
        Voltar ao perfil
    </a>

    <button
        type="button"
        class="btn-primary"
        onclick="window.print()"
    >
        Imprimir / Salvar em PDF
    </button>

</div>

<main class="student-report-sheet">

    <header class="student-report-header">

        <div class="student-report-brand">

            <?php component('school/branding', [
                'variant' => 'report',
            ]); ?>

        </div>

        <div class="student-report-title">

            <h1>Relatório Individual de Frequência</h1>

            <p>
                Ano letivo:
                <strong><?= $reportYear ?></strong>
            </p>

        </div>

        <div class="student-report-date">

            <span>Emitido em</span>

            <strong>
                <?= date('d/m/Y') ?>
            </strong>

            <small>
                <?= date('H:i') ?>
            </small>

        </div>

    </header>

    <section class="student-report-section">

        <div class="student-report-section-title">
            <h2>Identificação do aluno</h2>
        </div>

        <div class="student-report-student-grid">

            <div>
                <span>Nome completo</span>

                <strong>
                    <?= e($student['name'] ?? '-') ?>
                </strong>
            </div>

            <div>
                <span>Matrícula</span>

                <strong>
                    <?= e($student['registration'] ?? '-') ?>
                </strong>
            </div>

            <div>
                <span>Data de nascimento</span>

                <strong>
                    <?php if (!empty($student['birth_date'])): ?>

                        <?= date(
                            'd/m/Y',
                            strtotime(
                                (string) $student['birth_date']
                            )
                        ) ?>

                    <?php else: ?>

                        -

                    <?php endif; ?>
                </strong>
            </div>

            <div>
                <span>Situação</span>

                <strong>
                    <?= (int) ($student['active'] ?? 0) === 1
                        ? 'Ativo'
                        : 'Inativo'
                    ?>
                </strong>
            </div>

            <div>
                <span>Responsável</span>

                <strong>
                    <?= e($student['guardian_name'] ?? '-') ?>
                </strong>
            </div>

            <div>
                <span>Telefone do responsável</span>

                <strong>
                    <?= e($student['guardian_phone'] ?? '-') ?>
                </strong>
            </div>

        </div>

    </section>

    <section class="student-report-section">

        <div class="student-report-section-title">
            <h2>Resumo da frequência</h2>
        </div>

        <div class="student-report-kpis">

            <div>
                <span>Frequência geral</span>

                <strong>
                    <?= number_format(
                        $attendancePercentage,
                        1,
                        ',',
                        '.'
                    ) ?>%
                </strong>
            </div>

            <div>
                <span>Total de chamadas</span>

                <strong>
                    <?= $totalRecords ?>
                </strong>
            </div>

            <div>
                <span>Presenças</span>

                <strong>
                    <?= $totalPresentes ?>
                </strong>
            </div>

            <div>
                <span>Faltas</span>

                <strong>
                    <?= $totalFaltas ?>
                </strong>
            </div>

            <div>
                <span>Justificadas</span>

                <strong>
                    <?= $totalJustificadas ?>
                </strong>
            </div>

        </div>

    </section>

    <section class="student-report-section">

        <div class="student-report-section-title">
            <h2>Resumo mensal</h2>
        </div>

        <?php if (empty($filteredEvolution)): ?>

            <div class="student-report-empty">
                Nenhum dado mensal encontrado para o ano selecionado.
            </div>

        <?php else: ?>

            <div class="student-report-table-wrap">

                <table class="student-report-table">

                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Presenças</th>
                            <th>Faltas</th>
                            <th>Justificadas</th>
                            <th>Atestados</th>
                            <th>Transporte</th>
                            <th>Total</th>
                            <th>Frequência</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($filteredEvolution as $month): ?>

                            <?php

                            $monthNumber = (int) (
                                $month['month'] ?? 0
                            );

                            $monthPercentage = (float) (
                                $month['percentage'] ?? 0
                            );

                            ?>

                            <tr>
                                <td>
                                    <?= e(
                                        $monthLabels[$monthNumber]
                                        ?? 'Mês'
                                    ) ?>
                                </td>

                                <td>
                                    <?= (int) (
                                        $month['total_presentes'] ?? 0
                                    ) ?>
                                </td>

                                <td><?= (int) ($month['total_faltas'] ?? 0) ?></td>
                                <td><?= (int) ($month['total_faltas_justificadas'] ?? 0) ?></td>
                                <td><?= (int) ($month['total_atestados_medicos'] ?? 0) ?></td>
                                <td><?= (int) ($month['total_faltas_onibus'] ?? 0) ?></td>
                                <td><?= (int) ($month['total_records'] ?? 0) ?></td>

                                <td>
                                    <strong>
                                        <?= number_format(
                                            $monthPercentage,
                                            1,
                                            ',',
                                            '.'
                                        ) ?>%
                                    </strong>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>

    <section class="student-report-section">

        <div class="student-report-section-title">

            <h2>Intervenções e registros escolares</h2>

            <p>
                Situações que exigem atenção, respostas pedagógicas e
                reconhecimentos positivos cadastrados em <?= $reportYear ?>.
            </p>

        </div>

        <?php if (empty($filteredOccurrences)): ?>

            <div class="student-report-empty">
                Nenhuma ocorrência encontrada para o ano selecionado.
            </div>

        <?php else: ?>

            <div class="student-report-occurrence-summary">
                <div>
                    <strong><?= $occurrenceSummary['adverse'] ?></strong>
                    <span>Situações de atenção</span>
                </div>
                <div>
                    <strong><?= $occurrenceSummary['responses'] ?></strong>
                    <span>Respostas pedagógicas</span>
                </div>
                <div class="positive">
                    <strong><?= $occurrenceSummary['positive'] ?></strong>
                    <span>Reconhecimentos positivos</span>
                </div>
            </div>

            <div class="student-report-occurrences">

                <?php foreach ($filteredOccurrences as $occurrence): ?>

                    <?php

                    $type = (string) (
                        $occurrence['type'] ?? 'OTHER'
                    );

                    $nature = strtoupper((string) (
                        $occurrence['nature'] ?? 'ADVERSE'
                    ));

                    $typeClass = match ($nature) {
                        'POSITIVE' => 'praise',
                        'RESPONSE' => 'referral',
                        default => match ($type) {
                        'OBSERVATION' => 'observation',
                        
                        'WARNING' => 'warning',
                        
                        'SUSPENSION' => 'suspension',
                        
                        'REFERRAL' => 'referral',
                        
                        'PRAISE' => 'praise',
                        
                        default => 'other',
                        },
                    };

                    $status = (string) (
                        $occurrence['status'] ?? 'OPEN'
                    );

                    $typeLabel = (string) (
                        $occurrence['type_label']
                        ?? ($occurrenceTypes[$type] ?? 'Outro')
                    );

                    $statusLabel = $status === 'RESOLVED'
                        ? 'Resolvida'
                        : 'Aberta';

                    ?>

                    <article
                         class="
                             student-report-occurrence
                             <?= e($typeClass) ?>
                             "
                    >

                        <div class="student-report-occurrence-header">

                            <div>

                                <span>
                                    <?= e($typeLabel) ?>
                                </span>

                                <h3>
                                    <?= e(
                                        $occurrence['title']
                                        ?? 'Ocorrência'
                                    ) ?>
                                </h3>

                            </div>

                            <div class="student-report-occurrence-meta">

                                <strong>
                                    <?= !empty(
                                        $occurrence['occurrence_date']
                                    )
                                        ? date(
                                            'd/m/Y',
                                            strtotime(
                                                (string) $occurrence[
                                                    'occurrence_date'
                                                ]
                                            )
                                        )
                                        : '-'
                                    ?>
                                </strong>

                                <small>
                                    <?= e($statusLabel) ?>
                                </small>

                            </div>

                        </div>

                        <div class="student-report-occurrence-context">
                            <span>
                                <strong>Disciplina</strong>
                                <?= e((string) ($occurrence['subject_name'] ?? 'Não informada')) ?>
                            </span>
                            <span>
                                <strong>Finalidade</strong>
                                <?= e(match ($nature) {
                                    'POSITIVE' => 'Reconhecimento positivo',
                                    'RESPONSE' => 'Resposta pedagógica pontual',
                                    default => 'Situação que exige atenção',
                                }) ?>
                            </span>
                            <?php if (!empty($occurrence['focus_area_label'])): ?>
                                <span>
                                    <strong>Área relacionada</strong>
                                    <?= e((string) $occurrence['focus_area_label']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="student-report-occurrence-description">
                            <?= nl2br(
                                e(
                                    $occurrence['description']
                                    ?? ''
                                )
                            ) ?>
                        </p>

                        <?php if (!empty(
                            $occurrence['actions_taken']
                        )): ?>

                            <div class="student-report-occurrence-actions">

                                <strong>
                                    Providências adotadas
                                </strong>

                                <p>
                                    <?= nl2br(
                                        e(
                                            $occurrence[
                                                'actions_taken'
                                            ]
                                        )
                                    ) ?>
                                </p>

                            </div>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <section class="student-report-section student-report-observations">

        <div class="student-report-section-title">
            <h2>Observações</h2>
        </div>

        <div class="student-report-observation-lines">
            <span></span>
            <span></span>
            <span></span>
        </div>

    </section>

    <section class="student-report-signatures">

        <div>
            <span></span>
            <strong>Responsável pelo aluno</strong>
        </div>

        <div>
            <span></span>
            <strong>Representante da escola</strong>
        </div>

    </section>

    <footer class="student-report-footer">

        <strong>
            <?= e(school('name', app_name())) ?>
        </strong>

        <span>
            Documento gerado automaticamente pelo Sistema de Gerenciamento Escolar.
        </span>

    </footer>

</main>
