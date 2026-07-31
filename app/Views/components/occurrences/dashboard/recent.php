<?php

$occurrences = $occurrences ?? [];
$occurrenceTypes = $occurrenceTypes ?? [];

$occurrenceStatuses = $occurrenceStatuses ?? [];
$filters = $filters ?? [];
$classes = $classes ?? [];
$subjects = $subjects ?? [];

$hasActiveFilters = false;
foreach ($filters as $filterValue) {
    if ((string) $filterValue !== '' && (int) $filterValue !== 0) {
        $hasActiveFilters = true;
        break;
    }
}

$occurrenceSeverities =
    $occurrenceSeverities ?? [];

$occurrenceSeverityClasses =
    $occurrenceSeverityClasses ?? [];

$occurrenceSeverityIcons =
    $occurrenceSeverityIcons ?? [];

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

?>

<section class="card occurrences-dashboard-widget">

    <div class="occurrences-dashboard-widget-header">

        <div>

            <h3>Últimas ocorrências</h3>

            <p>
                Registros mais recentes cadastrados no sistema.
            </p>

        </div>

        <i data-lucide="history"></i>

    </div>

    <form method="get" action="<?= e(base_url('ocorrencias')) ?>" class="occurrences-filter-form">
        <div class="occurrences-filter-grid">
            <label class="occurrences-filter-field occurrences-filter-search">
                <span>Buscar</span>
                <input type="search" name="busca" value="<?= e((string) ($filters['search'] ?? '')) ?>" placeholder="Aluno, matrícula, título ou professor">
            </label>

            <label class="occurrences-filter-field">
                <span>Turma</span>
                <select name="turma">
                    <option value="0">Todas as turmas</option>
                    <?php foreach ($classes as $class): ?>
                        <?php
                        $classId = (int) ($class['id'] ?? 0);
                        $classLabel = trim((string) ($class['name'] ?? 'Turma'));
                        $classYear = (int) ($class['year'] ?? 0);
                        $classShift = trim((string) ($class['shift'] ?? ''));
                        if ($classYear > 0) {
                            $classLabel .= ' • ' . $classYear . 'º Ano';
                        }
                        if ($classShift !== '') {
                            $classLabel .= ' • ' . $classShift;
                        }
                        ?>
                        <option value="<?= $classId ?>" <?= (int) ($filters['class_id'] ?? 0) === $classId ? 'selected' : '' ?>>
                            <?= e($classLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="occurrences-filter-field">
                <span>Disciplina</span>
                <select name="disciplina">
                    <option value="0">Todas as disciplinas</option>
                    <?php foreach ($subjects as $subject): ?>
                        <?php $subjectId = (int) ($subject['id'] ?? 0); ?>
                        <option value="<?= $subjectId ?>" <?= (int) ($filters['subject_id'] ?? 0) === $subjectId ? 'selected' : '' ?>>
                            <?= e((string) ($subject['name'] ?? 'Disciplina')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="occurrences-filter-field">
                <span>Gravidade</span>
                <select name="gravidade">
                    <option value="">Todas</option>
                    <?php foreach ($occurrenceSeverities as $code => $label): ?>
                        <option value="<?= e((string) $code) ?>" <?= ($filters['severity'] ?? '') === $code ? 'selected' : '' ?>><?= e((string) $label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="occurrences-filter-field">
                <span>Tipo</span>
                <select name="tipo">
                    <option value="">Todos</option>
                    <?php foreach ($occurrenceTypes as $code => $label): ?>
                        <option value="<?= e((string) $code) ?>" <?= ($filters['type'] ?? '') === $code ? 'selected' : '' ?>><?= e((string) $label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="occurrences-filter-field">
                <span>Status</span>
                <select name="status">
                    <option value="">Todos</option>
                    <?php foreach ($occurrenceStatuses as $code => $label): ?>
                        <option value="<?= e((string) $code) ?>" <?= ($filters['status'] ?? '') === $code ? 'selected' : '' ?>><?= e((string) $label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="occurrences-filter-field">
                <span>Data inicial</span>
                <input type="date" name="data_inicial" value="<?= e((string) ($filters['date_from'] ?? '')) ?>">
            </label>

            <label class="occurrences-filter-field">
                <span>Data final</span>
                <input type="date" name="data_final" value="<?= e((string) ($filters['date_to'] ?? '')) ?>">
            </label>
        </div>

        <div class="occurrences-filter-actions">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="filter"></i>
                Aplicar filtros
            </button>

            <?php if ($hasActiveFilters): ?>
                <a href="<?= e(base_url('ocorrencias')) ?>" class="btn btn-secondary">
                    <i data-lucide="x"></i>
                    Limpar
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="occurrences-filter-result">
        <strong><?= count($occurrences) ?></strong>
        <?= count($occurrences) === 1 ? 'ocorrência encontrada' : 'ocorrências encontradas' ?>
    </div>

    <?php if (empty($occurrences)): ?>

        <div class="activity-empty">
            Nenhuma ocorrência encontrada com os filtros selecionados.
        </div>

    <?php else: ?>

        <div class="occurrences-recent-list">

            <?php foreach (
                $occurrences
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
                        $occurrenceTypes[$type]
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
                            $occurrence['severity']
                            ?? 'LOW'
                        )
                    )
                );

                $severityLabel =
                    $occurrence['severity_label']
                    ?? (
                        $occurrenceSeverities[
                            $severity
                        ]
                        ?? 'Baixa'
                    );

                $severityClass =
                    $occurrence['severity_class']
                    ?? (
                        $occurrenceSeverityClasses[
                            $severity
                        ]
                        ?? 'severity-low'
                    );

                $severityIcon =
                    $occurrence['severity_icon']
                    ?? (
                        $occurrenceSeverityIcons[
                            $severity
                        ]
                        ?? 'circle-check'
                    );

                $classParts = [];

                $className = trim(
                    (string) (
                        $occurrence['class_name']
                        ?? ''
                    )
                );

                $classYear = (int) (
                    $occurrence['class_year']
                    ?? 0
                );

                $classShift = trim(
                    (string) (
                        $occurrence['class_shift']
                        ?? ''
                    )
                );

                if ($className !== '') {
                    $classParts[] = $className;
                }

                if ($classYear > 0) {
                    $classParts[] =
                        $classYear . 'º Ano';
                }

                if ($classShift !== '') {
                    $classParts[] =
                        $classShift;
                }

                $classLabel = !empty($classParts)
                    ? implode(' • ', $classParts)
                    : 'Sem turma';

                $authorName = trim(
                    (string) (
                        $occurrence[
                            'created_by_name'
                        ] ?? ''
                    )
                );

                $authorRole = trim(
                    (string) (
                        $occurrence[
                            'created_by_role'
                        ] ?? ''
                    )
                );

                if ($authorName === '') {
                    $authorName = 'Sistema';
                }

                $createdAt = !empty(
                    $occurrence['created_at']
                )
                    ? strtotime(
                        (string) (
                            $occurrence['created_at']
                        )
                    )
                    : false;

                $occurrenceDate = !empty(
                    $occurrence['occurrence_date']
                )
                    ? strtotime(
                        (string) (
                            $occurrence[
                                'occurrence_date'
                            ]
                        )
                    )
                    : false;

                $attachments = is_array($occurrence['attachments'] ?? null)
                    ? $occurrence['attachments']
                    : [];
                $visibleAttachments = array_slice($attachments, 0, 3);
                $remainingAttachments = array_slice($attachments, 3);

                ?>

                <article
                    class="
                        occurrences-recent-item
                        <?= e($typeClass) ?>
                        <?= e($severityClass) ?>
                    "
                >

                    <div class="occurrences-recent-icon">

                        <i
                            data-lucide="<?= e(
                                $typeIcon
                            ) ?>"
                        ></i>

                    </div>

                    <div class="occurrences-recent-content">

                        <div class="occurrences-recent-title">

                            <span>
                                <?= e($typeLabel) ?>
                            </span>

                            <h4>
                                <?= e(
                                    $occurrence['title']
                                    ?? 'Ocorrência'
                                ) ?>
                            </h4>

                        </div>

                        <p class="occurrences-recent-student">

                            <strong>
                                <?= e(
                                    $occurrence[
                                        'student_name'
                                    ]
                                    ?? 'Aluno'
                                ) ?>
                            </strong>

                            <span>•</span>

                            <?= e($classLabel) ?>

                        </p>

                        <?php if (!empty($occurrence['subject_name'])): ?>

                            <div class="occurrences-recent-subject">

                                <i data-lucide="book-open"></i>

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

                        <?php if ($attachments !== []): ?>
                            <?php component('media/attachment-carousel', ['attachments' => $attachments, 'title' => 'Imagens da ocorrência']); ?>
                            <div class="occurrences-recent-attachments">
                                <div class="occurrences-recent-attachments-heading">
                                    <i data-lucide="paperclip"></i>
                                    <strong>Arquivos</strong>
                                    <span><?= count($attachments) ?></span>
                                </div>

                                <div class="occurrences-recent-attachments-list">
                                    <?php foreach ($visibleAttachments as $attachment): ?>
                                        <a
                                            href="<?= e((string) ($attachment['url'] ?? '#')) ?>"
                                            target="_blank"
                                            rel="noopener"
                                            class="occurrences-recent-attachment"
                                            title="Abrir <?= e((string) ($attachment['original_name'] ?? 'arquivo')) ?>"
                                        >
                                            <i data-lucide="<?= e((string) ($attachment['icon'] ?? 'paperclip')) ?>"></i>
                                            <span><?= e((string) ($attachment['original_name'] ?? 'Arquivo')) ?></span>
                                            <small><?= e((string) ($attachment['size_label'] ?? '')) ?></small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>

                                <?php if ($remainingAttachments !== []): ?>
                                    <details class="occurrences-recent-attachments-more">
                                        <summary>
                                            <i data-lucide="files"></i>
                                            Ver mais <?= count($remainingAttachments) ?> <?= count($remainingAttachments) === 1 ? 'arquivo' : 'arquivos' ?>
                                        </summary>
                                        <div class="occurrences-recent-attachments-list">
                                            <?php foreach ($remainingAttachments as $attachment): ?>
                                                <a
                                                    href="<?= e((string) ($attachment['url'] ?? '#')) ?>"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="occurrences-recent-attachment"
                                                >
                                                    <i data-lucide="<?= e((string) ($attachment['icon'] ?? 'paperclip')) ?>"></i>
                                                    <span><?= e((string) ($attachment['original_name'] ?? 'Arquivo')) ?></span>
                                                    <small><?= e((string) ($attachment['size_label'] ?? '')) ?></small>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="occurrences-recent-author">

                            <div>

                                <i
                                    data-lucide="user-round"
                                ></i>

                                <strong>
                                    <?= e($authorName) ?>
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

                    <div class="occurrences-recent-meta">

                        <strong>
                            <?= $occurrenceDate !== false
                                ? date(
                                    'd/m/Y',
                                    $occurrenceDate
                                )
                                : '-'
                            ?>
                        </strong>

                        <span
                            class="
                                occurrence-severity-badge
                                <?= e($severityClass) ?>
                            "
                        >

                            <i
                                data-lucide="<?= e(
                                    $severityIcon
                                ) ?>"
                            ></i>

                            <?= e($severityLabel) ?>

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

</section>