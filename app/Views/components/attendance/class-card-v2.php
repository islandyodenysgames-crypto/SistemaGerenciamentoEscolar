<?php

$done = (int) ($class['has_attendance'] ?? 0) === 1;
$percentage = $done ? (float) ($class['attendance_percentage'] ?? 0) : 0;
$attendanceId = $class['attendance_id'] ?? null;

$statusClass = $done ? 'done' : 'pending';
$statusLabel = $done ? 'Realizada' : 'Pendente';

?>

<div class="attendance-class-card-v2 attendance-class-card-v2-<?= $statusClass ?>">

    <div class="attendance-card-v2-header">

        <div>
            <span class="attendance-card-v2-eyebrow">
                Turma
            </span>

            <h3>
                <?= e($class['class_name']) ?>
                — <?= (int) $class['year'] ?>
            </h3>

            <p>
                Turno: <strong><?= e($class['shift']) ?></strong>
            </p>
        </div>

        <span class="attendance-card-v2-status attendance-card-v2-status-<?= $statusClass ?>">
            <?= $statusLabel ?>
        </span>

    </div>

    <?php if ($done): ?>

        <div class="attendance-card-v2-body">

            <div class="attendance-card-v2-progress">

                <?php component('base/progress', [
                    'percentage' => $percentage,
                    'label' => 'Frequência',
                    'size' => 135
                ]); ?>

                <strong>
                    <?= number_format($percentage, 1, ',', '.') ?>%
                </strong>

            </div>

            <div class="attendance-card-v2-metrics">

                <span>👥 <strong><?= (int) $class['total_students'] ?></strong> Total</span>
                <span>✅ <strong><?= (int) $class['presentes'] ?></strong> Presentes</span>
                <span>❌ <strong><?= (int) $class['ranking_absences'] ?></strong> Faltas</span>
                <span>🟡 <strong><?= (int) $class['justificadas'] ?></strong> Justificadas</span>
                <span>🔵 <strong><?= (int) $class['atestados'] ?></strong> Atestados</span>
                <span>🟣 <strong><?= (int) $class['onibus'] ?></strong> Ônibus</span>
                <span>⭐ <strong><?= number_format((float) $class['ife_score'], 2, ',', '.') ?></strong> IFE</span>

            </div>

        </div>

    <?php else: ?>

        <div class="attendance-card-v2-pending">

            <div class="attendance-card-v2-pending-icon">
                <i data-lucide="clock-alert"></i>
            </div>

            <div>
                <h4>Frequência ainda não registrada</h4>

                <p>
                    Esta turma ainda não possui chamada registrada para hoje.
                </p>
            </div>

        </div>

    <?php endif; ?>

    <div class="attendance-card-v2-actions">

        <?php if ($done && $attendanceId): ?>

            <a
                href="<?= base_url('frequencia/ver?id=' . $attendanceId) ?>"
                class="btn-secondary"
                title="Ver chamada"
            >
                <i data-lucide="eye"></i>
                Ver
            </a>

            <a
                href="<?= base_url('frequencia/editar?id=' . $attendanceId) ?>"
                class="btn-primary"
                title="Editar chamada"
            >
                <i data-lucide="pencil"></i>
                Editar
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
                    class="btn-danger"
                    title="Excluir chamada"
                >
                    <i data-lucide="trash-2"></i>
                    Excluir
                </button>
            </form>

        <?php else: ?>

            <a
                href="<?= base_url('frequencia/novo?turma=' . $class['class_id']) ?>"
                class="btn-primary"
            >
                <i data-lucide="play"></i>
                Iniciar frequência
            </a>

        <?php endif; ?>

    </div>

</div>