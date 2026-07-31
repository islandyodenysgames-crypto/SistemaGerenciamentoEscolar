<?php

$history = is_array($history ?? null) ? $history : [];
$classes = is_array($classes ?? null) ? $classes : [];
$filtered = ($date ?? '') !== '' || (int) ($classId ?? 0) > 0;

component('base/page-header', [
    'title' => 'Histórico de Frequências',
    'subtitle' => 'Consulte, confira e gerencie as chamadas já realizadas.',
]);

component('base/alert', ['type' => 'success', 'message' => $attendanceSuccess ?? null]);
component('base/alert', ['type' => 'danger', 'message' => $attendanceError ?? null]);
?>

<section class="attendance-report-filter card">
    <div class="attendance-report-section-heading">
        <div class="attendance-report-heading-icon"><i data-lucide="list-filter"></i></div>
        <div>
            <h2>Localizar chamadas</h2>
            <p>Use a data ou a turma para reduzir os registros exibidos.</p>
        </div>
    </div>

    <form method="GET" action="<?= base_url('frequencia/historico') ?>" class="attendance-report-filter-grid">
        <div class="form-group">
            <label for="attendanceHistoryDate">Data</label>
            <input id="attendanceHistoryDate" type="date" name="data" class="form-control" value="<?= e($date ?? '') ?>">
        </div>

        <div class="form-group attendance-report-filter-class">
            <label for="attendanceHistoryClass">Turma</label>
            <select id="attendanceHistoryClass" name="turma" class="form-control">
                <option value="">Todas as turmas</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?= (int) $class['id'] ?>" <?= (int) ($classId ?? 0) === (int) $class['id'] ? 'selected' : '' ?>>
                        <?= e($class['name']) ?> — <?= (int) $class['year'] ?>º Ano • <?= e($class['shift']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="attendance-report-filter-actions">
            <button type="submit" class="btn-primary"><i data-lucide="search"></i> Filtrar</button>
            <?php if ($filtered): ?>
                <a href="<?= base_url('frequencia/historico') ?>" class="btn-secondary"><i data-lucide="x"></i> Limpar filtros</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="attendance-report-table-card card">
    <div class="attendance-report-table-heading">
        <div>
            <span class="attendance-report-eyebrow">Histórico</span>
            <h2>Chamadas registradas</h2>
            <p><?= count($history) ?> registro(s) encontrado(s)</p>
        </div>
        <div class="attendance-report-total-badge"><i data-lucide="clipboard-check"></i><?= count($history) ?></div>
    </div>

    <form method="POST" action="<?= base_url('frequencia/excluir-selecionadas') ?>" onsubmit="return confirm('Deseja realmente excluir as frequências selecionadas?');">
        <div class="attendance-report-bulk-actions">
            <span id="attendanceSelectionSummary">Nenhuma chamada selecionada</span>
            <button type="submit" class="btn-danger" id="deleteSelectedAttendances" disabled><i data-lucide="trash-2"></i> Excluir selecionadas</button>
        </div>

        <div class="table-responsive attendance-report-table-wrap">
            <table class="table attendance-report-table">
                <thead><tr>
                    <th class="attendance-col-select" width="52"><input type="checkbox" id="selectAllAttendances" aria-label="Selecionar todas"></th>
                    <th class="attendance-col-date">Data</th>
                    <th class="attendance-col-class">Turma</th>
                    <th class="attendance-col-metric attendance-group-start">Presentes</th>
                    <th class="attendance-col-metric">Faltas</th>
                    <th class="attendance-col-metric">Justificadas</th>
                    <th class="attendance-col-metric">Atestados</th>
                    <th class="attendance-col-metric">Ônibus</th>
                    <th class="attendance-col-frequency attendance-group-start">Frequência</th>
                    <th class="attendance-col-actions attendance-group-start" width="210">Ações</th>
                </tr></thead>
                <tbody>
                <?php if (empty($history)): ?>
                    <tr><td colspan="10"><div class="attendance-report-empty"><i data-lucide="calendar-x"></i><strong>Nenhuma frequência encontrada</strong><p>Altere os filtros para ampliar a consulta.</p></div></td></tr>
                <?php endif; ?>
                <?php foreach ($history as $attendance):
                    $percentage = (float) ($attendance['percentage'] ?? 0);
                    $frequencyClass = $percentage >= 85 ? 'good' : ($percentage >= 75 ? 'attention' : 'critical');
                ?>
                    <tr>
                        <td class="attendance-col-select"><input type="checkbox" name="ids[]" value="<?= (int) $attendance['id'] ?>" class="attendance-checkbox" aria-label="Selecionar chamada"></td>
                        <td class="attendance-col-date"><strong><?= date('d/m/Y', strtotime($attendance['attendance_date'])) ?></strong></td>
                        <td class="attendance-col-class"><div class="attendance-report-class-cell"><strong><?= e($attendance['class_name']) ?></strong><small><?= (int) $attendance['year'] ?>º Ano • <?= e($attendance['shift']) ?></small></div></td>
                        <td class="attendance-col-metric attendance-group-start"><span class="attendance-count present"><?= (int) $attendance['presentes'] ?></span></td>
                        <td class="attendance-col-metric"><span class="attendance-count absent"><?= (int) $attendance['faltas'] ?></span></td>
                        <td class="attendance-col-metric"><span class="attendance-count justified"><?= (int) $attendance['justificadas'] ?></span></td>
                        <td class="attendance-col-metric"><span class="attendance-count medical"><?= (int) $attendance['atestados'] ?></span></td>
                        <td class="attendance-col-metric"><span class="attendance-count bus"><?= (int) $attendance['onibus'] ?></span></td>
                        <td class="attendance-col-frequency attendance-group-start"><span class="attendance-percentage <?= $frequencyClass ?>"><?= number_format($percentage, 1, ',', '.') ?>%</span></td>
                        <td class="attendance-col-actions attendance-group-start"><div class="attendance-report-row-actions">
                            <a href="<?= base_url('frequencia/ver?id=' . $attendance['id']) ?>" class="btn-secondary"><i data-lucide="eye"></i> Ver</a>
                            <a href="<?= base_url('frequencia/editar?id=' . $attendance['id']) ?>" class="btn-primary"><i data-lucide="pencil"></i> Editar</a>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectAll = document.getElementById('selectAllAttendances');
    const checkboxes = [...document.querySelectorAll('.attendance-checkbox')];
    const deleteButton = document.getElementById('deleteSelectedAttendances');
    const summary = document.getElementById('attendanceSelectionSummary');
    const update = () => {
        const selected = checkboxes.filter((checkbox) => checkbox.checked).length;
        if (summary) summary.textContent = selected === 0 ? 'Nenhuma chamada selecionada' : `${selected} chamada(s) selecionada(s)`;
        if (deleteButton) deleteButton.disabled = selected === 0;
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && selected === checkboxes.length;
            selectAll.indeterminate = selected > 0 && selected < checkboxes.length;
        }
    };
    selectAll?.addEventListener('change', () => { checkboxes.forEach((checkbox) => checkbox.checked = selectAll.checked); update(); });
    checkboxes.forEach((checkbox) => checkbox.addEventListener('change', update));
    update();
});
</script>
