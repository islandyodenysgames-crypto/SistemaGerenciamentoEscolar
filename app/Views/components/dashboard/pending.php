<?php

use App\Auth\Permissions;
use App\Core\Authorization;

$canManageAttendance = Authorization::can(
    Permissions::ATTENDANCE_MANAGE
);

?>

<div class="card pending-panel">

    <?php component('dashboard/panel-header', [
        'icon' => 'clock-alert',

        'title' => 'Turmas sem chamada hoje',

        'subtitle' => 'Pendências de registro da frequência',

        'badge' => !empty($classesWithoutAttendance)
            ? count($classesWithoutAttendance) . ' pendente(s)'
            : 'Tudo em dia',

        'badgeClass' => !empty($classesWithoutAttendance)
            ? 'badge-warning'
            : 'badge-success',
    ]); ?>

    <?php if (empty($classesWithoutAttendance)): ?>

        <div class="attendance-success">

            <div class="attendance-success-icon">
                <i data-lucide="circle-check-big"></i>
            </div>

            <h3>
                Todas as turmas registraram frequência
            </h3>

            <p>
                Excelente! Nenhuma turma está pendente hoje.
            </p>

        </div>

    <?php else: ?>

        <div class="pending-grid">

            <?php foreach ($classesWithoutAttendance as $class): ?>

                <?php if ($canManageAttendance): ?>

                    <a
                        href="<?= base_url(
                            'frequencia/novo?turma='
                            . (int) $class['id']
                        ) ?>"
                        class="pending-card"
                    >

                <?php else: ?>

                    <div class="pending-card pending-card-disabled">

                <?php endif; ?>

                        <div class="pending-icon">
                            <i data-lucide="school"></i>
                        </div>

                        <div>

                            <div class="pending-title">
                                <?= e($class['name']) ?>
                            </div>

                            <div class="pending-year">
                                <?= (int) $class['year'] ?>º Ano
                            </div>

                            <div class="pending-shift">
                                <?= e($class['shift']) ?>
                            </div>

                            <div class="pending-action">

                                <?php if ($canManageAttendance): ?>

                                    Registrar frequência →

                                <?php else: ?>

                                    Apenas visualização

                                <?php endif; ?>

                            </div>

                        </div>

                <?php if ($canManageAttendance): ?>

                    </a>

                <?php else: ?>

                    </div>

                <?php endif; ?>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>