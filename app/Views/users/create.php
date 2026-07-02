<?php

component('page-header', [
    'title' => 'Novo usuário',
    'subtitle' => 'Cadastre um novo usuário do sistema'
]);

?>

<div class="card">

    <form method="POST" action="<?= base_url('usuarios') ?>" class="user-form">

        <div class="form-group">
            <label>Nome</label>
            <input class="form-control" type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>E-mail</label>
            <input class="form-control" type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Senha</label>
            <input class="form-control" type="password" name="password" required>
        </div>

        <div class="form-actions">
            <a href="<?= base_url('usuarios') ?>" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Salvar
            </button>
        </div>

    </form>

</div>