<?php

component('base/page-header', [
    'title' => 'Editar usuário',
    'subtitle' => 'Atualize os dados do usuário'
]);

?>

<div class="card">

    <form method="POST" action="<?= base_url('usuarios/editar') ?>" class="user-form">

        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <div class="form-group">
            <label>Nome</label>
            <input class="form-control" type="text" name="name" value="<?= $user['name'] ?>" required>
        </div>

        <div class="form-group">
            <label>E-mail</label>
            <input class="form-control" type="email" name="email" value="<?= $user['email'] ?>" required>
        </div>

        <div class="form-group">
            <label>Nova senha</label>
            <input class="form-control" type="password" name="password" placeholder="Deixe em branco para manter a senha atual">
        </div>

        <div class="form-group">
            <label>Status</label>

            <select class="form-control" name="active">
                <option value="1" <?= (int) $user['active'] === 1 ? 'selected' : '' ?>>Ativo</option>
                <option value="0" <?= (int) $user['active'] === 0 ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="<?= base_url('usuarios') ?>" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Atualizar
            </button>
        </div>

    </form>

</div>