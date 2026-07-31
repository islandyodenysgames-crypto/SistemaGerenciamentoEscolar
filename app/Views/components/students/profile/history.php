<?php

$attendanceHistory = $attendanceHistory ?? [];

$statusMap = [
    'P' => [
        'icon' => '🟢',
        'label' => 'Presença',
        'class' => 'success',
    ],
    'F' => [
        'icon' => '🔴',
        'label' => 'Falta',
        'class' => 'danger',
    ],
    'FJ' => [
        'icon' => '🟠',
        'label' => 'Falta Justificada',
        'class' => 'warning',
    ],
    'AM' => [
        'icon' => '🔵',
        'label' => 'Atestado Médico',
        'class' => 'info',
    ],
    'FO' => [
        'icon' => '🟣',
        'label' => 'Falta de Ônibus',
        'class' => 'purple',
    ],
];

?>

<div
    class="card student-history-card"
    id="studentHistory"
>

    <div class="student-history-header">

        <div>
            <h3>Histórico de frequência</h3>

            <p>
                Últimos registros de frequência do aluno.
            </p>
        </div>

    </div>

    <?php if (empty($attendanceHistory)): ?>

        <div class="activity-empty">
            Nenhum registro encontrado.
        </div>

    <?php else: ?>

        <div class="student-history-timeline">

            <?php foreach ($attendanceHistory as $item): ?>

                <?php

                $code = (string) ($item['status'] ?? '');

                $status = $statusMap[$code] ?? [
                    'icon' => '⚪',
                    'label' => $code ?: 'Não informado',
                    'class' => 'default',
                ];

                ?>

                <div class="student-history-item">

                    <div
                        class="student-history-icon <?= e(
                            $status['class']
                        ) ?>"
                    >
                        <?= e($status['icon']) ?>
                    </div>

                    <div class="student-history-content">

                        <strong>
                            <?= e($status['label']) ?>
                        </strong>

                        <small>
                            <?php if (
                                !empty($item['attendance_date'])
                            ): ?>

                                <?= date(
                                    'd/m/Y',
                                    strtotime(
                                        $item['attendance_date']
                                    )
                                ) ?>

                            <?php else: ?>

                                Data não informada

                            <?php endif; ?>

                            •

                            <?= e(
                                $item['class_name'] ?? 'Turma'
                            ) ?>
                        </small>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>