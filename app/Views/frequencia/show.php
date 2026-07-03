<?php

component('page-header', [
    'title' => 'Visualizar chamada',
    'subtitle' => 'Confira os registros de frequência da turma'
]);

?>

<div class="card">

    <h3 style="margin-bottom:16px;">
        <?= htmlspecialchars($attendance['class_name']) ?>
        — <?= $attendance['year'] ?>
        — <?= htmlspecialchars($attendance['shift']) ?>
    </h3>

    <p>
        <strong>Data:</strong>
        <?= date('d/m/Y', strtotime($attendance['attendance_date'])) ?>
    </p>

    <p>
        <strong>Observações:</strong>
        <?= htmlspecialchars($attendance['notes'] ?: '-') ?>
    </p>

</div>

<div class="card" style="margin-top:24px;">

    <div class="table-header">
        <h3>Alunos da chamada</h3>

        <a href="<?= base_url('frequencia') ?>" class="btn-secondary">
            Voltar
        </a>
    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th>Aluno</th>
                <th width="160">Matrícula</th>
                <th width="220">Situação</th>
                <th>Justificativa</th>
            </tr>
        </thead>

        <tbody>

        <?php foreach ($items as $item): ?>

            <tr>
                <td><?= htmlspecialchars($item['student_name']) ?></td>

                <td><?= htmlspecialchars($item['registration']) ?></td>

                <td>
                    <?= htmlspecialchars($statusOptions[$item['status']] ?? $item['status']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($item['justification'] ?: '-') ?>
                </td>
            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>