<table class="data-table">

    <thead>
        <tr>
            <th width="60">ID</th>
            <th>Nome</th>
            <th width="130">Matrícula</th>
            <th width="150">Turma Atual</th>
            <th width="80">Ano</th>
            <th width="100">Turno</th>
            <th width="130">Frequência</th>
            <th width="120">Situação</th>
            <th width="100">Status</th>
            <th width="170">Ações</th>
        </tr>
    </thead>

    <tbody>

    <?php if (empty($students)): ?>

        <tr>
            <td colspan="10" class="table-empty">
                Nenhum aluno cadastrado.
            </td>
        </tr>

    <?php else: ?>

        <?php foreach ($students as $student): ?>

            <?php
                $totalRecords = (int) ($student['total_records'] ?? 0);

                $percentage = $totalRecords > 0
                    ? (float) $student['attendance_percentage']
                    : null;
            ?>

            <tr>
                <td><?= (int) $student['id'] ?></td>

                <td><?= e($student['name']) ?></td>

                <td><?= e($student['registration']) ?></td>

                <td>
                    <?php if (!empty($student['class_name'])): ?>

                        <?= e($student['class_name']) ?>

                    <?php else: ?>

                        <span class="text-warning text-strong">
                            Sem matrícula
                        </span>

                    <?php endif; ?>
                </td>

                <td><?= $student['class_year'] ?? '-' ?></td>

                <td>
                    <?= !empty($student['class_shift'])
                        ? e($student['class_shift'])
                        : '-' ?>
                </td>

                <td>
                    <?php if ($percentage === null): ?>

                        -

                    <?php else: ?>

                        <strong>
                            <?= number_format($percentage, 1, ',', '.') ?>%
                        </strong>

                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($percentage === null): ?>

                        <span class="badge badge-danger">
                            Sem dados
                        </span>

                    <?php elseif ($percentage >= 95): ?>

                        <span class="badge badge-success">
                            Excelente
                        </span>

                    <?php elseif ($percentage >= 85): ?>

                        <span class="badge badge-success">
                            Regular
                        </span>

                    <?php elseif ($percentage >= 75): ?>

                        <span class="badge badge-danger">
                            Atenção
                        </span>

                    <?php else: ?>

                        <span class="badge badge-danger">
                            Crítico
                        </span>

                    <?php endif; ?>
                </td>

                <td>
                    <?php if ((int) $student['active'] === 1): ?>

                        <span class="badge badge-success">
                            Ativo
                        </span>

                    <?php else: ?>

                        <span class="badge badge-danger">
                            Inativo
                        </span>

                    <?php endif; ?>
                </td>

                <td>
                    <div class="table-actions">

                        <a
                            href="<?= base_url('alunos/editar?id=' . $student['id']) ?>"
                            class="table-link"
                        >
                            Editar
                        </a>

                        <form
                            action="<?= base_url('alunos/excluir') ?>"
                            method="POST"
                            onsubmit="return confirm('Deseja realmente excluir este aluno?');"
                        >
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $student['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="table-delete"
                            >
                                Excluir
                            </button>
                        </form>

                    </div>
                </td>
            </tr>

        <?php endforeach; ?>

    <?php endif; ?>

    </tbody>

</table>