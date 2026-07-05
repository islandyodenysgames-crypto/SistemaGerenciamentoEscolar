<?php

$frequencyGoal = (float) ($frequencyGoal ?? 95);
$attendanceGoal = (float) ($attendanceGoal ?? 100);
$maxStudentsAlert = (int) ($maxStudentsAlert ?? 10);
$period = $period ?? 'Ano letivo atual';

$title = $title ?? 'Objetivo da Escola';
$icon = $icon ?? 'trophy';

?>

<footer class="manager-objective">

    <div class="manager-objective-icon">
        <i data-lucide="target"></i>
    </div>

    <div class="manager-objective-content">

        <p>
            <strong><?= e($title) ?>:</strong>
            Manter a frequência geral acima de <?= number_format($frequencyGoal, 0, ',', '.') ?>%,
            registrar <?= number_format($attendanceGoal, 0, ',', '.') ?>% das chamadas diariamente
            e manter no máximo <?= $maxStudentsAlert ?> aluno(s) em situação de alerta.
            Período: <?= e($period) ?>.
        </p>

    </div>

    <div class="manager-objective-trophy">
        <i data-lucide="<?= e($icon) ?>"></i>
    </div>

</footer>