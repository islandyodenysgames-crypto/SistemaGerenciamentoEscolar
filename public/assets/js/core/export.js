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

        const previousDisplays = Array.from(hiddenElements, (item) => item.style.display);
        hiddenElements.forEach((item) => { item.style.display = 'none'; });

        try {
            if (document.fonts?.ready) {
                await document.fonts.ready;
            }

            const exportScale = window.matchMedia('(max-width: 768px)').matches ? 1.5 : 2;
            const canvas = await html2canvas(element, {
                backgroundColor: '#ffffff',
                scale: exportScale,
                useCORS: true,
                allowTaint: false,
                logging: false
            });

            const date = new Date().toISOString().slice(0, 10);
            const blob = await new Promise((resolve, reject) => {
                canvas.toBlob((result) => {
                    if (result) resolve(result);
                    else reject(new Error('O navegador não conseguiu preparar a imagem.'));
                }, 'image/png');
            });
            const objectUrl = URL.createObjectURL(blob);
            const link = document.createElement('a');

            link.download = `${fileName}-${date}.png`;
            link.href = objectUrl;
            link.rel = 'noopener';
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            link.remove();

            // O URL precisa permanecer vivo tempo suficiente para o Chrome
            // Android transferir o arquivo ao gerenciador de downloads.
            window.setTimeout(() => URL.revokeObjectURL(objectUrl), 60000);

            return true;
        } finally {
            hiddenElements.forEach((item, index) => {
                item.style.display = previousDisplays[index] || '';
            });
        }
    }
};
