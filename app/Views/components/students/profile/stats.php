<?php

$attendancePercentage = (float) ($attendancePercentage ?? 0);
$totalRecords = (int) ($totalRecords ?? 0);
$totalFaltas = (int) ($totalFaltas ?? 0);
$totalJustificadas = (int) ($totalJustificadas ?? 0);

?>

<div class="student-profile-stats">

    <div class="student-profile-stat">
        <span>📊</span>

        <strong>
            <?= number_format(
                $attendancePercentage,
                1,
                ',',
                '.'
            ) ?>%
        </strong>

        <small>Frequência</small>
    </div>

    <div class="student-profile-stat">
        <span>📅</span>
        <strong><?= $totalRecords ?></strong>
        <small>Chamadas</small>
    </div>

    <div class="student-profile-stat">
        <span>❌</span>
        <strong><?= $totalFaltas ?></strong>
        <small>Faltas</small>
    </div>

    <div class="student-profile-stat">
        <span>⚠️</span>
        <strong><?= $totalJustificadas ?></strong>
        <small>Justificadas</small>
    </div>

</div>