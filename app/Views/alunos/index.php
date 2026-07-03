<?php

component('page-header', [
    'title' => 'Alunos',
    'subtitle' => 'Gerencie os alunos cadastrados no Sistema de Frequência Escolar'
]);

$studentSuccess = \App\Core\Session::get('student_success');
$studentError = \App\Core\Session::get('student_error');

\App\Core\Session::remove('student_success');
\App\Core\Session::remove('student_error');

?>

<?php if ($studentSuccess): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($studentSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($studentError): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($studentError) ?>
    </div>
<?php endif; ?>

<div class="card">

    <div class="table-header">

        <h3>Alunos cadastrados</h3>

        <a href="<?= base_url('alunos/novo') ?>" class="btn-primary">
            + Novo aluno
        </a>

    </div>

    <table class="data-table">

        <thead>

            <tr>
                <th width="70">ID</th>
                <th>Nome</th>
                <th width="150">Matrícula</th>
                <th width="140">Nascimento</th>
                <th>Responsável</th>
                <th width="150">Telefone</th>
                <th width="110">Status</th>
                <th width="170">Ações</th>
            </tr>

        </thead>

        <tbody>

        <?php if (empty($students)): ?>

            <tr>

                <td colspan="8" style="text-align:center;padding:40px;">
                    Nenhum aluno cadastrado.
                </td>

            </tr>

        <?php else: ?>

            <?php foreach ($students as $student): ?>

                <tr>

                    <td>
                        <?= $student['id'] ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($student['name']) ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($student['registration']) ?>
                    </td>

                    <td>
                        <?= !empty($student['birth_date'])
                            ? date('d/m/Y', strtotime($student['birth_date']))
                            : '-' ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($student['guardian_name'] ?? '-') ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($student['guardian_phone'] ?? '-') ?>
                    </td>

                    <td>

                        <?php if ((int) $student['active'] === 1): ?>

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

                        <div class="table-actions">

                            <a
                                href="<?= base_url('alunos/editar?id=' . $student['id']) ?>"
                                class="table-link"
                            >
                                Editar
                            </a>

                            <form
                                action="<?= base_url('alunos/excluir') ?>"
                                method="POST"
                                onsubmit="return confirm('Deseja realmente excluir este aluno?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $student['id'] ?>"
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