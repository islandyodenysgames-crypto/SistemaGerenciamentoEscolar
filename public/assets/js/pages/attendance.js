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

function markAll(status) {
    document
        .querySelectorAll('input[type="radio"][value="' + status + '"]')
        .forEach(function (input) {
            input.checked = true;
        });

    updateSelectedOptions();
}

function updateSummary() {
    const total = document.querySelectorAll('.student-card').length;

    const presentes = document.querySelectorAll('input[value="P"]:checked').length;
    const faltas = document.querySelectorAll('input[value="F"]:checked').length;
    const justificadas = document.querySelectorAll('input[value="FJ"]:checked').length;
    const atestados = document.querySelectorAll('input[value="AM"]:checked').length;
    const onibus = document.querySelectorAll('input[value="FO"]:checked').length;

    setText('totalCount', total);
    setText('presentCount', presentes);
    setText('absenceCount', faltas);
    setText('justifiedCount', justificadas);
    setText('medicalCount', atestados);
    setText('busCount', onibus);

    const percentageElement = document.getElementById('percentageCount');

    if (percentageElement) {
        const percentual = total > 0 ? ((presentes / total) * 100).toFixed(1) : 0;
        percentageElement.innerText = String(percentual).replace('.', ',') + '%';
    }

    const progressElement = document.getElementById('attendanceProgress');

    if (progressElement) {
        const progress = total > 0 ? (presentes / total) * 100 : 0;
        progressElement.style.width = progress + '%';
    }
}

function filterStudents() {
    const searchInput = document.getElementById('studentSearch');

    if (!searchInput) {
        return;
    }

    const search = searchInput.value.toLowerCase();

    document.querySelectorAll('.student-card').forEach(function (card) {
        const name = card.dataset.studentName || '';

        card.style.display = name.includes(search) ? '' : 'none';
    });
}

function setText(id, value) {
    const element = document.getElementById(id);

    if (element) {
        element.innerText = value;
    }
}

document.addEventListener('change', function (event) {
    if (event.target.matches('input[type="radio"]')) {
        updateSelectedOptions();
    }
});

document.addEventListener('DOMContentLoaded', updateSelectedOptions);