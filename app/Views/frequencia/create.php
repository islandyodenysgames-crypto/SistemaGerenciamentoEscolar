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
                            <?= e($class['name']) ?>
                            — <?= $class['year'] ?>
                            — <?= e($class['shift']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </form>

        <?php if ($classInfo): ?>

            <div class="divider"></div>

            <?php if (!empty($existingAttendance)): ?>

                <div class="attendance-done">
                    ✅ Chamada de hoje já realizada

                    <div class="mt-12">
                        <a href="<?= base_url('frequencia/ver?id=' . $existingAttendance['id']) ?>" class="btn-primary">
                            Ver chamada registrada
                        </a>
                    </div>
                </div>

            <?php else: ?>

                <div class="attendance-pending">
                    ⚠️ Chamada de hoje ainda não realizada
                </div>

            <?php endif; ?>

            <h3><?= e($classInfo['name']) ?></h3>

            <p>
                <strong>Ano:</strong> <?= $classInfo['year'] ?><br>
                <strong>Turno:</strong> <?= e($classInfo['shift']) ?>
            </p>

            <div class="divider"></div>

            <h3>Últimas chamadas</h3>

            <?php if (empty($history)): ?>

                <div class="activity-empty">
                    Nenhuma chamada anterior.
                </div>

            <?php else: ?>

                <?php foreach ($history as $item): ?>

                    <div class="history-item">
                        <strong><?= date('d/m/Y', strtotime($item['attendance_date'])) ?></strong><br>
                        <small><?= e($item['notes'] ?: 'Sem observações') ?></small>
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

                        <?php $currentStatus = $existingStatuses[$student['id']] ?? 'P'; ?>

                        <div class="student-card">

                            <div class="student-name">
                                <?= e($student['name']) ?>
                            </div>

                            <div class="student-registration">
                                Matrícula: <?= e($student['registration']) ?>
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

                                        <?= e($label) ?>
                                    </label>

                                <?php endforeach; ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

                <div class="form-actions mt-28">

                    <a href="<?= base_url('frequencia') ?>" class="btn-secondary">
                        Cancelar
                    </a>

                    <?php if (!empty($existingAttendance)): ?>

                        <button type="button" class="btn-secondary btn-large btn-disabled" disabled>
                            Chamada de hoje já realizada
                        </button>

                    <?php else: ?>

                        <button type="submit" class="btn-primary btn-large">
                            Salvar chamada
                        </button>

                    <?php endif; ?>

                </div>

            </form>

        <?php endif; ?>

    </main>

</div>