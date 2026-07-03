<div class="card">

    <div class="card-header">

        <h3>Turmas sem chamada hoje</h3>

    </div>

    <?php if (empty($classesWithoutAttendance)): ?>

        <div class="activity-empty">

            Todas as turmas já registraram frequência hoje.

        </div>

    <?php else: ?>

        <table class="data-table">

            <thead>

                <tr>

                    <th>Turma</th>

                    <th>Ano</th>

                    <th>Turno</th>

                </tr>

            </thead>

            <tbody>

            <?php foreach ($classesWithoutAttendance as $class): ?>

                <tr>

                    <td>

                        <?= htmlspecialchars($class['name']) ?>

                    </td>

                    <td>

                        <?= $class['year'] ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($class['shift']) ?>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    <?php endif; ?>

</div>