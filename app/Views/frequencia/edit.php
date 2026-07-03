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

<style>
.attendance-summary {
    display:grid;
    grid-template-columns:repeat(6,1fr);
    gap:14px;
    margin:24px 0;
}

.attendance-card,
.student-card {
    background:#fff;
    border-radius:18px;
    padding:18px;
    box-shadow:0 8px 30px rgba(15,23,42,.08);
}

.attendance-card {
    text-align:center;
}

.attendance-card strong {
    display:block;
    font-size:26px;
    margin-top:6px;
}

.student-list {
    display:grid;
    gap:16px;
}

.student-name {
    font-size:22px;
    font-weight:900;
}

.student-registration {
    color:#64748b;
    margin-top:4px;
}

.status-options {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-top:16px;
}

.status-option {
    min-width:140px;
    padding:14px 18px;
    border-radius:14px;
    border:2px solid #dbe3ef;
    font-weight:900;
    cursor:pointer;
    text-align:center;
    background:#fff;
}

.status-option input {
    display:none;
}

.status-option.selected {
    color:#fff;
    border-color:transparent;
}

.status-p { color:#16a34a; }
.status-f { color:#dc2626; }
.status-fj { color:#d97706; }
.status-am { color:#2563eb; }
.status-fo { color:#7c3aed; }

.status-p.selected { background:#16a34a; }
.status-f.selected { background:#dc2626; }
.status-fj.selected { background:#d97706; }
.status-am.selected { background:#2563eb; }
.status-fo.selected { background:#7c3aed; }

@media (max-width:900px) {
    .attendance-summary {
        grid-template-columns:repeat(2,1fr);
    }

    .status-option {
        min-width:100%;
    }
}
</style>

<div class="card">

    <h3>
        <?= htmlspecialchars($attendance['class_name']) ?>
        — <?= $attendance['year'] ?>
        — <?= htmlspecialchars($attendance['shift']) ?>
    </h3>

    <form method="POST" action="<?= base_url('frequencia/editar') ?>">

        <input type="hidden" name="id" value="<?= $attendance['id'] ?>">

        <div class="user-form" style="margin-top:20px;">

            <div class="form-group">
                <label>Data da chamada</label>

                <input
                    class="form-control"
                    type="date"
                    name="attendance_date"
                    value="<?= htmlspecialchars($attendance['attendance_date']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label>Observações</label>

                <input
                    class="form-control"
                    type="text"
                    name="notes"
                    value="<?= htmlspecialchars($attendance['notes'] ?? '') ?>"
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
            data-student-name="<?= htmlspecialchars(mb_strtolower($item['student_name'])) ?>"
        >

            <div class="student-name">
                <?= htmlspecialchars($item['student_name']) ?>
            </div>

            <div class="student-registration">
                Matrícula: <?= htmlspecialchars($item['registration']) ?>
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

                        <?= htmlspecialchars($label) ?>

                    </label>

                <?php endforeach; ?>

            </div>

        </div>

    <?php endforeach; ?>

</div>

<div class="form-actions" style="margin-top:28px;">

    <a href="<?= base_url('frequencia') ?>" class="btn-secondary">
        Cancelar
    </a>

    <button type="submit" class="btn-primary" style="font-size:18px;padding:16px 28px;">
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