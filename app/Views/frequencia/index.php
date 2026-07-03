<?php

component('page-header', [
    'title' => 'Frequência',
    'subtitle' => 'Gerencie as chamadas das turmas'
]);

$attendanceSuccess = \App\Core\Session::get('attendance_success');
$attendanceError = \App\Core\Session::get('attendance_error');

\App\Core\Session::remove('attendance_success');
\App\Core\Session::remove('attendance_error');

?>

<?php if ($attendanceSuccess): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($attendanceSuccess) ?>
    </div>
<?php endif; ?>

<?php if ($attendanceError): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($attendanceError) ?>
    </div>
<?php endif; ?>

<div class="card">

    <div class="table-header">

        <h3>Chamadas registradas</h3>

        <a href="<?= base_url('frequencia/novo') ?>" class="btn-primary">
            + Nova chamada
        </a>

    </div>

    <table class="data-table">

        <thead>

            <tr>
                <th width="70">ID</th>
                <th>Turma</th>
                <th width="110">Ano</th>
                <th width="120">Turno</th>
                <th width="140">Data</th>
                <th>Observações</th>
            </tr>

        </thead>

        <tbody>

        <?php if (empty($attendances)): ?>

            <tr>
                <td colspan="6" style="text-align:center;padding:40px;">
                    Nenhuma chamada registrada.
                </td>
            </tr>

        <?php else: ?>

            <?php foreach ($attendances as $attendance): ?>

                <tr>
                    <td><?= $attendance['id'] ?></td>

                    <td><?= htmlspecialchars($attendance['class_name']) ?></td>

                    <td><?= $attendance['year'] ?></td>

                    <td><?= htmlspecialchars($attendance['shift']) ?></td>

                    <td><?= date('d/m/Y', strtotime($attendance['attendance_date'])) ?></td>

                    <td><?= htmlspecialchars($attendance['notes'] ?? '-') ?></td>
                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>

</div>