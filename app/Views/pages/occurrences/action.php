<?php

$occurrence = $occurrence ?? [];
$statuses = $statuses ?? [];
$actionTypes = $actionTypes ?? [];
$actionTypeIcons = $actionTypeIcons ?? [];
$oldInput = $oldInput ?? [];

$occurrenceId = (int) (
    $occurrence['id'] ?? 0
);

$studentId = (int) (
    $occurrence['student_id'] ?? 0
);

$description = (string) (
    $oldInput['description'] ?? ''
);

$currentStatus = strtoupper(
    (string) (
        $occurrence['status'] ?? 'OPEN'
    )
);

$selectedStatus = strtoupper(
    (string) (
        $oldInput['status_after']
        ?? $currentStatus
    )
);

$selectedActionType = strtoupper(
    (string) (
        $oldInput['action_type']
        ?? 'ORIENTATION'
    )
);

component('base/page-header', [
    'title' => 'Nova providência',
    'subtitle' =>
        'Registre uma intervenção para esta ocorrência.',
]);

?>

<?php if (!empty($actionError)): ?>

    <div class="alert alert-danger">
        <?= e($actionError) ?>
    </div>

<?php endif; ?>

<div class="card occurrence-form-card">

    <div class="occurrence-action-summary">

        <div class="occurrence-action-summary-icon">
            <i data-lucide="clipboard-pen-line"></i>
        </div>

        <div>

            <span>Ocorrência</span>

            <strong>
                <?= e(
                    $occurrence['title']
                    ?? 'Ocorrência'
                ) ?>
            </strong>

            <small>
                Aluno:
                <?= e(
                    $occurrence['student_name']
                    ?? 'Aluno'
                ) ?>
            </small>

            <?php if (!empty($occurrence['subject_name'])): ?>

                <small>
                    Disciplina:
                    <?= e(
                        $occurrence['subject_name']
                    ) ?>
                </small>
                
            <?php endif; ?>

        </div>

        <span
            class="badge <?= $currentStatus === 'RESOLVED'
                ? 'badge-success'
                : 'badge-warning'
            ?>"
        >
            <?= $currentStatus === 'RESOLVED'
                ? 'Resolvida'
                : 'Aberta'
            ?>
        </span>

    </div>

    <div class="occurrence-action-original">

        <strong>Descrição da ocorrência</strong>

        <p>
            <?= nl2br(
                e(
                    $occurrence['description']
                    ?? ''
                )
            ) ?>
        </p>

    </div>

    <form
        method="POST"
        enctype="multipart/form-data"
        action="<?= base_url(
            'ocorrencias/providencia'
        ) ?>"
        class="occurrence-form"
    >

        <input
            type="hidden"
            name="occurrence_id"
            value="<?= $occurrenceId ?>"
        >

        <div class="form-grid">

            <div class="form-group form-group-full">

                <label for="action_type">
                    Tipo da providência
                </label>

                <select
                    id="action_type"
                    name="action_type"
                    required
                >

                    <?php foreach (
                        $actionTypes
                        as $value => $label
                    ): ?>

                        <option
                            value="<?= e($value) ?>"
                            <?= $selectedActionType === $value
                                ? 'selected'
                                : ''
                            ?>
                        >
                            <?= e($label) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <small>
                    Selecione a categoria que melhor
                    representa a intervenção realizada.
                </small>

            </div>

            <div class="form-group form-group-full">

                <label for="description">
                    Providência adotada
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="7"
                    maxlength="5000"
                    placeholder="Descreva a orientação, contato, atendimento ou outra intervenção realizada."
                    required
                ><?= e($description) ?></textarea>

                <small>
                    Informe claramente o que foi realizado
                    e, quando necessário, quais serão
                    os próximos passos.
                </small>

            </div>

            <div class="form-group form-group-full">
                <label>Anexos da providência <span>(opcional)</span></label>
                <?php component('media/staged-file-upload', [
                    'inputId' => 'occurrenceActionAttachments',
                    'inputName' => 'attachments[]',
                    'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.gif',
                    'maxFiles' => 10,
                    'maxSizeMb' => 25,
                    'help' => 'Imagens e documentos. Até 10 arquivos, com no máximo 25 MB por arquivo.',
                ]); ?>
                <small>Até 10 arquivos, com no máximo 25 MB por arquivo. Formatos: PDF, Word, Excel e imagens.</small>
            </div>

            <div class="form-group form-group-full">

                <label for="status_after">
                    Situação após esta providência
                </label>

                <select
                    id="status_after"
                    name="status_after"
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

        </div>

        <div class="occurrence-action-preview">

            <div class="occurrence-action-preview-icon">

                <i
                    id="actionTypePreviewIcon"
                    data-lucide="<?= e(
                        $actionTypeIcons[
                            $selectedActionType
                        ] ?? 'file-text'
                    ) ?>"
                ></i>

            </div>

            <div>

                <span>Tipo selecionado</span>

                <strong id="actionTypePreviewLabel">
                    <?= e(
                        $actionTypes[
                            $selectedActionType
                        ] ?? 'Outro'
                    ) ?>
                </strong>

            </div>

        </div>

        <div class="multiple-occurrence-warning">

            <i data-lucide="history"></i>

            <p>
                Esta providência será adicionada ao histórico
                da ocorrência com seu nome, perfil de acesso,
                data e hora. Registros anteriores não serão apagados.
            </p>

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
                Salvar providência
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById(
        'action_type'
    );

    const previewLabel = document.getElementById(
        'actionTypePreviewLabel'
    );

    const previewIcon = document.getElementById(
        'actionTypePreviewIcon'
    );

    const typeLabels = <?= json_encode(
        $actionTypes,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_APOS
        | JSON_HEX_AMP
        | JSON_HEX_QUOT
    ) ?>;

    const typeIcons = <?= json_encode(
        $actionTypeIcons,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_HEX_TAG
        | JSON_HEX_APOS
        | JSON_HEX_AMP
        | JSON_HEX_QUOT
    ) ?>;

    function updatePreview() {
        const selectedType =
            typeSelect?.value || 'OTHER';

        if (previewLabel) {
            previewLabel.textContent =
                typeLabels[selectedType]
                || 'Outro';
        }

        if (previewIcon) {
            previewIcon.setAttribute(
                'data-lucide',
                typeIcons[selectedType]
                || 'file-text'
            );
        }

        if (
            window.lucide
            && typeof window.lucide.createIcons
                === 'function'
        ) {
            window.lucide.createIcons();
        }
    }

    typeSelect?.addEventListener(
        'change',
        updatePreview
    );

    updatePreview();
});
</script>