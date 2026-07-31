<?php

component('base/page-header', [
    'title' => 'Nova turma',
    'subtitle' => 'Cadastre uma nova turma no sistema'
]);

?>

<div class="card">

    <form method="POST" action="<?= base_url('turmas') ?>" class="user-form" enctype="multipart/form-data">

        <div class="form-group">
            <label>Nome da turma</label>
            <input class="form-control" type="text" name="name" placeholder="Ex: 1º Ano A" required>
        </div>

        <div class="form-group">
            <label>Ano letivo</label>
            <input class="form-control" type="number" name="year" value="<?= date('Y') ?>" required>
        </div>

        <div class="form-group">
            <label>Turno</label>

            <select class="form-control" name="shift" required>
                <option value="">Selecione</option>
                <option value="Manhã">Manhã</option>
                <option value="Tarde">Tarde</option>
                <option value="Noite">Noite</option>
                <option value="Integral">Integral</option>
            </select>
        </div>

        <?php component('classes/photo-field', ['inputId' => 'class-photo-create']); ?>

        <div class="form-actions">
            <a href="<?= base_url('turmas') ?>" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Salvar
            </button>
        </div>

    </form>

</div>