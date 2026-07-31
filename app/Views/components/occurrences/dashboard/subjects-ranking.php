<?php

$subjects = $subjects ?? [];

$maximum = 0;

foreach ($subjects as $subject) {
    $maximum = max(
        $maximum,
        (int) ($subject['total_occurrences'] ?? 0)
    );
}

?>

<section class="card occurrences-dashboard-widget">

    <div class="occurrences-dashboard-widget-header">

        <div>
            <h3>Disciplinas com mais ocorrências</h3>
            <p>Comparativo das disciplinas com maior número de registros.</p>
        </div>

        <i data-lucide="book-open"></i>

    </div>

    <?php if (empty($subjects)): ?>

        <div class="activity-empty">
            Nenhuma disciplina encontrada no ranking.
        </div>

    <?php else: ?>

        <div class="occurrences-classes-ranking">

            <?php foreach ($subjects as $index => $subject): ?>

                <?php

                $total = (int) (
                    $subject['total_occurrences'] ?? 0
                );

                $percentage = $maximum > 0
                    ? ($total / $maximum) * 100
                    : 0;

                ?>

                <div
                    class="occurrences-class-ranking-item"
                >

                    <span class="occurrences-class-position">
                        <?= $index + 1 ?>
                    </span>

                    <div class="occurrences-class-content">

                        <div class="occurrences-class-label">

                            <strong>
                                <?= e(
                                    $subject['subject_name']
                                    ?? 'Sem disciplina'
                                ) ?>
                            </strong>

                            <span>
                                <?= $total ?> ocorrência(s)
                            </span>

                        </div>

                        <div class="occurrences-class-progress">

                            <span
                                style="width: <?= number_format(
                                    $percentage,
                                    2,
                                    '.',
                                    ''
                                ) ?>%;"
                            ></span>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>