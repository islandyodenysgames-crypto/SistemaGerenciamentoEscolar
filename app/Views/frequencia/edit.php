<?php

component('page-header', [
    'title' => 'Editar chamada',
    'subtitle' => 'Corrija as situações registradas na frequência'
]);

$statusLabels = $statusOptions ?? [];

?>

<div class="card">

    <h3 style="margin-bottom:16px;">
        <?= htmlspecialchars($attendance['class_name']) ?>
        — <?= $attendance['year'] ?>
        — <?= htmlspecialchars($attendance['shift']) ?>
    </h3>

    <form method="POST" action="<?= base_url('frequencia/editar') ?>">

        <input type="hidden" name="id" value="<?= $attendance['id'] ?>">

        <div class="user-form">

            <div class="form-group">
                <label>Data da chamada</label>

                <input
                    class="form-control"
                    type="date"
                    name="attendance_date"
                    value="<?= htmlspecialchars($attendance['attendance_date']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Observações</label>

                <input
                    class="form-control"
                    type="text"
                    name="notes"
                    value="<?= htmlspecialchars($attendance['notes'] ?? '') ?>"
                >
            </div>

        </div>

        <table class="data-table" style="margin-top:24px;">

            <thead>
                <tr>
                    <th>Aluno</th>
                    <th width="150">Matrícula</th>
                    <th width="520">Situação</th>
                </tr>
            </thead>

            <tbody>

            <?php foreach ($items as $item): ?>

                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['student_name']) ?></strong>
                    </td>

                    <td>
                        <?= htmlspecialchars($item['registration']) ?>
                    </td>

                    <td>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;">

                            <?php foreach ($statusLabels as $code => $label): ?>

                                <label style="font-weight:700;">
                                    <input
                                        type="radio"
                                        name="status[<?= $item['id'] ?>]"
                                        value="<?= $code ?>"
                                        <?= $item['status'] === $code ? 'checked' : '' ?>
                                    >

                                    <?= htmlspecialchars($label) ?>
                                </label>

                            <?php endforeach; ?>

                        </div>
                    </td>
                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <div class="form-actions" style="margin-top:24px;">

            <a href="<?= base_url('frequencia') ?>" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Salvar alterações
            </button>

        </div>

    </form>

</div>