<?php

declare(strict_types=1);

$mode = (string) (
    $mode ?? 'create'
);

$isEdit = $mode === 'edit';

$subject = $subject ?? [];

$subjectId = (int) (
    $subject['id'] ?? 0
);

$name = trim(
    (string) (
        $subject['name'] ?? ''
    )
);

$code = trim(
    (string) (
        $subject['code'] ?? ''
    )
);

$description = trim(
    (string) (
        $subject['description'] ?? ''
    )
);

$active = array_key_exists(
    'active',
    $subject
)
    ? (int) $subject['active']
    : 1;

$isGeneral = !empty(
    $subject['is_general']
)
    || strtoupper($code) === 'GERAL';

$formAction = $isEdit
    ? base_url('disciplinas/editar')
    : base_url('disciplinas');

?>

<?php if (!empty($subjectError)): ?>

    <?php component('base/alert', [
        'type' =>
            'danger',

        'message' =>
            $subjectError,
    ]); ?>

<?php endif; ?>

<section class="card subject-form-card">

    <form
        method="POST"
        action="<?= e($formAction) ?>"
        class="subject-form"
    >

        <?php if ($isEdit): ?>

            <input
                type="hidden"
                name="id"
                value="<?= $subjectId ?>"
            >

        <?php endif; ?>

        <div class="form-grid">

            <div class="form-group">

                <label for="name">
                    Nome da disciplina
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= e($name) ?>"
                    maxlength="120"
                    placeholder="Ex.: Física"
                    required
                    autofocus
                >

                <small>
                    Informe o nome completo da disciplina.
                </small>

            </div>

            <div class="form-group">

                <label for="code">
                    Código
                </label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    value="<?= e($code) ?>"
                    maxlength="30"
                    placeholder="Ex.: FIS"
                    <?= $isGeneral
                        ? 'readonly'
                        : ''
                    ?>
                    required
                >

                <small>
                    Use letras, números, hífen ou sublinhado.
                </small>

            </div>

            <div class="form-group form-group-full">

                <label for="description">
                    Descrição
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    maxlength="2000"
                    placeholder="Descrição opcional da disciplina."
                ><?= e($description) ?></textarea>

            </div>

            <div class="form-group form-group-full">

                <label class="subject-active-option">

                    <input
                        type="checkbox"
                        name="active"
                        value="1"
                        <?= $active === 1
                            ? 'checked'
                            : ''
                        ?>
                        <?= $isGeneral
                            ? 'disabled'
                            : ''
                        ?>
                    >

                    <span class="subject-active-switch"></span>

                    <span>

                        <strong>
                            Disciplina ativa
                        </strong>

                        <small>
                            Disciplinas ativas podem ser vinculadas aos usuários e selecionadas nas ocorrências.
                        </small>

                    </span>

                </label>

                <?php if ($isGeneral): ?>

                    <input
                        type="hidden"
                        name="active"
                        value="1"
                    >

                    <div class="subject-general-warning">

                        <i data-lucide="shield-check"></i>

                        <p>
                            A disciplina <strong>Geral da Escola</strong> é protegida e deve permanecer ativa.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        <div class="form-actions">

            <a
                href="<?= base_url(
                    'disciplinas'
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

                <?= $isEdit
                    ? 'Atualizar disciplina'
                    : 'Cadastrar disciplina'
                ?>
            </button>

        </div>

    </form>

</section>