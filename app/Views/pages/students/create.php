<?php

$classes = $classes ?? [];
$oldInput = $oldInput ?? [];

$selectedClassId = (int) (
    $selectedClassId ?? 0
);

$lockClass = (bool) (
    $lockClass ?? false
);

$origin = (string) (
    $origin ?? 'alunos'
);

$registrationValue = (string) (
    $oldInput['registration']
    ?? $generatedRegistration
    ?? ''
);

$studentError = \App\Core\Session::get(
    'student_error'
);

\App\Core\Session::remove(
    'student_error'
);

$cancelUrl = match ($origin) {
    'turma' => $selectedClassId > 0
        ? base_url(
            'alunos/turma?id='
            . $selectedClassId
        )
        : base_url('alunos'),

    'matriculas' => base_url('matriculas'),

    default => base_url('alunos'),
};

component('base/page-header', [
    'title' => 'Novo aluno',

    'subtitle' => $lockClass
        ? 'Cadastre o aluno e vincule-o à turma selecionada.'
        : 'Cadastre o aluno e selecione a turma da matrícula.',
]);

?>

<?php if (!empty($studentError)): ?>

    <div class="alert alert-danger">
        <?= e($studentError) ?>
    </div>

<?php endif; ?>

<div class="card">

    <form
        method="POST"
        action="<?= base_url('alunos') ?>"
        class="user-form"
    >

        <input
            type="hidden"
            name="origin"
            value="<?= e($origin) ?>"
        >

        <div class="form-group">

            <label for="name">
                Nome do aluno
            </label>

            <input
                class="form-control"
                id="name"
                type="text"
                name="name"
                value="<?= e(
                    $oldInput['name'] ?? ''
                ) ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="registration">
                Matrícula
            </label>

            <div class="student-registration-field">

                <input
                    class="form-control"
                    id="registration"
                    type="text"
                    name="registration"
                    value="<?= e($registrationValue) ?>"
                    required
                >

                <button
                    type="button"
                    class="btn-secondary"
                    id="generateRegistration"
                >
                    <i data-lucide="refresh-cw"></i>
                    Gerar matrícula
                </button>

            </div>

            <small>
                Você pode usar a matrícula gerada ou informar outra.
            </small>

        </div>

        <div class="form-group">

            <label for="birth_date">
                Data de nascimento
            </label>

            <input
                class="form-control"
                id="birth_date"
                type="date"
                name="birth_date"
                value="<?= e(
                    $oldInput['birth_date'] ?? ''
                ) ?>"
            >

        </div>

        <div class="form-group">

            <label for="guardian_name">
                Nome do responsável
            </label>

            <input
                class="form-control"
                id="guardian_name"
                type="text"
                name="guardian_name"
                value="<?= e(
                    $oldInput['guardian_name'] ?? ''
                ) ?>"
            >

        </div>

        <div class="form-group">

            <label for="guardian_phone">
                Telefone do responsável
            </label>

            <input
                class="form-control"
                id="guardian_phone"
                type="text"
                name="guardian_phone"
                value="<?= e(
                    $oldInput['guardian_phone'] ?? ''
                ) ?>"
            >

        </div>

        <div class="form-group">

            <label for="school_class_id">
                Turma
            </label>

            <?php if ($lockClass): ?>

                <input
                    type="hidden"
                    name="school_class_id"
                    value="<?= $selectedClassId ?>"
                >

                <select
                    class="form-control"
                    id="school_class_id"
                    disabled
                >

                    <?php foreach ($classes as $class): ?>

                        <?php

                        $classId = (int) (
                            $class['id'] ?? 0
                        );

                        ?>

                        <?php if (
                            $classId === $selectedClassId
                        ): ?>

                            <option selected>

                                <?= e(
                                    $class['name'] ?? 'Turma'
                                ) ?>

                                —

                                <?= (int) (
                                    $class['year'] ?? 0
                                ) ?>º Ano

                                —

                                <?= e(
                                    $class['shift'] ?? '-'
                                ) ?>

                            </option>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </select>

                <small>
                    O aluno será automaticamente vinculado a esta turma.
                </small>

            <?php else: ?>

                <select
                    class="form-control"
                    id="school_class_id"
                    name="school_class_id"
                    required
                >

                    <option value="">
                        Selecione uma turma
                    </option>

                    <?php foreach ($classes as $class): ?>

                        <?php

                        $classId = (int) (
                            $class['id'] ?? 0
                        );

                        ?>

                        <option
                            value="<?= $classId ?>"
                            <?= $selectedClassId === $classId
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= e(
                                $class['name'] ?? 'Turma'
                            ) ?>

                            —

                            <?= (int) (
                                $class['year'] ?? 0
                            ) ?>º Ano

                            —

                            <?= e(
                                $class['shift'] ?? '-'
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            <?php endif; ?>

        </div>

        <div class="form-actions">

            <a
                href="<?= e($cancelUrl) ?>"
                class="btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-primary"
            >
                Salvar e matricular
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById(
        'generateRegistration'
    );

    const input = document.getElementById(
        'registration'
    );

    button?.addEventListener('click', () => {
        const year = new Date().getFullYear();

        const randomNumber = Math.floor(
            Math.random() * 1000000
        );

        const formattedNumber = String(
            randomNumber
        ).padStart(6, '0');

        input.value = `${year}-${formattedNumber}`;

        input.focus();
        input.select();
    });
});
</script>