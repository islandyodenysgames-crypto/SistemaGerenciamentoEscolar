<?php

component('page-header', [
    'title' => 'Turmas',
    'subtitle' => 'Gerencie as turmas cadastradas no Sistema de Frequência Escolar'
]);

$classSuccess = \App\Core\Session::get('class_success');
$classError = \App\Core\Session::get('class_error');

\App\Core\Session::remove('class_success');
\App\Core\Session::remove('class_error');

?>

<?php if ($classSuccess): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($classSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($classError): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($classError) ?>
    </div>
<?php endif; ?>

<div class="card">

    <div class="table-header">

        <h3>Turmas cadastradas</h3>

        <a href="<?= base_url('turmas/novo') ?>" class="btn-primary">
            + Nova turma
        </a>

    </div>

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

                <td colspan="7" style="text-align:center;padding:40px;">
                    Nenhuma turma cadastrada.
                </td>

            </tr>

        <?php else: ?>

            <?php foreach ($classes as $class): ?>

                <tr>

                    <td>
                        <?= $class['id'] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($class['name']) ?>
                    </td>

                    <td>
                        <?= $class['year'] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($class['shift']) ?>
                    </td>

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
                                    value="<?= $class['id'] ?>"
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