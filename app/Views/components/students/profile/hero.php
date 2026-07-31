<?php

use App\Auth\Permissions;
use App\Core\Authorization;
use App\Core\Request;

$student = $student ?? [];

$canManageStudents = Authorization::can(
    Permissions::STUDENTS_MANAGE
);

$studentId = (int) (
    $student['id'] ?? 0
);

$classId = (int) Request::get(
    'turma',
    0
);

$backUrl = $classId > 0
    ? base_url(
        'alunos/turma?id=' . $classId
    )
    : base_url('alunos');

$reportUrl = base_url(
    'alunos/relatorio?id=' . $studentId
);

if ($classId > 0) {
    $reportUrl .= '&turma=' . $classId;
}

?>

<div class="student-profile-hero card">

    <div class="student-profile-hero-avatar">
        <?php if (!empty($student['photo_path'])): ?>
            <img class="entity-avatar-photo" src="<?= e(base_url((string) $student['photo_path'])) ?>" alt="Foto de <?= e((string) ($student['name'] ?? 'Aluno')) ?>">
        <?php else: ?>
            <i data-lucide="user"></i>
        <?php endif; ?>
    </div>

    <div class="student-profile-hero-content">

        <h2>
            <?= e(
                $student['name'] ?? 'Aluno'
            ) ?>
        </h2>

        <p>
            Matrícula:

            <strong>
                <?= e(
                    $student['registration'] ?? '-'
                ) ?>
            </strong>
        </p>

        <p>
            Responsável:

            <strong>
                <?= e(
                    $student['guardian_name'] ?? '-'
                ) ?>
            </strong>
        </p>

        <p>
            Telefone:

            <strong>
                <?= e(
                    $student['guardian_phone'] ?? '-'
                ) ?>
            </strong>
        </p>

    </div>

    <div class="student-profile-hero-actions">

        <a
            href="<?= e($reportUrl) ?>"
            class="btn-primary"
        >
            Gerar relatório
        </a>

        <?php if ($canManageStudents): ?>

            <a
                href="<?= base_url(
                    'alunos/editar?id='
                    . $studentId
                ) ?>"
                class="btn-secondary"
            >
                Editar aluno
            </a>

        <?php endif; ?>

        <a
            href="<?= e($backUrl) ?>"
            class="btn-secondary"
        >
            Voltar
        </a>

    </div>

</div>