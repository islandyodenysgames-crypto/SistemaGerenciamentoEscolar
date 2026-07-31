<?php

use App\Auth\Permissions;
use App\Core\Authorization;

$enrollments = $enrollments ?? [];

$canManageEnrollments = isset(
    $canManageEnrollments
)
    ? (bool) $canManageEnrollments
    : Authorization::can(
        Permissions::ENROLLMENTS_MANAGE
    );

$canCreateStudents = isset(
    $canCreateStudents
)
    ? (bool) $canCreateStudents
    : Authorization::can(
        Permissions::STUDENTS_MANAGE
    );

?>

<?php if (!empty($enrollmentSuccess)): ?>

    <div class="alert alert-success">
        <?= e($enrollmentSuccess) ?>
    </div>

<?php endif; ?>

<?php if (!empty($enrollmentError)): ?>

    <div class="alert alert-danger">
        <?= e($enrollmentError) ?>
    </div>

<?php endif; ?>

<div class="card">

    <div class="table-header">

        <div>

            <h3>Matrículas cadastradas</h3>

            <p>
                Consulte os alunos vinculados às turmas da escola.
            </p>

        </div>

        <?php if (
            $canManageEnrollments
            || $canCreateStudents
        ): ?>

            <div class="table-actions">

                <?php if ($canManageEnrollments): ?>

                    <a
                        href="<?= base_url(
                            'matriculas/importar'
                        ) ?>"
                        class="btn-secondary"
                    >
                        <i data-lucide="file-spreadsheet"></i>
                        Importar alunos
                    </a>

                <?php endif; ?>

                <?php if ($canCreateStudents): ?>

                    <a
                        href="<?= base_url(
                            'alunos/novo?origem=matriculas'
                        ) ?>"
                        class="btn-primary"
                    >
                        <i data-lucide="user-plus"></i>
                        Novo aluno
                    </a>

                <?php endif; ?>

            </div>

        <?php endif; ?>

    </div>

    <?php component('enrollments/table', [
        'enrollments' => $enrollments,

        'canManageEnrollments' =>
            $canManageEnrollments,
    ]); ?>

</div>