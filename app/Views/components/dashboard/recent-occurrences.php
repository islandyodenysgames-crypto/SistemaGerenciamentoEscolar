<?php

$occurrences = $occurrences ?? [];

$typeClasses = [
    'OBSERVATION' => 'observation',
    'WARNING' => 'warning',
    'SUSPENSION' => 'suspension',
    'REFERRAL' => 'referral',
    'PRAISE' => 'praise',
    'OTHER' => 'other',
];

?>

<section class="card dashboard-recent-occurrences">

    <div class="dashboard-occurrence-widget-header">

        <div>
            <h3>Últimas ocorrências</h3>

            <p>
                Registros mais recentes realizados na escola.
            </p>
        </div>

        <a
            href="<?= base_url('ocorrencias') ?>"
            class="btn-secondary"
        >
            Ver todas
        </a>

    </div>

    <?php if (empty($occurrences)): ?>

        <div class="activity-empty">
            Nenhuma ocorrência recente.
        </div>

    <?php else: ?>

        <div class="dashboard-recent-occurrences-list">

            <?php foreach ($occurrences as $occurrence): ?>

                <?php

                $type = (string) (
                    $occurrence['type'] ?? 'OTHER'
                );

                $typeClass = $typeClasses[$type]
                    ?? 'other';

                $isResolved = (
                    $occurrence['status'] ?? 'OPEN'
                ) === 'RESOLVED';

                ?>

                <article
                    class="
                        dashboard-recent-occurrence
                        <?= e($typeClass) ?>
                    "
                >

                    <div class="dashboard-recent-occurrence-content">

                        <span>
                            <?= e(
                                $occurrence['type_label']
                                ?? 'Outro'
                            ) ?>
                        </span>

                        <h4>
                            <?= e(
                                $occurrence['title']
                                ?? 'Ocorrência'
                            ) ?>
                        </h4>

                        <p>
                            <strong>
                                <?= e(
                                    $occurrence['student_name']
                                    ?? 'Aluno'
                                ) ?>
                            </strong>

                            <?php if (!empty(
                                $occurrence['class_name']
                            )): ?>

                                <span>•</span>

                                <?= e(
                                    $occurrence['class_name']
                                ) ?>

                            <?php endif; ?>
                        </p>

                    </div>

                    <div class="dashboard-recent-occurrence-meta">

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
                                $occurrence['student_id'] ?? 0
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