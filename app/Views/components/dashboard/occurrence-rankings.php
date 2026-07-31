<?php

$students = $students ?? [];
$classes = $classes ?? [];

?>

<div class="dashboard-occurrence-rankings">

    <section class="card dashboard-occurrence-ranking-card">

        <div class="dashboard-occurrence-widget-header">

            <div>
                <h3>Alunos com mais ocorrências</h3>
                <p>Maiores quantidades de registros.</p>
            </div>

            <i data-lucide="users-round"></i>

        </div>

        <?php if (empty($students)): ?>

            <div class="activity-empty">
                Nenhum aluno no ranking.
            </div>

        <?php else: ?>

            <div class="dashboard-occurrence-ranking-list">

                <?php foreach ($students as $index => $student): ?>

                    <a
                        href="<?= base_url(
                            'alunos/perfil?id='
                            . (int) ($student['id'] ?? 0)
                            . '#studentOccurrences'
                        ) ?>"
                        class="dashboard-occurrence-ranking-item"
                    >

                        <span class="dashboard-occurrence-position">
                            <?= $index + 1 ?>
                        </span>

                        <div>

                            <strong>
                                <?= e(
                                    $student['name'] ?? 'Aluno'
                                ) ?>
                            </strong>

                            <small>
                                <?= (int) (
                                    $student['open_occurrences']
                                    ?? 0
                                ) ?>
                                aberta(s)
                            </small>

                        </div>

                        <strong class="dashboard-occurrence-total">
                            <?= (int) (
                                $student['total_occurrences']
                                ?? 0
                            ) ?>
                        </strong>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

    <section class="card dashboard-occurrence-ranking-card">

        <div class="dashboard-occurrence-widget-header">

            <div>
                <h3>Turmas com mais ocorrências</h3>
                <p>Comparativo geral entre as turmas.</p>
            </div>

            <i data-lucide="school"></i>

        </div>

        <?php if (empty($classes)): ?>

            <div class="activity-empty">
                Nenhuma turma no ranking.
            </div>

        <?php else: ?>

            <div class="dashboard-occurrence-ranking-list">

                <?php foreach ($classes as $index => $class): ?>

                    <?php

                    $classLabel = trim(
                        ($class['name'] ?? 'Turma')
                        . (
                            !empty($class['year'])
                                ? ' • '
                                    . $class['year']
                                    . 'º Ano'
                                : ''
                        )
                        . (
                            !empty($class['shift'])
                                ? ' • '
                                    . $class['shift']
                                : ''
                        )
                    );

                    ?>

                    <a
                        href="<?= base_url(
                            'alunos/turma?id='
                            . (int) ($class['id'] ?? 0)
                        ) ?>"
                        class="dashboard-occurrence-ranking-item"
                    >

                        <span class="dashboard-occurrence-position">
                            <?= $index + 1 ?>
                        </span>

                        <div>

                            <strong>
                                <?= e($classLabel) ?>
                            </strong>

                            <small>Turma</small>

                        </div>

                        <strong class="dashboard-occurrence-total">
                            <?= (int) (
                                $class['total_occurrences']
                                ?? 0
                            ) ?>
                        </strong>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</div>