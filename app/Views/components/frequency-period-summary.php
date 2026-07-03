<div class="card">

    <div class="card-header">
        <h3>Indicadores da Escola</h3>
    </div>

    <table class="data-table">

        <thead>

            <tr>

                <th>Período</th>

                <th>Frequência</th>

                <th>Presentes</th>

                <th>Faltas</th>

            </tr>

        </thead>

        <tbody>

            <tr>

                <td>Hoje</td>

                <td>
                    <strong>
                        <?= $today['percentage'] ?>%
                    </strong>
                </td>

                <td><?= $today['presentes'] ?></td>

                <td><?= $today['faltas'] ?></td>

            </tr>

            <tr>

                <td>Semana</td>

                <td>
                    <strong>
                        <?= $week['percentage'] ?>%
                    </strong>
                </td>

                <td><?= $week['presentes'] ?></td>

                <td><?= $week['faltas'] ?></td>

            </tr>

            <tr>

                <td>Mês</td>

                <td>
                    <strong>
                        <?= $month['percentage'] ?>%
                    </strong>
                </td>

                <td><?= $month['presentes'] ?></td>

                <td><?= $month['faltas'] ?></td>

            </tr>

            <tr>

                <td>Ano</td>

                <td>
                    <strong>
                        <?= $year['percentage'] ?>%
                    </strong>
                </td>

                <td><?= $year['presentes'] ?></td>

                <td><?= $year['faltas'] ?></td>

            </tr>

        </tbody>

    </table>

</div>