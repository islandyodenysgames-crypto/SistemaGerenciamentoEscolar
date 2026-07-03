<?php

component('page-header', [
    'title' => 'Novo aluno',
    'subtitle' => 'Cadastre um novo aluno no sistema'
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

    <form method="POST" action="<?= base_url('alunos') ?>" class="user-form">

        <div class="form-group">
            <label>Nome do aluno</label>
            <input class="form-control" type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Matrícula</label>
            <input class="form-control" type="text" name="registration" required>
        </div>

        <div class="form-group">
            <label>Data de nascimento</label>
            <input class="form-control" type="date" name="birth_date">
        </div>

        <div class="form-group">
            <label>Nome do responsável</label>
            <input class="form-control" type="text" name="guardian_name">
        </div>

        <div class="form-group">
            <label>Telefone do responsável</label>
            <input class="form-control" type="text" name="guardian_phone">
        </div>

        <div class="form-actions">
            <a href="<?= base_url('alunos') ?>" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Salvar
            </button>
        </div>

    </form>

</div>