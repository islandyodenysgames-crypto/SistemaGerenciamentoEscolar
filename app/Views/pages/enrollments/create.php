<?php

component('page-header', [
    'title' => 'Nova matrícula',
    'subtitle' => 'Vincule um aluno a uma turma'
]);

$enrollmentError = \App\Core\Session::get('enrollment_error');

\App\Core\Session::remove('enrollment_error');

?>

<?php if ($enrollmentError): ?>
    <div class="alert alert-danger">
        <?= e($enrollmentError) ?>
    </div>
<?php endif; ?>

<div class="card">

    <form method="POST" action="<?= base_url('matriculas') ?>" class="user-form">

        <div class="form-group">
            <label>Aluno</label>

            <select class="form-control" name="student_id" required>
                <option value="">Selecione um aluno</option>

                <?php foreach ($students as $student): ?>
                    <option value="<?= $student['id'] ?>">
                        <?= e($student['name']) ?> — <?= e($student['registration']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Turma</label>

            <select class="form-control" name="school_class_id" required>
                <option value="">Selecione uma turma</option>

                <?php foreach ($classes as $class): ?>
                    <option value="<?= $class['id'] ?>">
                        <?= e($class['name']) ?> — <?= $class['year'] ?> — <?= e($class['shift']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Data da matrícula</label>
            <input
                class="form-control"
                type="date"
                name="enrollment_date"
                value="<?= date('Y-m-d') ?>"
                required
            >
        </div>

        <div class="form-actions">
            <a href="<?= base_url('matriculas') ?>" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Salvar
            </button>
        </div>

    </form>

</div>