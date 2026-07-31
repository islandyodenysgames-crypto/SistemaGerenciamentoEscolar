<div class="card daily-ranking-card" id="dailyRankingCard">

    <div class="ranking-share-header">

        <?php component('dashboard/panel-header', [
            'icon' => 'trophy',
            'title' => 'Ranking diário',
            'subtitle' => 'Melhores desempenhos da escola hoje',
            'badge' => count($ranking ?? []) . ' turma(s)',
            'badgeClass' => 'badge-success',
        ]); ?>

        <button
            type="button"
            class="ranking-action"
            id="shareRankingBtn"
            title="Exportar Ranking em PNG"
            aria-label="Exportar Ranking em PNG"
            data-export-hide
        >
            <i data-lucide="image-down"></i>
        </button>

    </div>

    <?php if (empty($ranking)): ?>

        <div class="attendance-success">

            <div class="attendance-success-icon">
                🏫
            </div>

            <h3>Nenhuma turma cadastrada</h3>

            <p>
                Ainda não existem turmas para exibir no ranking.
            </p>

        </div>

    <?php else: ?>

        <div class="ranking-list">

            <?php foreach ($ranking as $index => $item): ?>

                <?php
$goalPercentage = (float)($goalPercentage ?? 95);
$attentionThreshold = max(0, $goalPercentage - 5);

                $hasAttendance = (int) ($item['has_attendance'] ?? 0) === 1;

                $percentage = $hasAttendance
                    ? (float) ($item['attendance_percentage'] ?? 0)
                    : 0;

                $rankLabel = $index + 1 . 'º';

                $rankIcon = match ($index) {
                    0 => '🥇',
                    1 => '🥈',
                    2 => '🥉',
                    default => $rankLabel,
                };

                $performanceClass = match (true) {
                    !$hasAttendance => 'pending',
                    $percentage >= $goalPercentage => 'success',
                    $percentage >= $attentionThreshold => 'warning',
                    default => 'danger',
                };

                $performanceLabel = match ($performanceClass) {
                    'success' => 'Excelente',
                    'warning' => 'Atenção',
                    'danger' => 'Crítico',
                    default => 'Pendente',
                };

                ?>

                <div class="ranking-item ranking-item-<?= e($performanceClass) ?>">

                    <div class="ranking-position">

                        <strong><?= e((string) $rankIcon) ?></strong>

                        <span>
                            <?= $hasAttendance ? e($rankLabel) : 'Pendente' ?>
                        </span>

                    </div>

                    <div class="ranking-main">

                        <div class="ranking-title-row">

                            <div>

                                <div class="ranking-title">
                                    <?= e($item['class_name']) ?>
                                </div>

                                <div class="ranking-subtitle">
                                    <?= (int) $item['year'] ?>
                                    •
                                    <?= e($item['shift']) ?>
                                </div>

                            </div>

                            <?php if ($hasAttendance): ?>

                                <div class="ranking-status">

                                    <span class="ranking-performance-badge ranking-performance-<?= e($performanceClass) ?>">
                                        <?= e($performanceLabel) ?>
                                    </span>

                                </div>

                            <?php endif; ?>

                        </div>

                        <?php if ($hasAttendance): ?>

                            <div class="ranking-metrics">

                                <div class="ranking-metric">

                                    <span>👥</span>

                                    <strong>
                                        <?= (int) $item['presentes'] ?>
                                    </strong>

                                    <small>Presentes</small>

                                </div>

                                <div class="ranking-metric">

                                    <span>❌</span>

                                    <strong>
                                        <?= (int) $item['ranking_absences'] ?>
                                    </strong>

                                    <small>Faltas</small>

                                </div>

                                <div class="ranking-metric">

                                    <span>📈</span>

                                    <strong>
                                        <?= number_format($percentage, 0, ',', '.') ?>%
                                    </strong>

                                    <small>Frequência</small>

                                </div>

                                <div class="ranking-metric ranking-metric-occurrences">

                                    <span>📋</span>

                                    <strong>
                                        <?= (int) ($item['justificadas'] ?? 0) ?>
                                        /
                                        <?= (int) ($item['atestados'] ?? 0) ?>
                                        /
                                        <?= (int) ($item['onibus'] ?? 0) ?>
                                    </strong>

                                    <small>Jus. / Ates. / Ônibus</small>

                                </div>

                            </div>

                        <?php else: ?>

                            <span class="badge badge-warning">
                                Chamada pendente
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<?php component('export/ranking-share', [
    'ranking' => $ranking ?? [],
    'generalPercentage' => $generalPercentage ?? 0,
    'presentes' => $presentes ?? 0,
    'faltas' => $faltas ?? 0,
    'totalClasses' => $totalClasses ?? 0,
]); ?>