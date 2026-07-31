<?php

use App\Auth\Permissions;
use App\Core\Authorization;

$canManageStudents = Authorization::can(
    Permissions::STUDENTS_MANAGE
);

component('base/alert', [
    'type' => 'success',
    'message' => $studentSuccess ?? null,
]);

component('base/alert', [
    'type' => 'danger',
    'message' => $studentError ?? null,
]);

component('base/alert', [
    'type' => 'success',
    'message' => $classSuccess ?? null,
]);

component('base/alert', [
    'type' => 'danger',
    'message' => $classError ?? null,
]);

$students = $students ?? [];
$classes = $classes ?? [];

$studentTotals = [];

foreach ($students as $student) {
    $classId = (int) (
        $student['school_class_id'] ?? 0
    );

    if ($classId <= 0) {
        continue;
    }

    $studentTotals[$classId] = (
        $studentTotals[$classId] ?? 0
    ) + 1;
}

?>

<div class="card students-management-card">

    <div class="table-header">

        <div>

            <h3>Turmas</h3>

            <p>
                <?= $canManageStudents
                    ? 'Gerencie as turmas e acesse os alunos matriculados.'
                    : 'Consulte as turmas e os alunos matriculados.'
                ?>
            </p>

        </div>

        <?php if ($canManageStudents): ?>

            <div class="table-actions">

                <a
                    href="<?= base_url('turmas/novo') ?>"
                    class="btn-primary"
                >
                    + Nova turma
                </a>

            </div>

        <?php endif; ?>

    </div>

    <div class="students-class-grid">

        <?php if (empty($classes)): ?>

            <div class="activity-empty">
                Nenhuma turma cadastrada.
            </div>

        <?php endif; ?>

        <?php foreach ($classes as $class): ?>

            <?php

            $classId = (int) (
                $class['id'] ?? 0
            );

            $total = (int) (
                $studentTotals[$classId] ?? 0
            );

            $active = (int) (
                $class['active'] ?? 0
            ) === 1;

            ?>

            <div class="students-class-card">

                <div class="students-class-card-header">

                    <div class="students-class-card-icon">
                        <i data-lucide="school"></i>
                    </div>

                    <span
                        class="badge <?= $active
                            ? 'badge-success'
                            : 'badge-danger'
                        ?>"
                    >
                        <?= $active ? 'Ativa' : 'Inativa' ?>
                    </span>

                </div>

                <a
                    href="<?= base_url(
                        'alunos/turma?id=' . $classId
                    ) ?>"
                    class="students-class-card-body"
                >

                    <h3>
                        <?= e(
                            $class['name'] ?? 'Turma'
                        ) ?>
                    </h3>

                    <div class="students-class-info">

                        <span>

                            <i data-lucide="calendar-days"></i>

                            Ano letivo:

                            <strong>
                                <?= (int) (
                                    $class['year'] ?? 0
                                ) ?>
                            </strong>

                        </span>

                        <span>

                            <i data-lucide="clock"></i>

                            Turno:

                            <strong>
                                <?= e(
                                    $class['shift'] ?? '-'
                                ) ?>
                            </strong>

                        </span>

                        <span>

                            <i data-lucide="users"></i>

                            Alunos:

                            <strong>
                                <?= $total ?>
                            </strong>

                        </span>

                    </div>

                </a>

                <div class="students-class-card-actions">

                    <a
                        href="<?= base_url(
                            'alunos/turma?id=' . $classId
                        ) ?>"
                        class="btn-primary"
                    >
                        Abrir turma
                    </a>

                    <?php if ($canManageStudents): ?>

                        <a
                            href="<?= base_url(
                                'turmas/editar?id=' . $classId
                            ) ?>"
                            class="btn-secondary"
                        >
                            Editar
                        </a>

                        <form
                            action="<?= base_url(
                                'turmas/excluir'
                            ) ?>"
                            method="POST"
                            onsubmit="
                                return confirm(
                                    'Deseja realmente excluir esta turma?'
                                );
                            "
                        >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= $classId ?>"
                            >

                            <button
                                type="submit"
                                class="btn-danger"
                            >
                                Excluir
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>