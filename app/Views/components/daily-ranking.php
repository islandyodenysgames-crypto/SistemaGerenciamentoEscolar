<div class="card">

    <div class="card-header">
        <h3>Ranking Diário de Frequência</h3>
    </div>

    <?php if (empty($ranking)): ?>

        <div class="activity-empty">
            Nenhuma chamada registrada hoje.
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
                </tr>
            </thead>

            <tbody>

                <?php foreach ($ranking as $index => $item): ?>

                    <tr>
                        <td>
                            <?= $index === 0 ? '🥇 1º' : ($index === 1 ? '🥈 2º' : ($index === 2 ? '🥉 3º' : ($index + 1) . 'º')) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['class_name']) ?> — <?= $item['year'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['shift']) ?>
                        </td>

                        <td>
                            <strong>
                                <?= number_format((float) $item['attendance_percentage'], 1, ',', '.') ?>%
                            </strong>
                        </td>

                        <td>
                            <strong>
                                <?= number_format((float) $item['ife_score'], 2, ',', '.') ?>
                            </strong>
                        </td>

                        <td><?= (int) $item['ranking_absences'] ?></td>

                        <td><?= (int) $item['attenuated_absences'] ?></td>

                        <td><?= (int) $item['total_students'] ?></td>
                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>