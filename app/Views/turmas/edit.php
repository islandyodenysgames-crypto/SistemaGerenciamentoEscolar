<?php

component('page-header', [
    'title' => 'Editar turma',
    'subtitle' => 'Atualize os dados da turma'
]);

?>

<div class="card">

    <form method="POST" action="<?= base_url('turmas/editar') ?>" class="user-form">

        <input type="hidden" name="id" value="<?= $class['id'] ?>">

        <div class="form-group">
            <label>Nome da turma</label>
            <input class="form-control" type="text" name="name" value="<?= e($class['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Ano letivo</label>
            <input class="form-control" type="number" name="year" value="<?= $class['year'] ?>" required>
        </div>

        <div class="form-group">
            <label>Turno</label>

            <select class="form-control" name="shift" required>
                <?php foreach (['Manhã', 'Tarde', 'Noite', 'Integral'] as $shift): ?>
                    <option value="<?= $shift ?>" <?= $class['shift'] === $shift ? 'selected' : '' ?>>
                        <?= $shift ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Status</label>

            <select class="form-control" name="active">
                <option value="1" <?= (int) $class['active'] === 1 ? 'selected' : '' ?>>Ativa</option>
                <option value="0" <?= (int) $class['active'] === 0 ? 'selected' : '' ?>>Inativa</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="<?= base_url('turmas') ?>" class="btn-secondary">Cancelar</a>

            <button type="submit" class="btn-primary">
                Atualizar
            </button>
        </div>

    </form>

</div>