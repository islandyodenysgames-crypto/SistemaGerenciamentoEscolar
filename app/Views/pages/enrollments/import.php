<?php

component('base/page-header', [
    'title' => 'Importar Alunos',
    'subtitle' => 'Envie uma planilha com a lista de alunos e vincule a uma turma'
]);

?>

<div class="card mb-24">

    <form
        method="POST"
        action="<?= base_url('matriculas/importar') ?>"
        enctype="multipart/form-data"
        class="user-form"
    >

        <div class="form-grid">

            <div class="form-group">
                <label>Turma</label>

                <select
                    name="school_class_id"
                    class="form-control"
                    required
                >
                    <option value="">Selecione uma turma</option>

                    <?php foreach ($classes as $class): ?>

                        <option
                            value="<?= (int) $class['id'] ?>"
                            <?= selected((int) $class['id'], (int) ($schoolClassId ?? 0)) ?>
                        >
                            <?= e($class['name']) ?>
                            —
                            <?= (int) $class['year'] ?>º Ano
                            •
                            <?= e($class['shift']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

            <div class="form-group">
                <label>Planilha Excel</label>

                <input
                    type="file"
                    name="students_file"
                    class="form-control"
                    accept=".xlsx,.xls,.csv"
                    required
                >

                <small>
                    A primeira coluna deve conter o nome dos alunos.
                </small>
            </div>

        </div>

        <div class="form-actions">

            <a href="<?= base_url('matriculas') ?>" class="btn-secondary">
                Voltar
            </a>

            <button type="submit" class="btn-primary">
                Pré-visualizar
            </button>

        </div>

    </form>

</div>

<?php if (!empty($preview)): ?>

    <div class="card">

        <div class="table-header">

            <div>
                <h3>Pré-visualização da importação</h3>

                <p>
                    <?= count($preview) ?> aluno(s) encontrados na planilha.
                </p>
            </div>

        </div>

        <div class="table-responsive">

            <table class="table">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th>Matrícula</th>
                        <th>Situação</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($preview as $index => $student): ?>

                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong><?= e($student['name']) ?></strong>
                            </td>

                            <td>
                                <?= e($student['registration']) ?>
                            </td>

                            <td>
                                <?php if ($student['exists']): ?>
                                    <span class="badge badge-info">
                                        Já cadastrado
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-success">
                                        Novo
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <form
            method="POST"
            action="<?= base_url('matriculas/importar/confirmar') ?>"
            class="form-actions"
        >
            <a href="<?= base_url('matriculas/importar') ?>" class="btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn-primary">
                Confirmar importação
            </button>
        </form>

    </div>

<?php endif; ?>