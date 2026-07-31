<?php

$done = (int) ($class['has_attendance'] ?? 0) === 1;
$percentage = $done ? (float) ($class['attendance_percentage'] ?? 0) : 0;
$attendanceId = $class['attendance_id'] ?? null;

$statusClass = $done ? 'done' : 'pending';
$statusLabel = $done ? 'Realizada' : 'Pendente';

?>

<div class="attendance-class-card-v3 attendance-class-card-v3-<?= e($statusClass) ?>">

    <div class="attendance-class-v3-icon">
        <i data-lucide="<?= $done ? 'check-circle-2' : 'clock-3' ?>"></i>
    </div>

    <div class="attendance-class-v3-main">

        <div class="attendance-class-v3-title-row">

            <div>
                <h3>
                    <?= e($class['class_name']) ?>
                </h3>

                <p>
                    <?= (int) $class['year'] ?>º Ano • <?= e($class['shift']) ?>
                </p>
            </div>

            <span class="attendance-class-v3-status attendance-class-v3-status-<?= e($statusClass) ?>">
                <?= e($statusLabel) ?>
            </span>

        </div>

        <?php if ($done): ?>

            <div class="attendance-class-v3-metrics">

                <span>👥 <strong><?= (int) $class['total_students'] ?></strong> Total</span>
                <span>✅ <strong><?= (int) $class['presentes'] ?></strong> Presentes</span>
                <span>❌ <strong><?= (int) $class['ranking_absences'] ?></strong> Faltas</span>
                <span>🟠 <strong><?= (int) $class['justificadas'] ?></strong> Justificadas</span>
                <span>🔵 <strong><?= (int) $class['atestados'] ?></strong> Atestados</span>
                <span>🟣 <strong><?= (int) $class['onibus'] ?></strong> Ônibus</span>

            </div>

        <?php else: ?>

            <p class="attendance-class-v3-pending-text">
                Frequência ainda não registrada para esta turma.
            </p>

        <?php endif; ?>

    </div>

    <div class="attendance-class-v3-progress">

        <?php component('base/progress', [
            'percentage' => $percentage,
            'label' => $done ? 'Frequência' : 'Pendente',
            'size' => 96
        ]); ?>

    </div>

    <div class="attendance-class-v3-actions">

        <?php if ($done && $attendanceId): ?>

            <a
                href="<?= base_url('frequencia/ver?id=' . $attendanceId) ?>"
                class="attendance-class-v3-action"
                title="Ver chamada"
            >
                <i data-lucide="eye"></i>
            </a>

            <a
                href="<?= base_url('frequencia/editar?id=' . $attendanceId) ?>"
                class="attendance-class-v3-action attendance-class-v3-action-primary"
                title="Editar chamada"
            >
                <i data-lucide="pencil"></i>
            </a>

            <form
                action="<?= base_url('frequencia/excluir') ?>"
                method="POST"
                onsubmit="return confirm('Deseja realmente excluir esta chamada?');"
            >
                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $attendanceId ?>"
                >

                <button
                    type="submit"
                    class="attendance-class-v3-action attendance-class-v3-action-danger"
                    title="Excluir chamada"
                >
                    <i data-lucide="trash-2"></i>
                </button>
            </form>

        <?php else: ?>

            <a
                href="<?= base_url('frequencia/novo?turma=' . $class['class_id']) ?>"
                class="attendance-class-v3-start"
            >
                <span>Iniciar</span>
                <i data-lucide="arrow-right"></i>
            </a>

        <?php endif; ?>

    </div>

</div>