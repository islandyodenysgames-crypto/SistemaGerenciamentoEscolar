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
            <td colspan="6" class="table-empty">
                Nenhum usuário cadastrado.
            </td>
        </tr>

    <?php else: ?>

        <?php foreach ($users as $user): ?>

            <tr>
                <td><?= (int) $user['id'] ?></td>

                <td><?= e($user['name']) ?></td>

                <td><?= e($user['email']) ?></td>

                <td>
                    <?php if ((int) $user['active'] === 1): ?>
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
                                value="<?= (int) $user['id'] ?>"
                            >

                            <button type="submit" class="table-delete">
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