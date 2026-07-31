'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // =====================================================
    // ELEMENTOS
    // =====================================================

    const nameInput = document.getElementById('schoolNameInput');
    const shortNameInput = document.getElementById('schoolShortNameInput');

    const primaryColorInput = document.querySelector(
        'input[name="primary_color"]'
    );

    const secondaryColorInput = document.querySelector(
        'input[name="secondary_color"]'
    );

    const namePreview = document.getElementById(
        'schoolNamePreview'
    );

    const shortNamePreview = document.getElementById(
        'schoolShortNamePreview'
    );

    // =====================================================
    // PREVIEW DOS TEXTOS
    // =====================================================

    function updatePreview() {

        const schoolName =
            nameInput?.value.trim() ||
            'Sistema de Frequência Escolar';

        const shortName =
            shortNameInput?.value.trim() ||
            'SFE';

        if (namePreview) {
            namePreview.textContent = schoolName;
        }

        if (shortNamePreview) {
            shortNamePreview.textContent = shortName;
        }

    }

    nameInput?.addEventListener(
        'input',
        updatePreview
    );

    shortNameInput?.addEventListener(
        'input',
        updatePreview
    );

    updatePreview();

    // =====================================================
    // ABAS
    // =====================================================

    const tabs = document.querySelectorAll(
        '.settings-preview-tab'
    );

    const panes = document.querySelectorAll(
        '.settings-preview-pane'
    );

    tabs.forEach((tab) => {

        tab.addEventListener('click', () => {

            tabs.forEach((button) => {
                button.classList.remove('active');
            });

            panes.forEach((pane) => {
                pane.classList.remove('active');
            });

            tab.classList.add('active');

            const target = tab.dataset.previewTarget;

            document
                .querySelector(
                    `[data-preview-pane="${target}"]`
                )
                ?.classList.add('active');

        });

    });

    // =====================================================
    // PREVIEW DO LOGO
    // =====================================================

    const logoInput = document.getElementById(
        'schoolLogoInput'
    );

    const logoBoxes = document.querySelectorAll(
        '.settings-logo-box,' +
        '.settings-sidebar-logo-icon,' +
        '.settings-report-logo,' +
        '.school-branding-ranking-logo'
    );

    logoInput?.addEventListener('change', () => {

        const file = logoInput.files?.[0];

        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {

            alert('Selecione uma imagem válida.');

            logoInput.value = '';

            return;

        }

        const imageUrl = URL.createObjectURL(file);

        logoBoxes.forEach((box) => {

            box.innerHTML = '';

            const image = document.createElement('img');

            image.src = imageUrl;

            image.alt = 'Logo da escola';

            box.appendChild(image);

        });

    });

    // =====================================================
    // CORES DINÂMICAS
    // =====================================================

    function updateColors() {

        const primaryColor =
            primaryColorInput?.value ||
            '#16a34a';

        const secondaryColor =
            secondaryColorInput?.value ||
            '#f97316';

        if (window.ThemeManager) {

            window.ThemeManager.apply(
                primaryColor,
                secondaryColor
            );

        }

    }

    primaryColorInput?.addEventListener(
        'input',
        updateColors
    );

    secondaryColorInput?.addEventListener(
        'input',
        updateColors
    );

    updateColors();

});