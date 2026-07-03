<?php

$done = (int) ($class['has_attendance'] ?? 0) === 1;
$percentage = $done ? (float) ($class['attendance_percentage'] ?? 0) : 0;
$attendanceId = $class['attendance_id'] ?? null;

?>

<div class="attendance-class-card">

    <div class="attendance-class-info">

        <h3>
            <?= htmlspecialchars($class['class_name']) ?>
            — <?= $class['year'] ?>
        </h3>

        <p>
            Turno: <strong><?= htmlspecialchars($class['shift']) ?></strong>
        </p>

        <?php component('attendance-status-badge', [
            'done' => $done
        ]); ?>

        <?php if ($done): ?>

            <div class="attendance-class-numbers">
                <span>👥 Total: <strong><?= (int) $class['total_students'] ?></strong></span>
                <span>✅ Presentes: <strong><?= (int) $class['presentes'] ?></strong></span>
                <span>❌ Faltas: <strong><?= (int) $class['ranking_absences'] ?></strong></span>
                <span>🟡 Justificadas: <strong><?= (int) $class['justificadas'] ?></strong></span>
                <span>🔵 Atestados: <strong><?= (int) $class['atestados'] ?></strong></span>
                <span>🟣 Ônibus: <strong><?= (int) $class['onibus'] ?></strong></span>
                <span>⭐ IFE: <strong><?= number_format((float) $class['ife_score'], 2, ',', '.') ?></strong></span>
            </div>

        <?php else: ?>

            <div class="attendance-class-numbers">
                <span>Sem chamada registrada hoje.</span>
            </div>

        <?php endif; ?>

    </div>

    <div class="attendance-class-progress">

        <?php component('attendance-progress-circle', [
            'percentage' => $percentage,
            'label' => $done ? 'Frequência' : 'Pendente',
            'size' => 120
        ]); ?>

    </div>

    <div class="attendance-class-actions">

        <?php if ($done && $attendanceId): ?>

            <a
                href="<?= base_url('frequencia/ver?id=' . $attendanceId) ?>"
                class="btn-secondary"
            >
                Ver
            </a>

            <a
                href="<?= base_url('frequencia/editar?id=' . $attendanceId) ?>"
                class="btn-primary"
            >
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
                    value="<?= $attendanceId ?>"
                >

                <button
                    type="submit"
                    class="table-delete"
                >
                    Excluir
                </button>
            </form>

        <?php else: ?>

            <a
                href="<?= base_url('frequencia/novo?turma=' . $class['class_id']) ?>"
                class="btn-primary"
            >
                Iniciar frequência
            </a>

        <?php endif; ?>

    </div>

</div>