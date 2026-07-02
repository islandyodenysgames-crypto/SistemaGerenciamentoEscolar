<?php

component('page-header', [
    'title' => 'Usuários',
    'subtitle' => 'Gerencie os usuários do Sistema de Frequência Escolar'
]);

$userSuccess = \App\Core\Session::get('user_success');
$userError = \App\Core\Session::get('user_error');

\App\Core\Session::remove('user_success');
\App\Core\Session::remove('user_error');

?>

<?php if ($userSuccess): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($userSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($userError): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($userError) ?>
    </div>
<?php endif; ?>

<div class="card">

    <div class="table-header">

        <h3>Usuários cadastrados</h3>

        <a href="<?= base_url('usuarios/novo') ?>" class="btn-primary">
            + Novo usuário
        </a>

    </div>

    <table class="data-table">

        <thead>

            <tr>
                <th width="70">ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th width="110">Status</th>
                <th width="180">Criado em</th>
                <th width="170">Ações</th>
            </tr>

        </thead>

        <tbody>

        <?php if (empty($users)): ?>

            <tr>

                <td colspan="6" style="text-align:center;padding:40px;">
                    Nenhum usuário cadastrado.
                </td>

            </tr>

        <?php else: ?>

            <?php foreach ($users as $user): ?>

                <tr>

                    <td>
                        <?= $user['id'] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user['name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user['email']) ?>
                    </td>

                    <td>

                        <?php if ((int)$user['active'] === 1): ?>

                            <span class="badge badge-success">
                                Ativo
                            </span>

                        <?php else: ?>

                            <span class="badge badge-danger">
                                Inativo
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>
                        <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                    </td>

                    <td>

                        <div class="table-actions">

                            <a
                                href="<?= base_url('usuarios/editar?id=' . $user['id']) ?>"
                                class="table-link"
                            >
                                Editar
                            </a>

                            <form
                                action="<?= base_url('usuarios/excluir') ?>"
                                method="POST"
                                onsubmit="return confirm('Deseja realmente excluir este usuário?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $user['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="table-delete"
                                >
                                    Excluir
                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>