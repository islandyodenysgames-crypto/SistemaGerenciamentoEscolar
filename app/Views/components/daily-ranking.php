<div class="card daily-ranking-card">

    <div class="card-header">
        <h3>Ranking Diário de Frequência</h3>
    </div>

    <?php if (empty($ranking)): ?>

        <div class="activity-empty">
            Nenhuma turma ativa cadastrada.
        </div>

    <?php else: ?>

        <div class="ranking-list">

            <?php foreach ($ranking as $index => $item): ?>

                <?php
                    $hasAttendance = (int) ($item['has_attendance'] ?? 0) === 1;
                    $percentage = $hasAttendance ? (float) ($item['attendance_percentage'] ?? 0) : 0;

                    $position = $index + 1;
                    $medal = $position === 1
                        ? '🥇'
                        : ($position === 2
                            ? '🥈'
                            : ($position === 3 ? '🥉' : $position . 'º'));
                ?>

                <div class="ranking-item">

                    <div class="ranking-position">
                        <?= $hasAttendance ? $medal : '—' ?>
                    </div>

                    <div class="ranking-info">

                        <div class="ranking-title">
                            <?= htmlspecialchars($item['class_name']) ?>
                            — <?= $item['year'] ?>
                        </div>

                        <div class="ranking-subtitle">
                            Turno: <?= htmlspecialchars($item['shift']) ?>
                        </div>

                        <?php if ($hasAttendance): ?>

                            <div class="ranking-metrics">
                                <span>✅ <?= (int) $item['presentes'] ?> presentes</span>
                                <span>❌ <?= (int) $item['ranking_absences'] ?> faltas</span>
                                <span>🟡 <?= (int) $item['justificadas'] ?> justificadas</span>
                                <span>🔵 <?= (int) $item['atestados'] ?> atestados</span>
                                <span>🟣 <?= (int) $item['onibus'] ?> ônibus</span>
                                <span>⭐ IFE <?= number_format((float) $item['ife_score'], 2, ',', '.') ?></span>
                            </div>

                        <?php else: ?>

                            <span class="badge badge-danger">
                                Sem chamada
                            </span>

                        <?php endif; ?>

                    </div>

                    <div class="ranking-progress">

                        <?php component('attendance-progress-circle', [
                            'percentage' => $percentage,
                            'label' => $hasAttendance ? 'Freq.' : 'Pendente',
                            'size' => 105
                        ]); ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>