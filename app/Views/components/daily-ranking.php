<div class="card">

    <div class="card-header">
        <h3>Ranking Diário de Frequência</h3>
    </div>

    <?php if (empty($ranking)): ?>

        <div class="activity-empty">
            Nenhuma turma ativa cadastrada.
        </div>

    <?php else: ?>

        <table class="data-table">

            <thead>
                <tr>
                    <th>Posição</th>
                    <th>Turma</th>
                    <th>Turno</th>
                    <th>Frequência</th>
                    <th>IFE</th>
                    <th>Faltas penalizadas</th>
                    <th>Atenuadas</th>
                    <th>Total</th>
                    <th>Situação</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($ranking as $index => $item): ?>

                    <?php
                        $hasAttendance = (int) ($item['has_attendance'] ?? 0) === 1;
                    ?>

                    <tr>
                        <td>
                            <?php if (!$hasAttendance): ?>
                                —
                            <?php else: ?>
                                <?= $index === 0 ? '🥇 1º' : ($index === 1 ? '🥈 2º' : ($index === 2 ? '🥉 3º' : ($index + 1) . 'º')) ?>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['class_name']) ?> — <?= $item['year'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['shift']) ?>
                        </td>

                        <td>
                            <?php if ($hasAttendance): ?>
                                <strong>
                                    <?= number_format((float) $item['attendance_percentage'], 1, ',', '.') ?>%
                                </strong>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($hasAttendance): ?>
                                <strong>
                                    <?= number_format((float) $item['ife_score'], 2, ',', '.') ?>
                                </strong>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                        <td>
                            <?= $hasAttendance ? (int) $item['ranking_absences'] : '-' ?>
                        </td>

                        <td>
                            <?= $hasAttendance ? (int) $item['attenuated_absences'] : '-' ?>
                        </td>

                        <td>
                            <?= $hasAttendance ? (int) $item['total_students'] : '-' ?>
                        </td>

                        <td>
                            <?php if ($hasAttendance): ?>
                                <span class="badge badge-success">
                                    Com chamada
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    Sem chamada
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>