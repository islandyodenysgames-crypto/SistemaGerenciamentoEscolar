<div id="rankingShareCard" class="ranking-share-card">

    <div class="ranking-share-brand">

        <?php component('school/branding', [
            'variant' => 'ranking',
        ]); ?>

        <span>
            <?= date('d/m/Y') ?><br>
            <?= date('H:i') ?>
        </span>

    </div>

    <h2>Ranking Diário</h2>

    <p>Resumo institucional da frequência escolar</p>

    <div class="ranking-share-summary">

        <div>
            <span>📊</span>
            <strong><?= number_format((float) ($generalPercentage ?? 0), 1, ',', '.') ?>%</strong>
            <small>Freq. Geral</small>
        </div>

        <div>
            <span>👥</span>
            <strong><?= (int) ($presentes ?? 0) ?></strong>
            <small>Presentes</small>
        </div>

        <div>
            <span>❌</span>
            <strong><?= (int) ($faltas ?? 0) ?></strong>
            <small>Faltas</small>
        </div>

        <div>
            <span>🏫</span>
            <strong><?= (int) ($totalClasses ?? 0) ?></strong>
            <small>Turmas</small>
        </div>

    </div>

    <div class="ranking-share-list">

        <?php foreach (($ranking ?? []) as $index => $item): ?>

            <?php

            $hasAttendance = (int) ($item['has_attendance'] ?? 0) === 1;

            $percentage = $hasAttendance
                ? (float) ($item['attendance_percentage'] ?? 0)
                : 0;

            $rankIcon = match ($index) {
                0 => '🥇',
                1 => '🥈',
                2 => '🥉',
                default => ($index + 1) . 'º',
            };

            ?>

            <div class="ranking-share-row">

                <span><?= e((string) $rankIcon) ?></span>

                <div>

                    <strong><?= e($item['class_name']) ?></strong>

                    <small>
                        <?= (int) $item['year'] ?> • <?= e($item['shift']) ?>
                    </small>

                </div>

                <b><?= number_format($percentage, 0, ',', '.') ?>%</b>

            </div>

        <?php endforeach; ?>

    </div>

    <div class="ranking-share-footer">

        <strong><?= e(school('name', app_name())) ?></strong>

        <small>
            Gerado automaticamente pelo Sistema de Frequência Escolar
        </small>

    </div>

</div>