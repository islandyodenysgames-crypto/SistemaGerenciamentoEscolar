<?php

$students = $students ?? [];

?>

<section class="card occurrences-dashboard-widget">

    <div class="occurrences-dashboard-widget-header">

        <div>
            <h3>Alunos com mais ocorrências</h3>
            <p>Ranking geral por quantidade de registros.</p>
        </div>

        <i data-lucide="users-round"></i>

    </div>

    <?php if (empty($students)): ?>

        <div class="activity-empty">
            Nenhum aluno encontrado no ranking.
        </div>

    <?php else: ?>

        <div class="occurrences-students-ranking">

            <?php foreach ($students as $index => $student): ?>

                <?php

                $position = $index + 1;

                $total = (int) (
                    $student['total_occurrences'] ?? 0
                );

                $open = (int) (
                    $student['open_occurrences'] ?? 0
                );

                ?>

                <a
                    href="<?= base_url(
                        'alunos/perfil?id='
                        . (int) ($student['id'] ?? 0)
                        . '#studentOccurrences'
                    ) ?>"
                    class="occurrences-student-ranking-item"
                >

                    <span class="occurrences-ranking-position">
                        <?= $position ?>
                    </span>

                    <div class="occurrences-ranking-avatar">
                        <i data-lucide="user"></i>
                    </div>

                    <div class="occurrences-ranking-content">

                        <strong>
                            <?= e($student['name'] ?? 'Aluno') ?>
                        </strong>

                        <small>
                            Matrícula:
                            <?= e(
                                $student['registration'] ?? '-'
                            ) ?>
                        </small>

                    </div>

                    <div class="occurrences-ranking-numbers">

                        <strong><?= $total ?></strong>

                        <small>
                            <?= $open ?> aberta(s)
                        </small>

                    </div>

                </a>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>