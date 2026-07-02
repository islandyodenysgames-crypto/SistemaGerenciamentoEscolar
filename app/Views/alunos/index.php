<?php

component('page-header', [
    'title' => 'Alunos',
    'subtitle' => 'Gerencie os alunos cadastrados'
]);

?>

<div class="card">

    <div class="table-header">

        <h3>Alunos cadastrados</h3>

        <a href="#" class="btn-primary">
            + Novo aluno
        </a>

    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th width="70">ID</th>
                <th>Nome</th>
                <th width="150">Matrícula</th>
                <th width="140">Nascimento</th>
                <th>Responsável</th>
                <th width="150">Telefone</th>
                <th width="110">Status</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($students)): ?>

            <tr>
                <td colspan="7" style="text-align:center;padding:40px;">
                    Nenhum aluno cadastrado.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($students as $student): ?>

                <tr>
                    <td><?= $student['id'] ?></td>

                    <td><?= htmlspecialchars($student['name']) ?></td>

                    <td><?= htmlspecialchars($student['registration']) ?></td>

                    <td>
                        <?= !empty($student['birth_date'])
                            ? date('d/m/Y', strtotime($student['birth_date']))
                            : '-' ?>
                    </td>

                    <td><?= htmlspecialchars($student['guardian_name'] ?? '-') ?></td>

                    <td><?= htmlspecialchars($student['guardian_phone'] ?? '-') ?></td>

                    <td>
                        <?php if ((int) $student['active'] === 1): ?>
                            <span class="badge badge-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Inativo</span>
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>