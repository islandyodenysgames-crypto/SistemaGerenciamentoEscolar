<?php

component('page-header', [
    'title' => 'Editar chamada',
    'subtitle' => 'Corrija a frequência com visual otimizado para tablet'
]);

$statusLabels = $statusOptions ?? [
    'P'  => 'Presença',
    'F'  => 'Falta',
    'FJ' => 'Falta Justificada',
    'AM' => 'Atestado Médico',
    'FO' => 'Falta de Ônibus',
];

?>

<div class="card">

    <h3>
        <?= e($attendance['class_name']) ?>
        — <?= $attendance['year'] ?>
        — <?= e($attendance['shift']) ?>
    </h3>

    <form method="POST" action="<?= base_url('frequencia/editar') ?>">

        <input type="hidden" name="id" value="<?= $attendance['id'] ?>">

        <div class="user-form mt-20">

            <div class="form-group">
                <label>Data da chamada</label>

                <input
                    class="form-control"
                    type="date"
                    name="attendance_date"
                    value="<?= e($attendance['attendance_date']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Observações</label>

                <input
                    class="form-control"
                    type="text"
                    name="notes"
                    value="<?= e($attendance['notes'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label>Buscar aluno</label>

                <input
                    class="form-control"
                    type="text"
                    id="studentSearch"
                    placeholder="Digite parte do nome do aluno"
                    onkeyup="filterStudents()"
                >
            </div>

        </div>

</div>

<div class="attendance-summary">

    <div class="attendance-card">
        <span>Total</span>
        <strong id="totalCount">0</strong>
    </div>

    <div class="attendance-card">
        <span>Presentes</span>
        <strong id="presentCount">0</strong>
    </div>

    <div class="attendance-card">
        <span>Faltas</span>
        <strong id="absenceCount">0</strong>
    </div>

    <div class="attendance-card">
        <span>Justificadas</span>
        <strong id="justifiedCount">0</strong>
    </div>

    <div class="attendance-card">
        <span>Atestados</span>
        <strong id="medicalCount">0</strong>
    </div>

    <div class="attendance-card">
        <span>Ônibus</span>
        <strong id="busCount">0</strong>
    </div>

</div>

<div class="student-list">

    <?php foreach ($items as $item): ?>

        <div
            class="student-card"
            data-student-name="<?= e(mb_strtolower($item['student_name'])) ?>"
        >

            <div class="student-name">
                <?= e($item['student_name']) ?>
            </div>

            <div class="student-registration">
                Matrícula: <?= e($item['registration']) ?>
            </div>

            <div class="status-options">

                <?php foreach ($statusLabels as $code => $label): ?>

                    <label
                        class="status-option status-<?= strtolower($code) ?>"
                        onclick="selectStatus(this)"
                    >

                        <input
                            type="radio"
                            name="status[<?= $item['id'] ?>]"
                            value="<?= $code ?>"
                            <?= $item['status'] === $code ? 'checked' : '' ?>
                        >

                        <?= e($label) ?>

                    </label>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<div class="form-actions mt-28">

    <a href="<?= base_url('frequencia') ?>" class="btn-secondary">
        Cancelar
    </a>

    <button type="submit" class="btn-primary btn-large">
        Salvar alterações
    </button>

</div>

</form>