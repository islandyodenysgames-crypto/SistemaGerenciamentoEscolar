<?php

component('page-header', [
    'title' => 'Busca Inteligente',
    'subtitle' => 'Pesquise alunos, turmas e datas de frequência'
]);

?>

<div class="card">

    <form method="GET" action="<?= base_url('busca') ?>" class="user-form">

        <div class="form-group">
            <label>Pesquisar</label>

            <input
                class="form-control"
                type="text"
                name="q"
                value="<?= htmlspecialchars($term) ?>"
                placeholder="Digite o nome do aluno, matrícula, turma, turno ou data"
            >
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Buscar
            </button>
        </div>

    </form>

</div>

<?php if ($term !== ''): ?>

    <div class="card" style="margin-top:24px;">

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
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= htmlspecialchars($student['registration']) ?></td>
                            <td><?= (int) $student['active'] === 1 ? 'Ativo' : 'Inativo' ?></td>
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

    <div class="card" style="margin-top:24px;">

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
                            <td><?= htmlspecialchars($class['name']) ?></td>
                            <td><?= $class['year'] ?></td>
                            <td><?= htmlspecialchars($class['shift']) ?></td>
                            <td><?= (int) $class['active'] === 1 ? 'Ativa' : 'Inativa' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>

        <?php endif; ?>

    </div>

    <div class="card" style="margin-top:24px;">

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
                            <td><?= date('d/m/Y', strtotime($date['attendance_date'])) ?></td>
                            <td><?= $date['total_attendances'] ?></td>
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