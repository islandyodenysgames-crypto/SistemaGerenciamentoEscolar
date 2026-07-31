<?php

$student = $student ?? [];
$classes = $classes ?? [];
$currentEnrollment = $currentEnrollment ?? null;
$enrollmentHistory = $enrollmentHistory ?? [];

$studentId = (int) (
    $student['id'] ?? 0
);

$currentClassId = (int) (
    $currentEnrollment['school_class_id'] ?? 0
);

$studentError = \App\Core\Session::get(
    'student_error'
);

\App\Core\Session::remove(
    'student_error'
);

component('base/page-header', [
    'title' => 'Editar aluno',
    'subtitle' => 'Atualize os dados do aluno ou altere sua turma.',
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
        action="<?= base_url('alunos/editar') ?>"
        class="user-form"
    >

        <input
            type="hidden"
            name="id"
            value="<?= $studentId ?>"
        >

        <?php component('media/profile-photo-uploader', [
            'prefix' => 'studentPhoto',
            'title' => 'Foto do aluno',
            'currentPath' => $student['photo_path'] ?? '',
        ]); ?>

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
                    $student['name'] ?? ''
                ) ?>"
                required
            >

        </div>

        <div class="form-group">

            <label for="registration">
                Matrícula
            </label>

            <input
                class="form-control"
                id="registration"
                type="text"
                name="registration"
                value="<?= e(
                    $student['registration'] ?? ''
                ) ?>"
                required
            >

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
                    $student['birth_date'] ?? ''
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
                    $student['guardian_name'] ?? ''
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
                    $student['guardian_phone'] ?? ''
                ) ?>"
            >

        </div>

        <div class="form-group">

            <label for="school_class_id">
                Turma atual
            </label>

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
                        <?= $classId === $currentClassId
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

            <small>
                Ao selecionar outra turma, a matrícula atual será
                encerrada e uma nova será criada. O histórico será
                preservado.
            </small>

        </div>

        <div class="form-group">

            <label for="transfer_date">
                Data da mudança de turma
            </label>

            <input
                class="form-control"
                id="transfer_date"
                type="date"
                name="transfer_date"
                value="<?= date('Y-m-d') ?>"
                required
            >

            <small>
                Essa data será usada apenas se a turma for alterada.
            </small>

        </div>

        <div class="form-group">

            <label for="active">
                Status
            </label>

            <select
                class="form-control"
                id="active"
                name="active"
            >

                <option
                    value="1"
                    <?= (int) (
                        $student['active'] ?? 0
                    ) === 1
                        ? 'selected'
                        : ''
                    ?>
                >
                    Ativo
                </option>

                <option
                    value="0"
                    <?= (int) (
                        $student['active'] ?? 0
                    ) === 0
                        ? 'selected'
                        : ''
                    ?>
                >
                    Inativo
                </option>

            </select>

        </div>

        <?php if (!empty($currentEnrollment)): ?>

            <div class="form-group">

                <label>
                    Matrícula ativa atual
                </label>

                <div class="student-current-enrollment">

                    <strong>
                        <?= e(
                            $currentEnrollment['class_name']
                            ?? 'Turma'
                        ) ?>
                    </strong>

                    <span>
                        <?= (int) (
                            $currentEnrollment['class_year']
                            ?? 0
                        ) ?>º Ano
                        •
                        <?= e(
                            $currentEnrollment['class_shift']
                            ?? '-'
                        ) ?>
                    </span>

                    <small>
                        Desde
                        <?= !empty(
                            $currentEnrollment['enrollment_date']
                        )
                            ? date(
                                'd/m/Y',
                                strtotime(
                                    $currentEnrollment[
                                        'enrollment_date'
                                    ]
                                )
                            )
                            : '-'
                        ?>
                    </small>

                </div>

            </div>

        <?php endif; ?>

        <div class="form-actions">

            <a
                href="<?= $currentClassId > 0
                    ? base_url(
                        'alunos/perfil?id='
                        . $studentId
                        . '&turma='
                        . $currentClassId
                    )
                    : base_url(
                        'alunos/perfil?id='
                        . $studentId
                    )
                ?>"
                class="btn-secondary"
            >
                Cancelar
            </a>

            <button
                type="submit"
                class="btn-primary"
            >
                Atualizar
            </button>

        </div>

    </form>

</div>