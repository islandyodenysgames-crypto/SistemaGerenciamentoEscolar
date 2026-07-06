'use strict';

window.AppExport = {
    async downloadPNG({ elementSelector, fileName = 'imagem' }) {
        const element = document.querySelector(elementSelector);

        if (!element) {
            alert('Elemento não encontrado para exportação.');
            return;
        }

        if (typeof html2canvas === 'undefined') {
            alert('html2canvas não foi carregado.');
            return;
        }

        const hiddenElements = element.querySelectorAll('[data-export-hide]');

        hiddenElements.forEach((item) => {
            item.style.display = 'none';
        });

        try {
            const canvas = await html2canvas(element, {
                backgroundColor: '#ffffff',
                scale: 2,
                useCORS: true,
                logging: false
            });

            const date = new Date().toISOString().slice(0, 10);
            const link = document.createElement('a');

            link.download = `${fileName}-${date}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        } finally {
            hiddenElements.forEach((item) => {
                item.style.display = '';
            });
        }
    }
};