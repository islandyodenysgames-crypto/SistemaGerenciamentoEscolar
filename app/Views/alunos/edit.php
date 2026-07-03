<?php

component('page-header', [
    'title' => 'Editar aluno',
    'subtitle' => 'Atualize os dados do aluno'
]);

$studentError = \App\Core\Session::get('student_error');

\App\Core\Session::remove('student_error');

?>

<?php if ($studentError): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($studentError) ?>
    </div>
<?php endif; ?>

<div class="card">

    <form method="POST" action="<?= base_url('alunos/editar') ?>" class="user-form">

        <input type="hidden" name="id" value="<?= $student['id'] ?>">

        <div class="form-group">
            <label>Nome do aluno</label>
            <input class="form-control" type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
        </div>

        <div class="form-group">
            <label>Matrícula</label>
            <input class="form-control" type="text" name="registration" value="<?= htmlspecialchars($student['registration']) ?>" required>
        </div>

        <div class="form-group">
            <label>Data de nascimento</label>
            <input class="form-control" type="date" name="birth_date" value="<?= htmlspecialchars($student['birth_date'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Nome do responsável</label>
            <input class="form-control" type="text" name="guardian_name" value="<?= htmlspecialchars($student['guardian_name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Telefone do responsável</label>
            <input class="form-control" type="text" name="guardian_phone" value="<?= htmlspecialchars($student['guardian_phone'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Status</label>

            <select class="form-control" name="active">
                <option value="1" <?= (int) $student['active'] === 1 ? 'selected' : '' ?>>Ativo</option>
                <option value="0" <?= (int) $student['active'] === 0 ? 'selected' : '' ?>>Inativo</option>
            </select>
        </div>

        <div class="form-actions">
            <a href="<?= base_url('alunos') ?>" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Atualizar
            </button>
        </div>

    </form>

</div>