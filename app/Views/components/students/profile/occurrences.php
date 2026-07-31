<?php

use App\Auth\Permissions;
use App\Core\Authorization;
use App\Core\Session;

$student = $student ?? [];
$occurrences = $occurrences ?? [];
$occurrenceActions = $occurrenceActions ?? [];

$occurrenceActionTypes =
    $occurrenceActionTypes ?? [];

$occurrenceActionTypeIcons =
    $occurrenceActionTypeIcons ?? [];

$occurrenceTypes =
    $occurrenceTypes ?? [];

$occurrenceTypeCounts =
    $occurrenceTypeCounts ?? [];

$occurrenceSeverities =
    $occurrenceSeverities ?? [];

$occurrenceSeverityClasses =
    $occurrenceSeverityClasses ?? [];

$occurrenceSeverityIcons =
    $occurrenceSeverityIcons ?? [];

$occurrenceSeverityCounts =
    $occurrenceSeverityCounts ?? [];

$totalOccurrences = (int) (
    $totalOccurrences ?? 0
);

$openOccurrences = (int) (
    $openOccurrences ?? 0
);

$resolvedOccurrences = (int) (
    $resolvedOccurrences ?? 0
);

$loggedUser = Session::get('user');

$currentUserId = 0;

if (is_array($loggedUser)) {
    $currentUserId = (int) (
        $loggedUser['id'] ?? 0
    );
} elseif (is_object($loggedUser)) {
    $currentUserId = (int) (
        $loggedUser->id ?? 0
    );
}

$canManageAllOccurrences = Authorization::can(
    Permissions::OCCURRENCES_MANAGE
);

$canCreateOccurrences = Authorization::can(
    Permissions::OCCURRENCES_CREATE
);

$canViewOccurrences = Authorization::can(
    Permissions::OCCURRENCES_VIEW
);

$studentProfileReturnUrl = base_url(
    'alunos/perfil?id=' . (int) ($student['id'] ?? 0) . '#studentOccurrences'
);

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

$monthLabels = [
    1 => 'JAN',
    2 => 'FEV',
    3 => 'MAR',
    4 => 'ABR',
    5 => 'MAI',
    6 => 'JUN',
    7 => 'JUL',
    8 => 'AGO',
    9 => 'SET',
    10 => 'OUT',
    11 => 'NOV',
    12 => 'DEZ',
];

$severityDescriptions = [
    'LOW' =>
        'Situações de menor impacto.',

    'MEDIUM' =>
        'Situações que exigem atenção.',

    'HIGH' =>
        'Situações que requerem prioridade.',

    'CRITICAL' =>
        'Situações que exigem ação imediata.',
];

$groupedOccurrences = [];

foreach ($occurrences as $occurrence) {
    $date = (string) (
        $occurrence['occurrence_date'] ?? ''
    );

    if ($date === '') {
        $year = 'Sem data';
    } else {
        $timestamp = strtotime($date);

        $year = $timestamp !== false
            ? date('Y', $timestamp)
            : 'Sem data';
    }

    $groupedOccurrences[$year][] =
        $occurrence;
}

?>

<section
    class="card student-occurrences-card"
    id="studentOccurrences"
>

    <?php component('base/alert', [
        'type' => 'success',
        'message' =>
            $occurrenceSuccess ?? null,
    ]); ?>

    <?php component('base/alert', [
        'type' => 'danger',
        'message' =>
            $occurrenceError ?? null,
    ]); ?>

    <?php component('base/alert', [
        'type' => 'warning',
        'message' =>
            $occurrenceWarning ?? null,
    ]); ?>

    <div class="student-occurrences-header">

        <div>

            <h3>Ocorrências</h3>

            <p>
                Linha do tempo de registros,
                acompanhamentos e providências do aluno.
            </p>

        </div>

        <?php if ($canCreateOccurrences): ?>

            <a
                href="<?= base_url(
                    'ocorrencias/nova?aluno='
                    . (int) (
                        $student['id'] ?? 0
                    )
                ) ?>"
                class="btn-primary"
            >
                <i data-lucide="plus"></i>
                Nova ocorrência
            </a>

        <?php endif; ?>

    </div>

    <div class="student-occurrences-stats">

        <div>

            <span>Total</span>

            <strong>
                <?= $totalOccurrences ?>
            </strong>

        </div>

        <div class="open">

            <span>Abertas</span>

            <strong>
                <?= $openOccurrences ?>
            </strong>

        </div>

        <div class="resolved">

            <span>Resolvidas</span>

            <strong>
                <?= $resolvedOccurrences ?>
            </strong>

        </div>

    </div>

    <div class="occurrence-severity-summary">

        <?php foreach (
            $occurrenceSeverities
            as $severityCode => $severityLabel
        ): ?>

            <?php

            $severityCode = strtoupper(
                (string) $severityCode
            );

            $severityClass =
                $occurrenceSeverityClasses[
                    $severityCode
                ]
                ?? 'severity-low';

            $severityIcon =
                $occurrenceSeverityIcons[
                    $severityCode
                ]
                ?? 'circle-check';

            $severityTotal = (int) (
                $occurrenceSeverityCounts[
                    $severityCode
                ] ?? 0
            );

            ?>

            <div
                class="
                    occurrence-severity-summary-item
                    <?= e($severityClass) ?>
                "
            >

                <div
                    class="
                        occurrence-severity-summary-icon
                        <?= e($severityClass) ?>
                    "
                >
                    <i
                        data-lucide="<?= e(
                            $severityIcon
                        ) ?>"
                    ></i>
                </div>

                <div>

                    <span>
                        <?= e($severityLabel) ?>
                    </span>

                    <strong>
                        <?= $severityTotal ?>
                    </strong>

                    <small>
                        <?= e(
                            $severityDescriptions[
                                $severityCode
                            ] ?? ''
                        ) ?>
                    </small>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

    <div class="student-occurrences-type-stats">

        <div class="observation">

            <i data-lucide="eye"></i>

            <div class="info">

                <span>Observações</span>

                <strong>
                    <?= (int) (
                        $occurrenceTypeCounts[
                            'OBSERVATION'
                        ] ?? 0
                    ) ?>
                </strong>

            </div>

        </div>

        <div class="warning">

            <i data-lucide="triangle-alert"></i>

            <div class="info">

                <span>Advertências</span>

                <strong>
                    <?= (int) (
                        $occurrenceTypeCounts[
                            'WARNING'
                        ] ?? 0
                    ) ?>
                </strong>

            </div>

        </div>

        <div class="suspension">

            <i data-lucide="ban"></i>

            <div class="info">

                <span>Suspensões</span>

                <strong>
                    <?= (int) (
                        $occurrenceTypeCounts[
                            'SUSPENSION'
                        ] ?? 0
                    ) ?>
                </strong>

            </div>

        </div>

        <div class="referral">

            <i data-lucide="send"></i>

            <div class="info">

                <span>Encaminhamentos</span>

                <strong>
                    <?= (int) (
                        $occurrenceTypeCounts[
                            'REFERRAL'
                        ] ?? 0
                    ) ?>
                </strong>

            </div>

        </div>

        <div class="praise">

            <i data-lucide="award"></i>

            <div class="info">

                <span>Elogios</span>

                <strong>
                    <?= (int) (
                        $occurrenceTypeCounts[
                            'PRAISE'
                        ] ?? 0
                    ) ?>
                </strong>

            </div>

        </div>

        <div class="other">

            <i data-lucide="file-text"></i>

            <div class="info">

                <span>Outros</span>

                <strong>
                    <?= (int) (
                        $occurrenceTypeCounts[
                            'OTHER'
                        ] ?? 0
                    ) ?>
                </strong>

            </div>

        </div>

    </div>
        <?php if (empty($occurrences)): ?>

        <div class="activity-empty">
            Nenhuma ocorrência cadastrada para este aluno.
        </div>

    <?php else: ?>

        <div class="student-occurrences-timeline">

            <?php foreach (
                $groupedOccurrences
                as $year => $yearOccurrences
            ): ?>

                <div class="student-occurrences-year">

                    <div class="student-occurrences-year-label">
                        <?= e((string) $year) ?>
                    </div>

                    <div class="student-occurrences-year-items">

                        <?php foreach (
                            $yearOccurrences
                            as $occurrence
                        ): ?>

                            <?php

                            $occurrenceId = (int) (
                                $occurrence['id'] ?? 0
                            );

                            $type = strtoupper(
                                trim(
                                    (string) (
                                        $occurrence['type']
                                        ?? 'OTHER'
                                    )
                                )
                            );

                            $status = strtoupper(
                                trim(
                                    (string) (
                                        $occurrence['status']
                                        ?? 'OPEN'
                                    )
                                )
                            );

                            $severity = strtoupper(
                                trim(
                                    (string) (
                                        $occurrence['severity']
                                        ?? 'LOW'
                                    )
                                )
                            );

                            $typeLabel =
                                $occurrenceTypes[$type]
                                ?? 'Outro';

                            $typeClass =
                                $typeClasses[$type]
                                ?? 'other';

                            $typeIcon =
                                $typeIcons[$type]
                                ?? 'file-text';

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

                            $isResolved =
                                $status === 'RESOLVED';

                            $occurrenceDate = (string) (
                                $occurrence[
                                    'occurrence_date'
                                ] ?? ''
                            );

                            $occurrenceTimestamp =
                                $occurrenceDate !== ''
                                    ? strtotime(
                                        $occurrenceDate
                                    )
                                    : false;

                            $day =
                                $occurrenceTimestamp !== false
                                    ? date(
                                        'd',
                                        $occurrenceTimestamp
                                    )
                                    : '--';

                            $monthNumber =
                                $occurrenceTimestamp !== false
                                    ? (int) date(
                                        'n',
                                        $occurrenceTimestamp
                                    )
                                    : 0;

                            $month =
                                $monthLabels[$monthNumber]
                                ?? '---';

                            $occurrenceCreatedBy = (int) (
                                $occurrence[
                                    'created_by'
                                ] ?? 0
                            );

                            $isOccurrenceOwner =
                                $currentUserId > 0
                                && $occurrenceCreatedBy > 0
                                && $currentUserId
                                    === $occurrenceCreatedBy;

                            $canManageThisOccurrence =
                                $canManageAllOccurrences
                                || (
                                    $canCreateOccurrences
                                    && $isOccurrenceOwner
                                );

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
                                    (string) $occurrence[
                                        'created_at'
                                    ]
                                )
                                : false;

                            $actions = $occurrenceActions[
                                $occurrenceId
                            ] ?? [];

                            ?>

                            <article
                                class="
                                    student-occurrence-timeline-item
                                    student-occurrence-<?= e(
                                        $typeClass
                                    ) ?>
                                    <?= e($severityClass) ?>
                                    <?= $isResolved
                                        ? 'is-resolved'
                                        : 'is-open'
                                    ?>
                                "
                            >

                                <div
                                    class="student-occurrence-timeline-date"
                                >

                                    <strong>
                                        <?= e($day) ?>
                                    </strong>

                                    <span>
                                        <?= e($month) ?>
                                    </span>

                                </div>

                                <div
                                    class="student-occurrence-timeline-marker"
                                >

                                    <div
                                        class="student-occurrence-icon"
                                    >
                                        <i
                                            data-lucide="<?= e(
                                                $typeIcon
                                            ) ?>"
                                        ></i>
                                    </div>

                                </div>

                                <div
                                    class="
                                        student-occurrence-timeline-card
                                        <?= e($severityClass) ?>
                                    "
                                >

                                    <div
                                        class="student-occurrence-title-row"
                                    >

                                        <div>

                                            <span
                                                class="student-occurrence-type"
                                            >
                                                <?= e($typeLabel) ?>
                                            </span>

                                            <h4>
                                                <?= e(
                                                    $occurrence[
                                                        'title'
                                                    ]
                                                    ?? 'Ocorrência'
                                                ) ?>
                                            </h4>

                                        </div>

                                        <div
                                            class="
                                                student-occurrence-badges
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

                                    </div>

                                    <div
                                        class="student-occurrence-description"
                                    >
                                        <?= nl2br(
                                            e(
                                                $occurrence[
                                                    'description'
                                                ] ?? ''
                                            )
                                        ) ?>
                                    </div>

                                    <?php if (
                                        !empty(
                                            $occurrence['subject_name']
                                        )
                                    ): ?>
                                        <div
                                            class="student-occurrence-subject"
                                        >

                                            <i
                                                data-lucide="book-open"
                                            ></i>

                                            <strong>
                                                Disciplina
                                            </strong>

                                            <span>
                                                <?= e(
                                                    $occurrence[
                                                        'subject_name'
                                                    ]
                                                ) ?>
                                            </span>

                                        </div>
                                    <?php endif; ?>

                                    <?php
                                    $attachments = $occurrence['attachments'] ?? [];
                                    ?>

                                    <?php if ($attachments !== []): ?>
                                        <?php component('media/attachment-carousel', ['attachments' => $attachments, 'title' => 'Imagens da ocorrência']); ?>
                                        <div class="student-occurrence-attachments">
                                            <div class="student-occurrence-attachments-heading">
                                                <i data-lucide="paperclip"></i>
                                                <strong>Arquivos</strong>
                                                <span><?= count($attachments) ?></span>
                                            </div>
                                            <div class="student-occurrence-attachments-list">
                                                <?php foreach ($attachments as $attachment): ?>
                                                    <div class="student-occurrence-attachment-row">
                                                        <a
                                                            href="<?= e((string) ($attachment['url'] ?? '#')) ?>"
                                                            target="_blank"
                                                            rel="noopener"
                                                            class="student-occurrence-attachment"
                                                        >
                                                            <i data-lucide="<?= e((string) ($attachment['icon'] ?? 'paperclip')) ?>"></i>
                                                            <span>
                                                                <strong><?= e((string) ($attachment['original_name'] ?? 'Arquivo')) ?></strong>
                                                                <small><?= e((string) ($attachment['size_label'] ?? '')) ?></small>
                                                            </span>
                                                            <i data-lucide="external-link"></i>
                                                        </a>

                                                        <?php if ($canManageThisOccurrence): ?>
                                                            <form
                                                                method="POST"
                                                                action="<?= base_url('ocorrencias/anexos/excluir') ?>"
                                                                onsubmit="return confirm('Deseja realmente excluir o anexo <?= e(addslashes((string) ($attachment['original_name'] ?? 'selecionado'))) ?>?');"
                                                                class="student-occurrence-attachment-remove-form"
                                                            >
                                                                <input type="hidden" name="attachment_id" value="<?= (int) ($attachment['id'] ?? 0) ?>">
                                                                <input type="hidden" name="source" value="student_profile">
                                                                <input type="hidden" name="return_to" value="<?= e($studentProfileReturnUrl) ?>">
                                                                <button
                                                                    type="submit"
                                                                    class="student-occurrence-attachment-remove"
                                                                    aria-label="Excluir anexo <?= e((string) ($attachment['original_name'] ?? '')) ?>"
                                                                    title="Excluir anexo"
                                                                >
                                                                    <i data-lucide="trash-2"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div
                                        class="student-occurrence-author"
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

                                        <div>

                                            <i
                                                data-lucide="clock-3"
                                            ></i>

                                            <span>
                                                <?= $createdAt !== false
                                                    ? date(
                                                        'd/m/Y \à\s H:i',
                                                        $createdAt
                                                    )
                                                    : '-'
                                                ?>
                                            </span>

                                        </div>

                                    </div>

                                    <?php if (
                                        !empty(
                                            $occurrence[
                                                'actions_taken'
                                            ]
                                        )
                                    ): ?>

                                        <div
                                            class="
                                                student-occurrence-actions-taken
                                                occurrence-initial-action
                                            "
                                        >

                                            <div
                                                class="occurrence-action-heading"
                                            >

                                                <strong>
                                                    Providência inicial
                                                </strong>

                                                <span
                                                    class="badge badge-warning"
                                                >
                                                    Registro original
                                                </span>

                                            </div>

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
                                                                        <div
                                        class="occurrence-actions-timeline"
                                    >

                                        <div
                                            class="occurrence-actions-header"
                                        >

                                            <div>

                                                <i
                                                    data-lucide="history"
                                                ></i>

                                                <strong>
                                                    Providências e intervenções
                                                </strong>

                                            </div>

                                            <span
                                                class="badge <?= !empty($actions)
                                                    ? 'badge-success'
                                                    : 'badge-warning'
                                                ?>"
                                            >
                                                <?= count($actions) ?>
                                                registro(s)
                                            </span>

                                        </div>

                                        <?php if (empty($actions)): ?>

                                            <div
                                                class="occurrence-actions-empty"
                                            >
                                                Nenhuma providência por etapa
                                                foi registrada ainda.
                                            </div>

                                        <?php else: ?>

                                            <div
                                                class="occurrence-actions-list"
                                            >

                                                <?php foreach (
                                                    $actions
                                                    as $action
                                                ): ?>

                                                    <?php

                                                    $actionType = strtoupper(
                                                        trim(
                                                            (string) (
                                                                $action[
                                                                    'action_type'
                                                                ]
                                                                ?? 'OTHER'
                                                            )
                                                        )
                                                    );

                                                    $actionTypeLabel =
                                                        $occurrenceActionTypes[
                                                            $actionType
                                                        ]
                                                        ?? 'Outro';

                                                    $actionTypeIcon =
                                                        $occurrenceActionTypeIcons[
                                                            $actionType
                                                        ]
                                                        ?? 'file-text';

                                                    $actionStatus =
                                                        strtoupper(
                                                            trim(
                                                                (string) (
                                                                    $action[
                                                                        'status_after'
                                                                    ]
                                                                    ?? 'OPEN'
                                                                )
                                                            )
                                                        );

                                                    $actionResolved =
                                                        $actionStatus
                                                        === 'RESOLVED';

                                                    $actionAuthor = trim(
                                                        (string) (
                                                            $action[
                                                                'created_by_name'
                                                            ] ?? ''
                                                        )
                                                    );

                                                    $actionRole = trim(
                                                        (string) (
                                                            $action[
                                                                'created_by_role'
                                                            ] ?? ''
                                                        )
                                                    );

                                                    if (
                                                        $actionAuthor === ''
                                                    ) {
                                                        $actionAuthor =
                                                            'Sistema';
                                                    }

                                                    $actionTimestamp =
                                                        !empty(
                                                            $action[
                                                                'created_at'
                                                            ]
                                                        )
                                                            ? strtotime(
                                                                (string) $action[
                                                                    'created_at'
                                                                ]
                                                            )
                                                            : false;

                                                    ?>

                                                    <article
                                                        class="
                                                            occurrence-action-item
                                                            <?= $actionResolved
                                                                ? 'is-resolved'
                                                                : 'is-open'
                                                            ?>
                                                        "
                                                    >

                                                        <div
                                                            class="
                                                                occurrence-action-marker
                                                            "
                                                        >

                                                            <i
                                                                data-lucide="<?= e(
                                                                    $actionTypeIcon
                                                                ) ?>"
                                                            ></i>

                                                        </div>

                                                        <div
                                                            class="
                                                                occurrence-action-content
                                                            "
                                                        >

                                                            <div
                                                                class="
                                                                    occurrence-action-type
                                                                "
                                                            >

                                                                <i
                                                                    data-lucide="<?= e(
                                                                        $actionTypeIcon
                                                                    ) ?>"
                                                                ></i>

                                                                <strong>
                                                                    <?= e(
                                                                        $actionTypeLabel
                                                                    ) ?>
                                                                </strong>

                                                            </div>

                                                            <div
                                                                class="
                                                                    occurrence-action-meta
                                                                "
                                                            >

                                                                <div>

                                                                    <strong>
                                                                        <?= e(
                                                                            $actionAuthor
                                                                        ) ?>
                                                                    </strong>

                                                                    <?php if (
                                                                        $actionRole
                                                                        !== ''
                                                                    ): ?>

                                                                        <small>
                                                                            •
                                                                            <?= e(
                                                                                $actionRole
                                                                            ) ?>
                                                                        </small>

                                                                    <?php endif; ?>

                                                                </div>

                                                                <span>
                                                                    <?= $actionTimestamp !== false
                                                                        ? date(
                                                                            'd/m/Y \à\s H:i',
                                                                            $actionTimestamp
                                                                        )
                                                                        : '-'
                                                                    ?>
                                                                </span>

                                                            </div>

                                                            <p>
                                                                <?= nl2br(
                                                                    e(
                                                                        $action[
                                                                            'description'
                                                                        ] ?? ''
                                                                    )
                                                                ) ?>
                                                            </p>

                                                            <?php $actionAttachments=(array)($action['attachments']??[]); ?>
                                                            <?php if($actionAttachments): ?>
                                                                <?php component('media/attachment-carousel', ['attachments' => $actionAttachments, 'title' => 'Imagens da ação']); ?>
                                                                <div class="occurrence-action-attachments">
                                                                    <strong><i data-lucide="paperclip"></i> Anexos</strong>
                                                                    <?php foreach($actionAttachments as $attachment): ?>
                                                                        <div class="occurrence-action-attachment">
                                                                            <a href="<?= e((string)$attachment['url']) ?>" target="_blank" rel="noopener">
                                                                                <i data-lucide="file-down"></i>
                                                                                <span><?= e((string)$attachment['original_name']) ?></span>
                                                                                <small><?= e((string)$attachment['size_label']) ?></small>
                                                                            </a>
                                                                            <?php if($canManageThisOccurrence): ?>
                                                                                <form method="post" action="<?= base_url('ocorrencias/providencia/anexo/excluir') ?>" onsubmit="return confirm('Remover este anexo da providência?');">
                                                                                    <input type="hidden" name="attachment_id" value="<?= (int)$attachment['id'] ?>">
                                                                                    <input type="hidden" name="student_id" value="<?= (int)($student['id']??0) ?>">
                                                                                    <button type="submit" title="Remover anexo"><i data-lucide="trash-2"></i></button>
                                                                                </form>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>

                                                            <span
                                                                class="badge <?= $actionResolved
                                                                    ? 'badge-success'
                                                                    : 'badge-warning'
                                                                ?>"
                                                            >
                                                                <?= $actionResolved
                                                                    ? 'Ocorrência resolvida'
                                                                    : 'Ocorrência mantida aberta'
                                                                ?>
                                                            </span>

                                                        </div>

                                                    </article>

                                                <?php endforeach; ?>

                                            </div>

                                        <?php endif; ?>

                                    </div>
                                                                        <div
                                        class="student-occurrence-actions"
                                    >

                                        <?php if (
                                            $canManageThisOccurrence
                                        ): ?>

                                            <a
                                                href="<?= base_url(
                                                    'ocorrencias/editar?id='
                                                    . $occurrenceId
                                                ) ?>"
                                                class="btn-secondary"
                                            >
                                                Editar
                                            </a>

                                        <?php endif; ?>

                                        <?php if (
                                            $canViewOccurrences
                                        ): ?>

                                            <a
                                                href="<?= base_url(
                                                    'ocorrencias/providencia?id='
                                                    . $occurrenceId
                                                ) ?>"
                                                class="btn-primary"
                                            >
                                                <i
                                                    data-lucide="clipboard-pen-line"
                                                ></i>

                                                <?= $isResolved
                                                    ? 'Nova providência'
                                                    : 'Adicionar providência'
                                                ?>
                                            </a>

                                        <?php endif; ?>

                                        <?php if (
                                            $canManageThisOccurrence
                                        ): ?>

                                            <form
                                                method="POST"
                                                action="<?= base_url(
                                                    'ocorrencias/excluir'
                                                ) ?>"
                                                onsubmit="
                                                    return confirm(
                                                        'Deseja realmente excluir esta ocorrência?'
                                                    );
                                                "
                                            >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $occurrenceId ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn-danger"
                                                >
                                                    Excluir
                                                </button>

                                            </form>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>
</section>
<style>
.occurrence-action-attachments{margin:.8rem 0;display:grid;gap:.45rem;padding:.7rem;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc}.occurrence-action-attachments>strong{display:flex;align-items:center;gap:.4rem}.occurrence-action-attachments>strong svg{width:16px;height:16px}.occurrence-action-attachment{display:flex;align-items:center;gap:.5rem;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:.45rem .55rem}.occurrence-action-attachment>a{min-width:0;flex:1;display:flex;align-items:center;gap:.45rem;text-decoration:none;color:inherit}.occurrence-action-attachment>a svg{width:17px;height:17px}.occurrence-action-attachment>a span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.occurrence-action-attachment>a small{margin-left:auto;color:#64748b}.occurrence-action-attachment form{margin:0}.occurrence-action-attachment button{border:0;background:transparent;color:#b91c1c;cursor:pointer;padding:.3rem}.occurrence-action-attachment button svg{width:16px;height:16px}
</style>
