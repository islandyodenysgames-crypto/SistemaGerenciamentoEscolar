<?php

component('page-header', [
    'title' => 'Nova chamada',
    'subtitle' => 'Registro rápido de frequência por turma'
]);

$statusLabels = [
    'P'  => 'Presença',
    'F'  => 'Falta',
    'FJ' => 'Falta Justificada',
    'AM' => 'Atestado Médico',
    'FO' => 'Falta de Ônibus',
];

$existingStatuses = $existingStatuses ?? [];

?>

<style>
.attendance-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 24px;
}

.attendance-panel,
.attendance-main {
    background: #fff;
    border-radius: 18px;
    padding: 24px;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
}

.attendance-summary {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 14px;
    margin-bottom: 18px;
}

.attendance-card {
    padding: 18px;
    border-radius: 16px;
    background: #f8fafc;
    text-align: center;
}

.attendance-card strong {
    display: block;
    font-size: 26px;
}

.student-attendance {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}

.student-card {
    border: 2px solid #e5e7eb;
    border-radius: 18px;
    padding: 18px;
    background: #fff;
}

.student-name {
    font-size: 22px;
    font-weight: 900;
}

.student-registration {
    font-size: 14px;
    color: #64748b;
}

.status-options {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 16px;
}

.status-option {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 130px;
    padding: 14px 18px;
    border-radius: 14px;
    border: 2px solid #dbe3ef;
    font-weight: 900;
    cursor: pointer;
    background: #fff;
}

.status-option input {
    display: none;
}

.status-option.selected {
    color: #fff;
    border-color: transparent;
}

.status-p { color: #16a34a; }
.status-f { color: #dc2626; }
.status-fj { color: #d97706; }
.status-am { color: #2563eb; }
.status-fo { color: #7c3aed; }

.status-p.selected { background: #16a34a; }
.status-f.selected { background: #dc2626; }
.status-fj.selected { background: #d97706; }
.status-am.selected { background: #2563eb; }
.status-fo.selected { background: #7c3aed; }

.attendance-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin: 20px 0;
}

.history-item {
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
}

.attendance-done {
    margin-bottom: 20px;
    padding: 16px;
    border-radius: 14px;
    background: #dcfce7;
    color: #166534;
    font-weight: 800;
}

.attendance-pending {
    margin-bottom: 20px;
    padding: 16px;
    border-radius: 14px;
    background: #fef3c7;
    color: #92400e;
    font-weight: 800;
}

.attendance-progress {
    margin-bottom: 24px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
    height: 18px;
}

.attendance-progress-bar {
    width: 100%;
    height: 100%;
    background: #16a34a;
    transition: .3s;
}

@media (max-width: 1100px) {
    .attendance-summary {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .attendance-layout {
        grid-template-columns: 1fr;
    }

    .attendance-summary {
        grid-template-columns: repeat(2, 1fr);
    }

    .status-option {
        min-width: 100%;
    }
}
</style>

<div class="attendance-layout">

    <aside class="attendance-panel">

        <form method="GET" action="<?= base_url('frequencia/novo') ?>" class="user-form">

            <div class="form-group">
                <label>Turma</label>

                <select class="form-control" name="turma" required onchange="this.form.submit()">
                    <option value="">Selecione uma turma</option>

                    <?php foreach ($classes as $class): ?>
                        <option
                            value="<?= $class['id'] ?>"
                            <?= (int) $classId === (int) $class['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($class['name']) ?>
                            — <?= $class['year'] ?>
                            — <?= htmlspecialchars($class['shift']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </form>

        <?php if ($classInfo): ?>

            <hr style="margin:20px 0;">

            <?php if (!empty($existingAttendance)): ?>

                <div class="attendance-done">
                    ✅ Chamada de hoje já realizada

                    <div style="margin-top:12px;">
                        <a
                            href="<?= base_url('frequencia/ver?id=' . $existingAttendance['id']) ?>"
                            class="btn-primary"
                        >
                            Ver chamada registrada
                        </a>
                    </div>
                </div>

            <?php else: ?>

                <div class="attendance-pending">
                    ⚠️ Chamada de hoje ainda não realizada
                </div>

            <?php endif; ?>

            <h3><?= htmlspecialchars($classInfo['name']) ?></h3>

            <p>
                <strong>Ano:</strong> <?= $classInfo['year'] ?><br>
                <strong>Turno:</strong> <?= htmlspecialchars($classInfo['shift']) ?>
            </p>

            <hr style="margin:20px 0;">

            <h3>Últimas chamadas</h3>

            <?php if (empty($history)): ?>

                <div class="activity-empty">
                    Nenhuma chamada anterior.
                </div>

            <?php else: ?>

                <?php foreach ($history as $item): ?>

                    <div class="history-item">
                        <strong><?= date('d/m/Y', strtotime($item['attendance_date'])) ?></strong><br>
                        <small><?= htmlspecialchars($item['notes'] ?: 'Sem observações') ?></small>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        <?php endif; ?>

    </aside>

    <main class="attendance-main">

        <?php if (!$classId): ?>

            <div class="activity-empty">
                Selecione uma turma para iniciar a chamada.
            </div>

        <?php elseif (empty($students)): ?>

            <div class="activity-empty">
                Nenhum aluno matriculado nesta turma.
            </div>

        <?php else: ?>

            <form method="POST" action="<?= base_url('frequencia') ?>">

                <input type="hidden" name="school_class_id" value="<?= $classId ?>">

                <div class="user-form">

                    <div class="form-group">
                        <label>Data da chamada</label>

                        <input
                            class="form-control"
                            type="date"
                            name="attendance_date"
                            value="<?= $today ?? date('Y-m-d') ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Observações gerais</label>

                        <input
                            class="form-control"
                            type="text"
                            name="notes"
                            placeholder="Opcional"
                        >
                    </div>

                </div>

                <div class="attendance-summary">

                    <div class="attendance-card">
                        <span>Total</span>
                        <strong id="totalCount"><?= count($students) ?></strong>
                    </div>

                    <div class="attendance-card">
                        <span>Presentes</span>
                        <strong id="presentCount">0</strong>
                    </div>

                    <div class="attendance-card">
                        <span>Faltas</span>
                        <strong id="absenceCount">0</strong>
                    </div>

                    <div class="attendance-card">
                        <span>Justificadas</span>
                        <strong id="justifiedCount">0</strong>
                    </div>

                    <div class="attendance-card">
                        <span>Atestados</span>
                        <strong id="medicalCount">0</strong>
                    </div>

                    <div class="attendance-card">
                        <span>Ônibus</span>
                        <strong id="busCount">0</strong>
                    </div>

                    <div class="attendance-card">
                        <span>Frequência</span>
                        <strong id="percentageCount">0%</strong>
                    </div>

                </div>

                <div class="attendance-progress">
                    <div id="attendanceProgress" class="attendance-progress-bar"></div>
                </div>

                <div class="attendance-actions">

                    <button type="button" class="btn-primary" onclick="markAll('P')">
                        Marcar todos como Presença
                    </button>

                    <button type="button" class="btn-secondary" onclick="markAll('F')">
                        Marcar todos como Falta
                    </button>

                </div>

                <div class="student-attendance">

                    <?php foreach ($students as $student): ?>

                        <?php
                            $currentStatus = $existingStatuses[$student['id']] ?? 'P';
                        ?>

                        <div class="student-card">

                            <div class="student-name">
                                <?= htmlspecialchars($student['name']) ?>
                            </div>

                            <div class="student-registration">
                                Matrícula: <?= htmlspecialchars($student['registration']) ?>
                            </div>

                            <div class="status-options">

                                <?php foreach ($statusLabels as $code => $label): ?>

                                    <label
                                        class="status-option status-<?= strtolower($code) ?>"
                                        onclick="selectStatus(this)"
                                    >

                                        <input
                                            type="radio"
                                            name="status[<?= $student['id'] ?>]"
                                            value="<?= $code ?>"
                                            <?= $currentStatus === $code ? 'checked' : '' ?>
                                        >

                                        <?= htmlspecialchars($label) ?>

                                    </label>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

                <div class="form-actions" style="margin-top:28px;">

                    <a href="<?= base_url('frequencia') ?>" class="btn-secondary">
                        Cancelar
                    </a>

                    <?php if (!empty($existingAttendance)): ?>

                        <button
                            type="button"
                            class="btn-secondary"
                            style="font-size:20px;padding:18px 32px;opacity:.7;cursor:not-allowed;"
                            disabled
                        >
                            Chamada de hoje já realizada
                        </button>

                    <?php else: ?>

                        <button
                            type="submit"
                            class="btn-primary"
                            style="font-size:20px;padding:18px 32px;"
                        >
                            Salvar chamada
                        </button>

                    <?php endif; ?>

                </div>

            </form>

        <?php endif; ?>

    </main>

</div>

<script>
function selectStatus(label) {
    const input = label.querySelector('input[type="radio"]');

    if (input) {
        input.checked = true;
    }

    updateSelectedOptions();
}

function updateSelectedOptions() {
    document.querySelectorAll('.status-option').forEach(function (label) {
        label.classList.remove('selected');

        const input = label.querySelector('input');

        if (input && input.checked) {
            label.classList.add('selected');
        }
    });

    updateSummary();
}

function markAll(status) {
    document
        .querySelectorAll('input[type="radio"][value="' + status + '"]')
        .forEach(function (input) {
            input.checked = true;
        });

    updateSelectedOptions();
}

function updateSummary() {
    const total = document.querySelectorAll('.student-card').length;

    const presentes = document.querySelectorAll('input[value="P"]:checked').length;
    const faltas = document.querySelectorAll('input[value="F"]:checked').length;
    const justificadas = document.querySelectorAll('input[value="FJ"]:checked').length;
    const atestados = document.querySelectorAll('input[value="AM"]:checked').length;
    const onibus = document.querySelectorAll('input[value="FO"]:checked').length;

    const percentual = total > 0 ? ((presentes / total) * 100).toFixed(1) : 0;
    const progress = total > 0 ? (presentes / total) * 100 : 0;

    document.getElementById('totalCount').innerText = total;
    document.getElementById('presentCount').innerText = presentes;
    document.getElementById('absenceCount').innerText = faltas;
    document.getElementById('justifiedCount').innerText = justificadas;
    document.getElementById('medicalCount').innerText = atestados;
    document.getElementById('busCount').innerText = onibus;
    document.getElementById('percentageCount').innerText = percentual.replace('.', ',') + '%';
    document.getElementById('attendanceProgress').style.width = progress + '%';
}

document.addEventListener('change', function (event) {
    if (event.target.matches('input[type="radio"]')) {
        updateSelectedOptions();
    }
});

document.addEventListener('DOMContentLoaded', updateSelectedOptions);
</script>