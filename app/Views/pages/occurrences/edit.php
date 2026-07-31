<?php

$occurrence = $occurrence ?? [];
$types = $types ?? [];
$statuses = $statuses ?? [];
$severities = $severities ?? [];
$severityClasses = $severityClasses ?? [];
$severityIcons = $severityIcons ?? [];
$titleSuggestions = $titleSuggestions ?? [];
$subjects = $subjects ?? [];

$selectedSubject = (int) (
    $occurrence['subject_id'] ?? 0
);

$occurrenceId = (int) (
    $occurrence['id'] ?? 0
);

$studentId = (int) (
    $occurrence['student_id'] ?? 0
);

$selectedDate = (string) (
    $occurrence['occurrence_date']
    ?? date('Y-m-d')
);

$selectedType = strtoupper(
    (string) (
        $occurrence['type']
        ?? 'OBSERVATION'
    )
);

$selectedSeverity = strtoupper(
    (string) (
        $occurrence['severity']
        ?? 'LOW'
    )
);

$selectedStatus = strtoupper(
    (string) (
        $occurrence['status']
        ?? 'OPEN'
    )
);

$selectedTitle = (string) (
    $occurrence['title'] ?? ''
);

$selectedDescription = (string) (
    $occurrence['description'] ?? ''
);

$selectedActionsTaken = (string) (
    $occurrence['actions_taken'] ?? ''
);

component('base/page-header', [
    'title' => 'Editar Ocorrência',

    'subtitle' =>
        'Aluno: '
        . e(
            $occurrence['student_name']
            ?? 'Aluno'
        ),
]);

?>

<?php if (!empty($occurrenceError)): ?>

    <div class="alert alert-danger">
        <?= e($occurrenceError) ?>
    </div>

<?php endif; ?>

<?php if (!empty($occurrenceWarning)): ?>
    <div class="alert alert-warning"><?= e($occurrenceWarning) ?></div>
<?php endif; ?>

<div class="card occurrence-form-card">

    <form
        method="POST"
        action="<?= base_url(
            'ocorrencias/editar'
        ) ?>"
        class="occurrence-form"
        enctype="multipart/form-data"
    >

        <input
            type="hidden"
            name="id"
            value="<?= $occurrenceId ?>"
        >

        <div class="occurrence-student-summary">

            <div class="occurrence-student-avatar">
                <i data-lucide="user"></i>
            </div>

            <div>

                <span>Aluno</span>

                <strong>
                    <?= e(
                        $occurrence['student_name']
                        ?? '-'
                    ) ?>
                </strong>

                <small>
                    Matrícula:
                    <?= e(
                        $occurrence[
                            'student_registration'
                        ] ?? '-'
                    ) ?>
                </small>

            </div>

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
                        $subjects
                        as $subject
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
                    Alterar a gravidade modifica a prioridade
                    desta ocorrência nos painéis e relatórios.
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

                <datalist id="occurrenceTitleList"></datalist>

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
                    placeholder="Descreva de forma clara o que ocorreu."
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
                    As providências posteriores permanecem
                    registradas separadamente na linha do tempo.
                </small>

            </div>

            <?php component('occurrences/attachments-field', [
                'attachments' => $occurrence['attachments'] ?? [],
                'occurrenceId' => $occurrenceId,
                'canRemove' => true,
            ]); ?>

        </div>

        <div class="form-actions">

            <a
                href="<?= base_url(
                    'alunos/perfil?id='
                    . $studentId
                    . '#studentOccurrences'
                ) ?>"
                class="btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-primary"
            >
                <i data-lucide="save"></i>
                Atualizar ocorrência
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

            titleList.appendChild(option);
        });
    }

    function updateSeveritySelection() {
        severityOptions.forEach((option) => {
            const input = option.querySelector(
                'input[name="severity"]'
            );

            option.classList.toggle(
                'is-selected',
                Boolean(input?.checked)
            );
        });
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