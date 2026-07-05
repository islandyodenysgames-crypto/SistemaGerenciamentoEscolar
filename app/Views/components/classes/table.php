<table class="data-table">

    <thead>
        <tr>
            <th width="70">ID</th>
            <th>Turma</th>
            <th width="120">Ano Letivo</th>
            <th width="140">Turno</th>
            <th width="110">Status</th>
            <th width="180">Criada em</th>
            <th width="170">Ações</th>
        </tr>
    </thead>

    <tbody>

    <?php if (empty($classes)): ?>

        <tr>
            <td colspan="7" class="table-empty">
                Nenhuma turma cadastrada.
            </td>
        </tr>

    <?php else: ?>

        <?php foreach ($classes as $class): ?>

            <tr>
                <td><?= (int) $class['id'] ?></td>

                <td><?= e($class['name']) ?></td>

                <td><?= (int) $class['year'] ?></td>

                <td><?= e($class['shift']) ?></td>

                <td>
                    <?php if ((int) $class['active'] === 1): ?>
                        <span class="badge badge-success">
                            Ativa
                        </span>
                    <?php else: ?>
                        <span class="badge badge-danger">
                            Inativa
                        </span>
                    <?php endif; ?>
                </td>

                <td>
                    <?= date('d/m/Y H:i', strtotime($class['created_at'])) ?>
                </td>

                <td>
                    <div class="table-actions">

                        <a
                            href="<?= base_url('turmas/editar?id=' . $class['id']) ?>"
                            class="table-link"
                        >
                            Editar
                        </a>

                        <form
                            action="<?= base_url('turmas/excluir') ?>"
                            method="POST"
                            onsubmit="return confirm('Deseja realmente excluir esta turma?');"
                        >
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $class['id'] ?>"
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