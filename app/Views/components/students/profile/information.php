<?php

$student = $student ?? [];

$attendancePercentage = (float) (
    $attendancePercentage ?? 0
);

$totalRecords = (int) (
    $totalRecords ?? 0
);

?>

<div
    class="student-profile-grid"
    id="studentInformation"
>

    <div class="card student-profile-info-card">

        <h3>Dados do aluno</h3>

        <div class="student-profile-info-list">

            <div>
                <span>Nome</span>

                <strong>
                    <?= e($student['name'] ?? '-') ?>
                </strong>
            </div>

            <div>
                <span>Matrícula</span>

                <strong>
                    <?= e($student['registration'] ?? '-') ?>
                </strong>
            </div>

            <div>
                <span>Data de nascimento</span>

                <strong>
                    <?php if (!empty($student['birth_date'])): ?>

                        <?= date(
                            'd/m/Y',
                            strtotime($student['birth_date'])
                        ) ?>

                    <?php else: ?>

                        -

                    <?php endif; ?>
                </strong>
            </div>

            <div>
                <span>Responsável</span>

                <strong>
                    <?= e($student['guardian_name'] ?? '-') ?>
                </strong>
            </div>

            <div>
                <span>Telefone do responsável</span>

                <strong>
                    <?= e($student['guardian_phone'] ?? '-') ?>
                </strong>
            </div>

            <div>
                <span>Situação</span>

                <?php if (
                    (int) ($student['active'] ?? 0) === 1
                ): ?>

                    <strong class="text-success">
                        Ativo
                    </strong>

                <?php else: ?>

                    <strong class="text-warning">
                        Inativo
                    </strong>

                <?php endif; ?>
            </div>

        </div>

    </div>

    <div class="card student-profile-placeholder-card">

        <h3>Resumo de frequência</h3>

        <p>
            Este aluno possui
            <strong><?= $totalRecords ?></strong>
            registro(s) de frequência, com frequência geral de
            <strong>
                <?= number_format(
                    $attendancePercentage,
                    1,
                    ',',
                    '.'
                ) ?>%
            </strong>.
        </p>

        <p>
            Abaixo você pode acompanhar os últimos registros,
            a evolução da frequência e o calendário mensal.
        </p>

    </div>

</div>