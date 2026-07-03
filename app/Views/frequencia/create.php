<?php

component('page-header', [
    'title' => 'Nova chamada',
    'subtitle' => 'Registre a frequência diária da turma'
]);

?>

<div class="card">

    <form method="GET" action="<?= base_url('frequencia/novo') ?>" class="user-form">

        <div class="form-group">
            <label>Selecione a turma</label>

            <select class="form-control" name="turma" required onchange="this.form.submit()">
                <option value="">Selecione uma turma</option>

                <?php foreach ($classes as $class): ?>
                    <option value="<?= $class['id'] ?>" <?= (int) $classId === (int) $class['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($class['name']) ?> — <?= $class['year'] ?> — <?= htmlspecialchars($class['shift']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

    </form>

</div>

<?php if ($classId > 0): ?>

    <div class="card" style="margin-top:24px;">

        <form method="POST" action="<?= base_url('frequencia') ?>">

            <input type="hidden" name="school_class_id" value="<?= $classId ?>">

            <div class="user-form">

                <div class="form-group">
                    <label>Data da chamada</label>
                    <input class="form-control" type="date" name="attendance_date" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label>Observações</label>
                    <input class="form-control" type="text" name="notes" placeholder="Opcional">
                </div>

            </div>

            <table class="data-table" style="margin-top:24px;">

                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th width="160">Matrícula</th>

                        <?php foreach ($statusOptions as $label): ?>
                            <th width="130"><?= htmlspecialchars($label) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>

                <?php if (empty($students)): ?>

                    <tr>
                        <td colspan="<?= 2 + count($statusOptions) ?>" style="text-align:center;padding:40px;">
                            Nenhum aluno matriculado nesta turma.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($students as $student): ?>

                        <tr>
                            <td><?= htmlspecialchars($student['name']) ?></td>

                            <td><?= htmlspecialchars($student['registration']) ?></td>

                            <?php foreach ($statusOptions as $code => $label): ?>

                                <td style="text-align:center;">

                                    <input
                                        type="radio"
                                        name="status[<?= $student['id'] ?>]"
                                        value="<?= $code ?>"
                                        <?= $code === 'P' ? 'checked' : '' ?>
                                    >

                                </td>

                            <?php endforeach; ?>
                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

                </tbody>

            </table>

            <?php if (!empty($students)): ?>

                <div class="form-actions" style="margin-top:24px;">
                    <a href="<?= base_url('frequencia') ?>" class="btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit" class="btn-primary">
                        Salvar chamada
                    </button>
                </div>

            <?php endif; ?>

        </form>

    </div>

<?php endif; ?>