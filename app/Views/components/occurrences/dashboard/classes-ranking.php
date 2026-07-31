<?php

$classes = $classes ?? [];

$maximum = 0;

foreach ($classes as $class) {
    $maximum = max(
        $maximum,
        (int) ($class['total_occurrences'] ?? 0)
    );
}

?>

<section class="card occurrences-dashboard-widget occurrences-classes-widget">

    <div class="occurrences-dashboard-widget-header">

        <div>
            <h3>Turmas com mais ocorrências</h3>
            <p>Comparativo das turmas com maior número de registros.</p>
        </div>

        <i data-lucide="school"></i>

    </div>

    <?php if (empty($classes)): ?>

        <div class="activity-empty">
            Nenhuma turma encontrada no ranking.
        </div>

    <?php else: ?>

        <div class="occurrences-classes-ranking">

            <?php foreach ($classes as $index => $class): ?>

                <?php

                $total = (int) (
                    $class['total_occurrences'] ?? 0
                );

                $percentage = $maximum > 0
                    ? ($total / $maximum) * 100
                    : 0;

                $classLabel = trim(
                    ($class['name'] ?? 'Turma')
                    . ' • '
                    . (
                        !empty($class['year'])
                            ? $class['year'] . 'º Ano'
                            : ''
                    )
                    . ' • '
                    . ($class['shift'] ?? '')
                );

                ?>

                <a
                    href="<?= base_url(
                        'alunos/turma?id='
                        . (int) ($class['id'] ?? 0)
                    ) ?>"
                    class="occurrences-class-ranking-item"
                >

                    <span class="occurrences-class-position">
                        <?= $index + 1 ?>
                    </span>

                    <div class="occurrences-class-content">

                        <div class="occurrences-class-label">

                            <strong>
                                <?= e($classLabel) ?>
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

                </a>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>