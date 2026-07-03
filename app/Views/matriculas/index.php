<?php

component('page-header', [
    'title' => 'Matrículas',
    'subtitle' => 'Vincule alunos às turmas'
]);

?>

<div class="card">

    <div class="table-header">

        <h3>Matrículas cadastradas</h3>

        <a href="<?= base_url('matriculas/novo') ?>" class="btn-primary">
            + Nova matrícula
        </a>

    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th width="70">ID</th>
                <th>Aluno</th>
                <th width="150">Matrícula</th>
                <th>Turma</th>
                <th width="120">Ano</th>
                <th width="120">Turno</th>
                <th width="140">Data</th>
                <th width="110">Status</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($enrollments)): ?>

            <tr>
                <td colspan="8" style="text-align:center;padding:40px;">
                    Nenhuma matrícula cadastrada.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($enrollments as $enrollment): ?>

                <tr>
                    <td><?= $enrollment['id'] ?></td>

                    <td><?= htmlspecialchars($enrollment['student_name']) ?></td>

                    <td><?= htmlspecialchars($enrollment['registration']) ?></td>

                    <td><?= htmlspecialchars($enrollment['class_name']) ?></td>

                    <td><?= $enrollment['year'] ?></td>

                    <td><?= htmlspecialchars($enrollment['shift']) ?></td>

                    <td>
                        <?= date('d/m/Y', strtotime($enrollment['enrollment_date'])) ?>
                    </td>

                    <td>
                        <?php if ((int) $enrollment['active'] === 1): ?>
                            <span class="badge badge-success">Ativa</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inativa</span>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>