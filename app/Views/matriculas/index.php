<?php

component('page-header', [
    'title' => 'Matrículas',
    'subtitle' => 'Gerencie as matrículas dos alunos nas turmas'
]);

$enrollmentSuccess = \App\Core\Session::get('enrollment_success');
$enrollmentError = \App\Core\Session::get('enrollment_error');

\App\Core\Session::remove('enrollment_success');
\App\Core\Session::remove('enrollment_error');

?>

<?php if ($enrollmentSuccess): ?>
    <div class="alert alert-success">
        <?= e($enrollmentSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($enrollmentError): ?>
    <div class="alert alert-danger">
        <?= e($enrollmentError) ?>
    </div>
<?php endif; ?>

<div class="card">

    <div class="table-header">

        <h3>Matrículas cadastradas</h3>

        <a href="<?= base_url('matriculas/novo') ?>" class="btn-primary">
            + Nova matrícula
        </a>

    </div>

    <table class="data-table">

        <thead>
            <tr>
                <th width="70">ID</th>
                <th>Aluno</th>
                <th width="150">Matrícula</th>
                <th>Turma</th>
                <th width="100">Ano</th>
                <th width="120">Turno</th>
                <th width="130">Data</th>
                <th width="110">Status</th>
                <th width="140">Ações</th>
            </tr>
        </thead>

        <tbody>

        <?php if (empty($enrollments)): ?>

            <tr>
                <td colspan="9" class="table-empty">
                    Nenhuma matrícula cadastrada.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($enrollments as $enrollment): ?>

                <tr>

                    <td><?= $enrollment['id'] ?></td>

                    <td><?= e($enrollment['student_name']) ?></td>

                    <td><?= e($enrollment['registration']) ?></td>

                    <td><?= e($enrollment['class_name']) ?></td>

                    <td><?= $enrollment['year'] ?></td>

                    <td><?= e($enrollment['shift']) ?></td>

                    <td><?= date('d/m/Y', strtotime($enrollment['enrollment_date'])) ?></td>

                    <td>
                        <?php if ((int) $enrollment['active'] === 1): ?>
                            <span class="badge badge-success">
                                Ativa
                            </span>
                        <?php else: ?>
                            <span class="badge badge-danger">
                                Cancelada
                            </span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <?php if ((int) $enrollment['active'] === 1): ?>

                            <form
                                action="<?= base_url('matriculas/cancelar') ?>"
                                method="POST"
                                onsubmit="return confirm('Deseja realmente cancelar esta matrícula?');"
                            >
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $enrollment['id'] ?>"
                                >

                                <button type="submit" class="table-delete">
                                    Cancelar
                                </button>
                            </form>

                        <?php else: ?>

                            <span class="text-muted">
                                —
                            </span>

                        <?php endif; ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>