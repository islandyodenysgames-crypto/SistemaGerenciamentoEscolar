<div class="card">

    <div class="card-header">
        <h3>Ranking Diário de Frequência</h3>
    </div>

    <?php if (empty($ranking)): ?>

        <div class="activity-empty">
            Nenhuma turma ativa cadastrada.
        </div>

    <?php else: ?>

        <div style="display:grid;gap:16px;margin-top:18px;">

            <?php foreach ($ranking as $index => $item): ?>

                <?php
                    $hasAttendance = (int) ($item['has_attendance'] ?? 0) === 1;
                    $percentage = $hasAttendance ? (float) ($item['attendance_percentage'] ?? 0) : 0;
                ?>

                <div
                    style="
                        display:grid;
                        grid-template-columns:90px 1fr 130px;
                        gap:18px;
                        align-items:center;
                        padding:18px;
                        border-radius:18px;
                        background:#f8fafc;
                    "
                >

                    <div style="font-size:24px;font-weight:900;text-align:center;">
                        <?php if (!$hasAttendance): ?>
                            —
                        <?php else: ?>
                            <?= $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : ($index + 1) . 'º')) ?>
                        <?php endif; ?>
                    </div>

                    <div>

                        <h3 style="margin-bottom:6px;">
                            <?= htmlspecialchars($item['class_name']) ?> — <?= $item['year'] ?>
                        </h3>

                        <p style="margin-bottom:10px;color:#64748b;">
                            Turno: <?= htmlspecialchars($item['shift']) ?>
                        </p>

                        <?php if ($hasAttendance): ?>

                            <div style="display:flex;gap:12px;flex-wrap:wrap;line-height:1.9;">
                                <span>✅ Presentes: <strong><?= (int) $item['presentes'] ?></strong></span>
                                <span>❌ Faltas: <strong><?= (int) $item['ranking_absences'] ?></strong></span>
                                <span>🟡 Justificadas: <strong><?= (int) $item['justificadas'] ?></strong></span>
                                <span>🔵 Atestados: <strong><?= (int) $item['atestados'] ?></strong></span>
                                <span>🟣 Ônibus: <strong><?= (int) $item['onibus'] ?></strong></span>
                                <span>⭐ IFE: <strong><?= number_format((float) $item['ife_score'], 2, ',', '.') ?></strong></span>
                            </div>

                        <?php else: ?>

                            <span class="badge badge-danger">
                                Sem chamada
                            </span>

                        <?php endif; ?>

                    </div>

                    <div>
                        <?php component('attendance-progress-circle', [
                            'percentage' => $percentage,
                            'label' => $hasAttendance ? 'Freq.' : 'Pendente',
                            'size' => 110
                        ]); ?>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>