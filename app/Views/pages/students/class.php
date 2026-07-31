<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/pages/occurrence-multiple.css'
    ) ?>"
>
<link
    rel="stylesheet"
    href="<?= base_url(
        'assets/css/pages/class-intelligence.css'
    ) ?>"
>

<?php
$frequencyGoal = (float)($frequencyGoal ?? 95);

use App\Auth\Permissions;
use App\Core\Authorization;

$students = $students ?? [];
$alertStudents = $alertStudents ?? [];

$classId = (int) ($classId ?? 0);

$canManageStudents = Authorization::can(
    Permissions::STUDENTS_MANAGE
);

$canManageEnrollments = Authorization::can(
    Permissions::ENROLLMENTS_MANAGE
);

$canCreateOccurrences = Authorization::can(
    Permissions::OCCURRENCES_CREATE
);

$canViewAttendance =
    Authorization::can(Permissions::ATTENDANCE_VIEW)
    || Authorization::can(Permissions::ATTENDANCE_MANAGE);

$canManageAttendance = Authorization::can(
    Permissions::ATTENDANCE_MANAGE
);

component('base/page-header', [
    'title' => 'Painel da Turma',

    'subtitle' => trim(
        ($className ?? 'Turma')
        . ' • '
        . (
            $classYear !== ''
                ? $classYear . 'º Ano'
                : ''
        )
        . ' • '
        . ($classShift ?? '')
    ),
]);

component('favorites/page-toggle', [
    'type' => 'class',
    'id' => $classId,
    'active' => (bool) ($isClassFavorite ?? false),
    'label' => 'Favoritar turma',
]);

?>

<div class="class-workspace-nav">

    <a
        href="<?= base_url(
            'alunos/turma?id=' . $classId
        ) ?>"
        class="active"
    >
        <i data-lucide="layout-dashboard"></i>
        Painel
    </a>

    <a href="#classIntelligence">
        <i data-lucide="brain-circuit"></i>
        Inteligência
    </a>

    <a href="#classStudents">
        <i data-lucide="users"></i>
        Alunos
    </a>

    <?php if ($canManageAttendance): ?>

        <a
            href="<?= base_url(
                'frequencia/novo?turma=' . $classId
            ) ?>"
        >
            <i data-lucide="clipboard-check"></i>
            Frequência
        </a>

    <?php endif; ?>

    <?php if ($canViewAttendance): ?>

        <a
            href="<?= base_url(
                'frequencia/historico?turma=' . $classId
            ) ?>"
        >
            <i data-lucide="calendar-days"></i>
            Histórico
        </a>

    <?php endif; ?>

    <a href="#">
        <i data-lucide="chart-column"></i>
        Relatórios
    </a>

</div>

<section class="class-workspace-section">

    <div class="class-workspace-section-header">

        <div>

            <h3>Painel da turma</h3>

            <p>
                Resumo geral da turma e da frequência escolar.
            </p>

        </div>

    </div>

    <div class="students-class-kpis">

        <div class="students-class-kpi">

            <span>👨‍🎓</span>

            <strong>
                <?= (int) ($totalStudents ?? 0) ?>
            </strong>

            <small>Alunos matriculados</small>

        </div>

        <div class="students-class-kpi">

            <span>📅</span>

            <strong>
                <?= (int) ($totalRecords ?? 0) ?>
            </strong>

            <small>Registros de frequência</small>

        </div>

        <div class="students-class-kpi">

            <span>📊</span>

            <strong>
                <?= number_format(
                    (float) ($averageFrequency ?? 0),
                    1,
                    ',',
                    '.'
                ) ?>%
            </strong>

            <small>Frequência média</small>

        </div>

        <?php if ((int) ($studentsInAlert ?? 0) > 0): ?>

            <button
                type="button"
                class="students-class-kpi students-class-kpi-link"
                id="toggleClassAlertStudents"
                aria-controls="classAlertStudents"
                aria-expanded="false"
                aria-label="Mostrar alunos em alerta"
            >

                <span>⚠️</span>

                <strong>
                    <?= (int) ($studentsInAlert ?? 0) ?>
                </strong>

                <small>Alunos em alerta</small>

                <em>
                    <span data-alert-toggle-label>Ver alunos</span>
                    <i data-lucide="chevron-down" data-alert-toggle-icon></i>
                </em>

            </button>

        <?php else: ?>

            <div class="students-class-kpi">

                <span>✅</span>

                <strong>0</strong>

                <small>Alunos em alerta</small>

                <em>Nenhum aluno abaixo da meta de <?= number_format($frequencyGoal, 1, ',', '.') ?>%</em>

            </div>

        <?php endif; ?>

    </div>

</section>

<?php if (!empty($alertStudents)): ?>

    <section
        class="class-workspace-section class-alert-students"
        id="classAlertStudents"
        hidden
    >

        <div class="class-workspace-section-header">

            <div>
                <h3>Alunos em alerta de frequência</h3>
                <p>
                    Alunos com registros de frequência e percentual inferior à meta configurada de <?= number_format($frequencyGoal, 1, ',', '.') ?>%.
                </p>
            </div>

            <span class="class-alert-count">
                <?= count($alertStudents) ?> aluno(s)
            </span>

        </div>

        <div class="class-alert-students-grid">

            <?php foreach ($alertStudents as $alertStudent): ?>

                <?php
                $alertPercentage = (float) ($alertStudent['attendance_percentage'] ?? 0);
                $alertRecords = (int) ($alertStudent['total_records'] ?? 0);
                ?>

                <article class="class-alert-student-card">

                    <div class="class-alert-student-icon">
                        <i data-lucide="user-round-alert"></i>
                    </div>

                    <div class="class-alert-student-info">
                        <strong><?= e($alertStudent['name'] ?? 'Aluno') ?></strong>
                        <span>
                            <?= $alertRecords ?> registro(s) de frequência
                        </span>
                    </div>

                    <div class="class-alert-student-frequency">
                        <strong>
                            <?= number_format($alertPercentage, 1, ',', '.') ?>%
                        </strong>
                        <span>Frequência</span>
                    </div>

                    <a
                        href="<?= base_url('alunos/perfil?id=' . (int) ($alertStudent['id'] ?? 0)) ?>"
                        class="class-alert-student-action"
                    >
                        Ver perfil
                        <i data-lucide="arrow-up-right"></i>
                    </a>

                </article>

            <?php endforeach; ?>

        </div>

    </section>

<?php endif; ?>

<?php component('classes/intelligence', [
    'classIntelligence' => $classIntelligence ?? [],
    'classId' => $classId,
    'className' => $className ?? 'Turma',
]); ?>

<?php if (
    $canManageAttendance
    || $canViewAttendance
    || $canManageEnrollments
    || $canManageStudents
): ?>

    <section class="class-workspace-section">

        <div class="class-workspace-section-header">

            <div>

                <h3>Ações rápidas</h3>

                <p>
                    Acesse as principais funções da turma.
                </p>

            </div>

        </div>

        <div class="class-quick-actions">

            <?php if ($canManageAttendance): ?>

                <a
                    href="<?= base_url(
                        'frequencia/novo?turma=' . $classId
                    ) ?>"
                    class="class-quick-action"
                >
                    <i data-lucide="clipboard-check"></i>
                    <span>Nova chamada</span>
                </a>

            <?php endif; ?>

            <?php if ($canViewAttendance): ?>

                <a
                    href="<?= base_url(
                        'frequencia/historico?turma=' . $classId
                    ) ?>"
                    class="class-quick-action"
                >
                    <i data-lucide="calendar-days"></i>
                    <span>Histórico</span>
                </a>

            <?php endif; ?>

            <?php if ($canManageEnrollments): ?>

                <a
                    href="<?= base_url('matriculas/importar') ?>"
                    class="class-quick-action"
                >
                    <i data-lucide="file-spreadsheet"></i>
                    <span>Importar alunos</span>
                </a>

            <?php endif; ?>

            <?php if ($canManageStudents): ?>

                <a
                    href="<?= base_url(
                        'alunos/novo?turma='
                        . $classId
                        . '&origem=turma'
                    ) ?>"
                    class="class-quick-action"
                >
                    <i data-lucide="user-plus"></i>
                    <span>Novo aluno</span>
                </a>

                <a
                    href="<?= base_url(
                        'turmas/editar?id=' . $classId
                    ) ?>"
                    class="class-quick-action"
                >
                    <i data-lucide="settings"></i>
                    <span>Editar turma</span>
                </a>

            <?php endif; ?>

        </div>

    </section>

<?php endif; ?>

<section
    class="class-workspace-section"
    id="classStudents"
>

    <div class="class-workspace-section-header">

        <div>

            <h3>Alunos</h3>

            <p>
                <?= count($students) ?>
                aluno(s) matriculado(s) nesta turma.
            </p>

        </div>

    </div>

    <?php if ($canCreateOccurrences): ?>

        <form
            id="multipleOccurrenceForm"
            method="POST"
            action="<?= base_url(
                'ocorrencias/multiplas/nova'
            ) ?>"
        >

            <input
                type="hidden"
                name="school_class_id"
                value="<?= $classId ?>"
            >

        </form>

    <?php endif; ?>

    <?php if ($canManageStudents): ?>

        <form
            id="deleteSelectedStudentsForm"
            method="POST"
            action="<?= base_url(
                'alunos/excluir-selecionados'
            ) ?>"
            onsubmit="
                return confirm(
                    'Deseja realmente excluir os alunos selecionados?'
                );
            "
        ></form>

    <?php endif; ?>

    <div class="students-toolbar">

        <a
            href="<?= base_url('alunos') ?>"
            class="btn-secondary"
        >
            ← Voltar
        </a>

        <div class="students-toolbar-actions">

            <?php if (
                $canCreateOccurrences
                && !empty($students)
            ): ?>

                <button
                    type="button"
                    class="btn-secondary"
                    id="selectAllOccurrenceStudents"
                >
                    <i data-lucide="list-checks"></i>
                    Selecionar todos
                </button>

                <button
                    type="button"
                    class="btn-secondary"
                    id="clearOccurrenceStudents"
                    hidden
                >
                    <i data-lucide="x"></i>
                    Limpar seleção
                </button>

            <?php endif; ?>

            <?php if ($canManageStudents): ?>

                <button
                    type="submit"
                    form="deleteSelectedStudentsForm"
                    class="btn-danger"
                    id="deleteSelectedStudentsButton"
                    disabled
                >
                    Excluir selecionados
                </button>

            <?php endif; ?>

        </div>

    </div>

    <?php if (empty($students)): ?>

        <div class="card">

            <div class="activity-empty">
                Nenhum aluno matriculado nesta turma.
            </div>

        </div>

    <?php else: ?>

        <div class="student-search-box">

            <i data-lucide="search"></i>

            <input
                type="text"
                id="studentCardSearch"
                placeholder="Pesquisar aluno..."
            >

        </div>

        <div class="students-card-grid">

            <?php foreach ($students as $student): ?>

                <?php

                $studentId = (int) (
                    $student['id'] ?? 0
                );

                $studentPercentage = (float) (
                    $student['attendance_percentage'] ?? 0
                );

                $studentRecords = (int) (
                    $student['total_records'] ?? 0
                );

                $active = (int) (
                    $student['active'] ?? 0
                ) === 1;

                $frequencyClass = match (true) {
                    $studentRecords === 0 => 'warning',

                    $studentPercentage < $frequencyGoal => 'danger',

                    default => 'success',
                };

                $frequencyLabel = $studentRecords > 0
                    ? number_format(
                        $studentPercentage,
                        1,
                        ',',
                        '.'
                    ) . '%'
                    : 'Sem dados';

                ?>

                <article
                    class="
                        student-profile-card
                        student-profile-card-<?= e(
                            $frequencyClass
                        ) ?>
                    "
                    data-student-id="<?= $studentId ?>"
                    data-student-name="<?= e(
                        mb_strtolower(
                            (string) (
                                $student['name'] ?? ''
                            )
                        )
                    ) ?>"
                >

                    <div class="student-profile-header">

                        <div class="student-card-selections">

                            <?php if ($canCreateOccurrences): ?>

                                <label
                                    class="
                                        student-occurrence-select
                                        student-selection-control
                                    "
                                    title="Selecionar para ocorrência"
                                >

                                    <input
                                        type="checkbox"
                                        name="student_ids[]"
                                        value="<?= $studentId ?>"
                                        form="multipleOccurrenceForm"
                                        class="occurrence-student-checkbox"
                                        aria-label="Selecionar <?= e(
                                            $student['name'] ?? 'aluno'
                                        ) ?> para ocorrência"
                                    >

                                    <span>
                                        <i data-lucide="clipboard-pen-line"></i>
                                    </span>

                                </label>

                            <?php endif; ?>

                            <?php if ($canManageStudents): ?>

                                <label
                                    class="
                                        student-delete-select
                                        student-selection-control
                                    "
                                    title="Selecionar para exclusão"
                                >

                                    <input
                                        type="checkbox"
                                        name="ids[]"
                                        value="<?= $studentId ?>"
                                        form="deleteSelectedStudentsForm"
                                        class="delete-student-checkbox"
                                        aria-label="Selecionar <?= e(
                                            $student['name'] ?? 'aluno'
                                        ) ?> para exclusão"
                                    >

                                    <span>
                                        <i data-lucide="trash-2"></i>
                                    </span>

                                </label>

                            <?php endif; ?>

                        </div>

                        <div class="student-profile-avatar">
                            <?php if (!empty($student['photo_path'])): ?>
                                <img class="entity-avatar-photo" src="<?= e(base_url((string) $student['photo_path'])) ?>" alt="Foto de <?= e((string) ($student['name'] ?? 'Aluno')) ?>">
                            <?php else: ?>
                                <i data-lucide="user"></i>
                            <?php endif; ?>
                        </div>

                        <span
                            class="badge <?= $active
                                ? 'badge-success'
                                : 'badge-warning'
                            ?>"
                        >
                            <?= $active
                                ? 'Ativo'
                                : 'Inativo'
                            ?>
                        </span>

                    </div>

                    <div class="student-profile-body">

                        <h3>
                            <?= e(
                                $student['name'] ?? ''
                            ) ?>
                        </h3>

                        <p>
                            Matrícula:

                            <strong>
                                <?= e(
                                    $student['registration']
                                    ?? ''
                                ) ?>
                            </strong>
                        </p>

                        <div class="student-profile-metrics">

                            <div>

                                <span>📊</span>

                                <strong>
                                    <?= e($frequencyLabel) ?>
                                </strong>

                                <small>Frequência</small>

                            </div>

                            <div>

                                <span>📅</span>

                                <strong>
                                    <?= $studentRecords ?>
                                </strong>

                                <small>Registros</small>

                            </div>

                        </div>

                        <p class="student-profile-guardian">

                            Responsável:

                            <strong>
                                <?= e(
                                    $student['guardian_name']
                                    ?? '-'
                                ) ?>
                            </strong>

                        </p>

                    </div>

                    <div class="student-profile-actions">

                        <a
                            href="<?= base_url(
                                'alunos/perfil?id='
                                . $studentId
                                . '&turma='
                                . $classId
                            ) ?>"
                            class="btn-primary"
                        >
                            Perfil
                        </a>

                        <?php if ($canManageStudents): ?>

                            <a
                                href="<?= base_url(
                                    'alunos/editar?id='
                                    . $studentId
                                ) ?>"
                                class="btn-secondary"
                            >
                                Editar
                            </a>

                        <?php endif; ?>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</section>

<?php if ($canCreateOccurrences): ?>

    <div
        class="student-selection-bar"
        id="occurrenceSelectionBar"
        hidden
    >

        <div class="student-selection-bar-summary">

            <div class="student-selection-bar-icon">
                <i data-lucide="users-round"></i>
            </div>

            <div>

                <strong>
                    <span id="occurrenceSelectedCount">
                        0
                    </span>
                    aluno(s) selecionado(s)
                </strong>

                <small>
                    Registre a mesma ocorrência para todos os envolvidos.
                </small>

            </div>

        </div>

        <div class="student-selection-bar-actions">

            <button
                type="button"
                class="btn-secondary"
                id="cancelOccurrenceSelection"
            >
                Cancelar seleção
            </button>

            <button
                type="submit"
                form="multipleOccurrenceForm"
                class="btn-primary"
                id="openMultipleOccurrenceButton"
                disabled
            >
                <i data-lucide="clipboard-pen-line"></i>
                Registrar ocorrência
            </button>

        </div>

    </div>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const alertStudentsToggle = document.getElementById(
        'toggleClassAlertStudents'
    );

    const alertStudentsPanel = document.getElementById(
        'classAlertStudents'
    );

    const alertStudentsToggleLabel =
        alertStudentsToggle?.querySelector(
            '[data-alert-toggle-label]'
        );

    const alertStudentsToggleIcon =
        alertStudentsToggle?.querySelector(
            '[data-alert-toggle-icon]'
        );

    alertStudentsToggle?.addEventListener('click', () => {
        if (!alertStudentsPanel) {
            return;
        }

        const shouldOpen = alertStudentsPanel.hidden;

        alertStudentsPanel.hidden = !shouldOpen;
        alertStudentsToggle.setAttribute(
            'aria-expanded',
            shouldOpen ? 'true' : 'false'
        );
        alertStudentsToggle.setAttribute(
            'aria-label',
            shouldOpen
                ? 'Ocultar alunos em alerta'
                : 'Mostrar alunos em alerta'
        );
        alertStudentsToggle.classList.toggle(
            'is-expanded',
            shouldOpen
        );

        if (alertStudentsToggleLabel) {
            alertStudentsToggleLabel.textContent =
                shouldOpen ? 'Ocultar alunos' : 'Ver alunos';
        }

        if (alertStudentsToggleIcon) {
            alertStudentsToggleIcon.setAttribute(
                'data-lucide',
                shouldOpen ? 'chevron-up' : 'chevron-down'
            );

            if (window.lucide) {
                window.lucide.createIcons();
            }
        }

        if (shouldOpen) {
            alertStudentsPanel.scrollIntoView({
                behavior: 'smooth',
                block: 'start',
            });
        }
    });

    const searchInput = document.getElementById(
        'studentCardSearch'
    );

    searchInput?.addEventListener('input', () => {
        const search = searchInput.value
            .trim()
            .toLowerCase();

        document
            .querySelectorAll('.student-profile-card')
            .forEach((card) => {
                const name = card.dataset.studentName || '';

                card.style.display = name.includes(search)
                    ? ''
                    : 'none';
            });
    });

    const occurrenceCheckboxes = Array.from(
        document.querySelectorAll(
            '.occurrence-student-checkbox'
        )
    );

    const occurrenceSelectionBar =
        document.getElementById(
            'occurrenceSelectionBar'
        );

    const occurrenceSelectedCount =
        document.getElementById(
            'occurrenceSelectedCount'
        );

    const openMultipleOccurrenceButton =
        document.getElementById(
            'openMultipleOccurrenceButton'
        );

    const selectAllOccurrenceStudents =
        document.getElementById(
            'selectAllOccurrenceStudents'
        );

    const clearOccurrenceStudents =
        document.getElementById(
            'clearOccurrenceStudents'
        );

    const cancelOccurrenceSelection =
        document.getElementById(
            'cancelOccurrenceSelection'
        );

    function updateOccurrenceSelection() {
        const selected = occurrenceCheckboxes.filter(
            (checkbox) => checkbox.checked
        );

        const selectedCount = selected.length;

        if (occurrenceSelectedCount) {
            occurrenceSelectedCount.textContent =
                String(selectedCount);
        }

        if (occurrenceSelectionBar) {
            occurrenceSelectionBar.hidden =
                selectedCount === 0;
        }

        if (openMultipleOccurrenceButton) {
            openMultipleOccurrenceButton.disabled =
                selectedCount === 0;
        }

        if (clearOccurrenceStudents) {
            clearOccurrenceStudents.hidden =
                selectedCount === 0;
        }

        occurrenceCheckboxes.forEach((checkbox) => {
            const card = checkbox.closest(
                '.student-profile-card'
            );

            card?.classList.toggle(
                'is-selected-for-occurrence',
                checkbox.checked
            );
        });
    }

    occurrenceCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener(
            'change',
            updateOccurrenceSelection
        );
    });

    selectAllOccurrenceStudents?.addEventListener(
        'click',
        () => {
            occurrenceCheckboxes.forEach(
                (checkbox) => {
                    const card = checkbox.closest(
                        '.student-profile-card'
                    );

                    const cardVisible =
                        !card
                        || card.style.display !== 'none';

                    if (cardVisible) {
                        checkbox.checked = true;
                    }
                }
            );

            updateOccurrenceSelection();
        }
    );

    function clearOccurrenceSelection() {
        occurrenceCheckboxes.forEach(
            (checkbox) => {
                checkbox.checked = false;
            }
        );

        updateOccurrenceSelection();
    }

    clearOccurrenceStudents?.addEventListener(
        'click',
        clearOccurrenceSelection
    );

    cancelOccurrenceSelection?.addEventListener(
        'click',
        clearOccurrenceSelection
    );

    const deleteCheckboxes = Array.from(
        document.querySelectorAll(
            '.delete-student-checkbox'
        )
    );

    const deleteSelectedStudentsButton =
        document.getElementById(
            'deleteSelectedStudentsButton'
        );

    function updateDeleteSelection() {
        const selectedCount = deleteCheckboxes.filter(
            (checkbox) => checkbox.checked
        ).length;

        if (deleteSelectedStudentsButton) {
            deleteSelectedStudentsButton.disabled =
                selectedCount === 0;
        }

        deleteCheckboxes.forEach((checkbox) => {
            const card = checkbox.closest(
                '.student-profile-card'
            );

            card?.classList.toggle(
                'is-selected-for-deletion',
                checkbox.checked
            );
        });
    }

    deleteCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener(
            'change',
            updateDeleteSelection
        );
    });

    updateOccurrenceSelection();
    updateDeleteSelection();
});
</script>