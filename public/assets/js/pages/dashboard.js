'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('shareRankingBtn');

    if (!button) {
        return;
    }

    button.addEventListener('click', async () => {
        if (!window.AppExport) {
            alert('AppExport não foi carregado.');
            return;
        }

        button.disabled = true;
        button.innerHTML = '<i data-lucide="loader-circle"></i>';

        if (window.lucide) {
            lucide.createIcons();
        }

        try {
            await window.AppExport.downloadPNG({
                elementSelector: '#rankingShareCard',
                fileName: 'ranking-diario'
            });
        } catch (error) {
            console.error(error);
            alert('Erro ao gerar PNG.');
        }

        button.disabled = false;
        button.innerHTML = '<i data-lucide="image-down"></i>';

        if (window.lucide) {
            lucide.createIcons();
        }
    });
});