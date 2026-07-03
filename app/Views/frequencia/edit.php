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

<script>
function selectStatus(label) {
    const input = label.querySelector('input[type="radio"]');

    if (input) {
        input.checked = true;
    }

    updateSelectedOptions();
}

function updateSelectedOptions() {
    document.querySelectorAll('.status-option').forEach(function (label) {
        label.classList.remove('selected');

        const input = label.querySelector('input');

        if (input && input.checked) {
            label.classList.add('selected');
        }
    });

    updateSummary();
}

function updateSummary() {
    const cards = document.querySelectorAll('.student-card');
    const total = cards.length;

    const presentes = document.querySelectorAll('input[value="P"]:checked').length;
    const faltas = document.querySelectorAll('input[value="F"]:checked').length;
    const justificadas = document.querySelectorAll('input[value="FJ"]:checked').length;
    const atestados = document.querySelectorAll('input[value="AM"]:checked').length;
    const onibus = document.querySelectorAll('input[value="FO"]:checked').length;

    document.getElementById('totalCount').innerText = total;
    document.getElementById('presentCount').innerText = presentes;
    document.getElementById('absenceCount').innerText = faltas;
    document.getElementById('justifiedCount').innerText = justificadas;
    document.getElementById('medicalCount').innerText = atestados;
    document.getElementById('busCount').innerText = onibus;
}

function filterStudents() {
    const search = document.getElementById('studentSearch').value.toLowerCase();

    document.querySelectorAll('.student-card').forEach(function (card) {
        const name = card.dataset.studentName || '';

        card.style.display = name.includes(search) ? '' : 'none';
    });
}

document.addEventListener('change', function (event) {
    if (event.target.matches('input[type="radio"]')) {
        updateSelectedOptions();
    }
});

document.addEventListener('DOMContentLoaded', updateSelectedOptions);
</script>