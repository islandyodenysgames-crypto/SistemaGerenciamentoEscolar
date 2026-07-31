<?php

$students = $students ?? [];
$studentIds = $studentIds ?? [];
$types = $types ?? [];
$statuses = $statuses ?? [];
$severities = $severities ?? [];
$severityClasses = $severityClasses ?? [];
$severityIcons = $severityIcons ?? [];
$titleSuggestions = $titleSuggestions ?? [];
$oldInput = $oldInput ?? [];
$subjects = $subjects ?? [];

$selectedSubject = (int) (
    $oldInput['subject_id'] ?? 0
);

$classId = (int) (
    $classId ?? 0
);

$selectedDate = (string) (
    $oldInput['occurrence_date']
    ?? date('Y-m-d')
);

$selectedType = strtoupper(
    (string) (
        $oldInput['type']
        ?? 'OBSERVATION'
    )
);

$selectedSeverity = strtoupper(
    (string) (
        $oldInput['severity']
        ?? 'LOW'
    )
);

$selectedStatus = strtoupper(
    (string) (
        $oldInput['status']
        ?? 'OPEN'
    )
);

$selectedTitle = (string) (
    $oldInput['title'] ?? ''
);

$selectedDescription = (string) (
    $oldInput['description'] ?? ''
);

$selectedActionsTaken = (string) (
    $oldInput['actions_taken'] ?? ''
);

$normalizedStudentIds = [];

foreach ($studentIds as $studentId) {
    $studentId = (int) $studentId;

    if ($studentId > 0) {
        $normalizedStudentIds[$studentId] =
            $studentId;
    }
}

$studentIds = array_values(
    $normalizedStudentIds
);

component('base/page-header', [
    'title' => 'Ocorrência múltipla',

    'subtitle' =>
        count($students)
        . ' aluno(s) selecionado(s)',
]);

?>

<?php if (!empty($occurrenceError)): ?>

    <div class="alert alert-danger">
        <?= e($occurrenceError) ?>
    </div>

<?php endif; ?>

<div class="card occurrence-form-card">

    <form
        method="POST"
        action="<?= base_url(
            'ocorrencias/multiplas'
        ) ?>"
        class="occurrence-form"
    >

        <input
            type="hidden"
            name="school_class_id"
            value="<?= $classId ?>"
        >

        <?php foreach (
            $studentIds
            as $studentId
        ): ?>

            <input
                type="hidden"
                name="student_ids[]"
                value="<?= (int) $studentId ?>"
            >

        <?php endforeach; ?>

        <div class="multiple-occurrence-summary">

            <div class="multiple-occurrence-summary-icon">
                <i data-lucide="users"></i>
            </div>

            <div>

                <span>Alunos selecionados</span>

                <strong>
                    <?= count($students) ?>
                    aluno(s)
                </strong>

                <small>
                    A mesma ocorrência será registrada
                    individualmente no histórico de cada aluno.
                </small>

            </div>

        </div>

        <div class="multiple-occurrence-students">

            <?php foreach (
                $students
                as $student
            ): ?>

                <div class="multiple-occurrence-student">

                    <div class="multiple-occurrence-student-avatar">
                        <i data-lucide="user"></i>
                    </div>

                    <div>

                        <strong>
                            <?= e(
                                $student['name']
                                ?? 'Aluno'
                            ) ?>
                        </strong>

                        <small>
                            Matrícula:
                            <?= e(
                                $student['registration']
                                ?? '-'
                            ) ?>
                        </small>

                    </div>

                    <i
                        data-lucide="circle-check"
                        class="multiple-occurrence-student-check"
                    ></i>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="form-grid">

            <div class="form-group">

                <label for="occurrence_date">
                    Data da ocorrência
                </label>

                <input
                    type="date"
                    id="occurrence_date"
                    name="occurrence_date"
                    value="<?= e($selectedDate) ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label for="subject_id">
                    Disciplina
                </label>

                <select
                    id="subject_id"
                    name="subject_id"
                    required
                >
                    <option value="">
                        Selecione...
                    </option>

                    <?php foreach (
                        $subjects as $subject
                    ): ?>

                        <option
                            value="<?= (int) $subject['id'] ?>"
                            <?= $selectedSubject === (int) $subject['id']
                                ? 'selected'
                                : ''
                            ?>
                        >
                            <?= e(
                                $subject['name']
                            ) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group">

                <label for="type">
                    Tipo
                </label>

                <select
                    id="type"
                    name="type"
                    required
                >

                    <?php foreach (
                        $types
                        as $value => $label
                    ): ?>

                        <option
                            value="<?= e($value) ?>"
                            <?= $selectedType === $value
                                ? 'selected'
                                : ''
                            ?>
                        >
                            <?= e($label) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group form-group-full">

                <label>
                    Gravidade da ocorrência
                </label>

                <div
                    class="occurrence-severity-options"
                    id="occurrenceSeverityOptions"
                >

                    <?php foreach (
                        $severities
                        as $value => $label
                    ): ?>

                        <?php

                        $severityClass =
                            $severityClasses[$value]
                            ?? 'severity-low';

                        $severityIcon =
                            $severityIcons[$value]
                            ?? 'circle-check';

                        ?>

                        <label
                            class="
                                occurrence-severity-option
                                <?= e($severityClass) ?>
                                <?= $selectedSeverity === $value
                                    ? 'is-selected'
                                    : ''
                                ?>
                            "
                        >

                            <input
                                type="radio"
                                name="severity"
                                value="<?= e($value) ?>"
                                <?= $selectedSeverity === $value
                                    ? 'checked'
                                    : ''
                                ?>
                                required
                            >

                            <span
                                class="occurrence-severity-option-icon"
                            >
                                <i
                                    data-lucide="<?= e(
                                        $severityIcon
                                    ) ?>"
                                ></i>
                            </span>

                            <span
                                class="occurrence-severity-option-content"
                            >

                                <strong>
                                    <?= e($label) ?>
                                </strong>

                                <small>

                                    <?php if (
                                        $value === 'LOW'
                                    ): ?>

                                        Situação de menor impacto,
                                        com acompanhamento simples.

                                    <?php elseif (
                                        $value === 'MEDIUM'
                                    ): ?>

                                        Situação que exige atenção
                                        e acompanhamento da equipe.

                                    <?php elseif (
                                        $value === 'HIGH'
                                    ): ?>

                                        Situação séria, com necessidade
                                        de intervenção prioritária.

                                    <?php else: ?>

                                        Situação gravíssima, que requer
                                        atenção imediata da gestão.

                                    <?php endif; ?>

                                </small>

                            </span>

                            <span
                                class="occurrence-severity-option-check"
                            >
                                <i data-lucide="check"></i>
                            </span>

                        </label>

                    <?php endforeach; ?>

                </div>

                <small class="occurrence-severity-help">
                    A gravidade selecionada será aplicada
                    a todos os alunos envolvidos.
                </small>

            </div>

            <div class="form-group">

                <label for="status">
                    Situação
                </label>

                <select
                    id="status"
                    name="status"
                    required
                >

                    <?php foreach (
                        $statuses
                        as $value => $label
                    ): ?>

                        <option
                            value="<?= e($value) ?>"
                            <?= $selectedStatus === $value
                                ? 'selected'
                                : ''
                            ?>
                        >
                            <?= e($label) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="form-group form-group-full">

                <label for="title">
                    Título
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    list="occurrenceTitleList"
                    maxlength="150"
                    value="<?= e($selectedTitle) ?>"
                    placeholder="Digite ou selecione um título"
                    required
                >

                <datalist
                    id="occurrenceTitleList"
                ></datalist>

                <small class="occurrence-title-help">
                    As sugestões mudam conforme o tipo selecionado.
                </small>

            </div>

            <div class="form-group form-group-full">

                <label for="description">
                    Descrição
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Descreva de forma clara o que ocorreu e a participação dos alunos selecionados."
                    required
                ><?= e($selectedDescription) ?></textarea>

            </div>

            <div class="form-group form-group-full">

                <label for="actions_taken">
                    Providência inicial
                </label>

                <textarea
                    id="actions_taken"
                    name="actions_taken"
                    rows="4"
                    placeholder="Informe a providência tomada no momento do registro, quando houver."
                ><?= e($selectedActionsTaken) ?></textarea>

                <small>
                    A providência inicial também será registrada
                    individualmente no histórico de cada aluno.
                </small>

            </div>

        </div>

        <div class="multiple-occurrence-warning">

            <i data-lucide="triangle-alert"></i>

            <p>
                Ao salvar, o sistema criará
                <?= count($students) ?>
                registro(s) de ocorrência, um para cada aluno.
                A edição posterior de um registro não alterará
                automaticamente os demais.
            </p>

        </div>

        <div class="form-actions">

            <a
                href="<?= $classId > 0
                    ? base_url(
                        'alunos/turma?id='
                        . $classId
                    )
                    : base_url('alunos')
                ?>"
                class="btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-primary"
            >
                <i data-lucide="save"></i>

                Salvar para
                <?= count($students) ?>
                aluno(s)
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById(
        'type'
    );

    const titleInput = document.getElementById(
        'title'
    );

    const titleList = document.getElementById(
        'occurrenceTitleList'
    );

    const severityOptions =
        document.querySelectorAll(
            '.occurrence-severity-option'
        );

    const severityInputs =
        document.querySelectorAll(
            'input[name="severity"]'
        );

    const suggestions = <?= json_encode(
        $titleSuggestions,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_APOS
        | JSON_HEX_AMP
        | JSON_HEX_QUOT
    ) ?>;

    function updateTitleSuggestions() {
        const selectedType =
            typeSelect?.value
            || 'OBSERVATION';

        const titles =
            suggestions[selectedType]
            || suggestions.OTHER
            || [];

        if (!titleList) {
            return;
        }

        titleList.innerHTML = '';

        titles.forEach((title) => {
            const option =
                document.createElement(
                    'option'
                );

            option.value = title;

            titleList.appendChild(
                option
            );
        });
    }

    function updateSeveritySelection() {
        severityOptions.forEach(
            (option) => {
                const input =
                    option.querySelector(
                        'input[name="severity"]'
                    );

                option.classList.toggle(
                    'is-selected',
                    Boolean(input?.checked)
                );
            }
        );
    }

    typeSelect?.addEventListener(
        'change',
        updateTitleSuggestions
    );

    severityInputs.forEach((input) => {
        input.addEventListener(
            'change',
            updateSeveritySelection
        );
    });

    titleInput?.addEventListener(
        'input',
        () => {
            titleInput.setCustomValidity('');
        }
    );

    updateTitleSuggestions();
    updateSeveritySelection();

    if (
        window.lucide
        && typeof window.lucide.createIcons
            === 'function'
    ) {
        window.lucide.createIcons();
    }
});
</script>