<?php

component('base/page-header', [
    'title' => 'Visualizar chamada',
    'subtitle' => 'Confira os registros de frequência da turma'
]);

?>

<div class="card">

    <h3 class="mb-16">
        <?= e($attendance['class_name']) ?>
        — <?= $attendance['year'] ?>
        — <?= e($attendance['shift']) ?>
    </h3>

    <p>
        <strong>Data:</strong>
        <?= date('d/m/Y', strtotime($attendance['attendance_date'])) ?>
    </p>

    <p>
        <strong>Observações:</strong>
        <?= e($attendance['notes'] ?: '-') ?>
    </p>

</div>

<div class="card mt-24">

    <div class="table-header">
        <h3>Alunos da chamada</h3>

        <a href="<?= base_url('frequencia') ?>" class="btn-secondary">
            Voltar
        </a>
    </div>

    <div class="table-responsive">
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
                <td><?= e($item['student_name']) ?></td>

                <td><?= e($item['registration']) ?></td>

                <td>
                    <?= e($statusOptions[$item['status']] ?? $item['status']) ?>
                </td>

                <td>
                    <?= e($item['justification'] ?: '-') ?>
                </td>
            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>
    </div>

</div>