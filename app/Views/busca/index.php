<?php

component('page-header', [
    'title' => 'Busca Inteligente',
    'subtitle' => 'Pesquise alunos, turmas e datas de frequência'
]);

?>

<div class="card">

    <form
        method="GET"
        action="<?= base_url('busca') ?>"
        class="user-form"
    >

        <div class="form-group">

            <label>Pesquisar</label>

            <input
                class="form-control"
                type="text"
                name="q"
                value="<?= e($term) ?>"
                placeholder="Digite o nome do aluno, matrícula, turma, turno ou data"
            >

        </div>

        <div class="form-actions">

            <button
                type="submit"
                class="btn-primary"
            >
                Buscar
            </button>

        </div>

    </form>

</div>

<?php if ($term !== ''): ?>

    <div class="card mt-24">

        <div class="card-header">
            <h3>Alunos encontrados</h3>
        </div>

        <?php if (empty($results['students'])): ?>

            <div class="activity-empty">
                Nenhum aluno encontrado.
            </div>

        <?php else: ?>

            <table class="data-table">

                <thead>

                    <tr>
                        <th>Aluno</th>
                        <th width="160">Matrícula</th>
                        <th width="120">Status</th>
                        <th width="100">Ações</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($results['students'] as $student): ?>

                        <tr>

                            <td>
                                <?= e($student['name']) ?>
                            </td>

                            <td>
                                <?= e($student['registration']) ?>
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

                                <a
                                    href="<?= base_url('alunos/editar?id=' . $student['id']) ?>"
                                    class="table-link"
                                >
                                    Ver
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

    <div class="card mt-24">

        <div class="card-header">
            <h3>Turmas encontradas</h3>
        </div>

        <?php if (empty($results['classes'])): ?>

            <div class="activity-empty">
                Nenhuma turma encontrada.
            </div>

        <?php else: ?>

            <table class="data-table">

                <thead>

                    <tr>
                        <th>Turma</th>
                        <th width="110">Ano</th>
                        <th width="120">Turno</th>
                        <th width="120">Status</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($results['classes'] as $class): ?>

                        <tr>

                            <td>
                                <?= e($class['name']) ?>
                            </td>

                            <td>
                                <?= $class['year'] ?>
                            </td>

                            <td>
                                <?= e($class['shift']) ?>
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

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

    <div class="card mt-24">

        <div class="card-header">
            <h3>Datas encontradas</h3>
        </div>

        <?php if (empty($results['dates'])): ?>

            <div class="activity-empty">
                Nenhuma data encontrada.
            </div>

        <?php else: ?>

            <table class="data-table">

                <thead>

                    <tr>
                        <th>Data</th>
                        <th width="180">Chamadas registradas</th>
                        <th width="120">Ações</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($results['dates'] as $date): ?>

                        <tr>

                            <td>
                                <?= date('d/m/Y', strtotime($date['attendance_date'])) ?>
                            </td>

                            <td>
                                <?= $date['total_attendances'] ?>
                            </td>

                            <td>

                                <a
                                    href="<?= base_url('relatorios/diario?data=' . $date['attendance_date']) ?>"
                                    class="table-link"
                                >
                                    Relatório
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

<?php endif; ?>