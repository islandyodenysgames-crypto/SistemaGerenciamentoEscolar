(() => {
    const formatSize = (bytes) => {
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
        return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
    };

    const fileKey = (file) => `${file.name}:${file.size}:${file.lastModified}`;

    const boot = (root) => {
        if (root.dataset.ready === '1') return;
        root.dataset.ready = '1';

        const picker = root.querySelector('.staged-file-upload__picker');
        const committed = root.querySelector('.staged-file-upload__committed');
        const selectButton = root.querySelector('[data-file-select]');
        const sendButton = root.querySelector('[data-file-send]');
        const status = root.querySelector('[data-file-status]');
        const queue = root.querySelector('[data-file-queue]');
        const maxFiles = Number(root.dataset.maxFiles || 10);
        const maxSize = Number(root.dataset.maxSize || 26214400);
        let pending = [];
        let uploaded = [];

        const syncCommitted = () => {
            const transfer = new DataTransfer();
            uploaded.forEach((file) => transfer.items.add(file));
            committed.files = transfer.files;
            committed.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const renderQueue = () => {
            queue.innerHTML = '';
            queue.hidden = uploaded.length === 0;
            uploaded.forEach((file, index) => {
                const row = document.createElement('div');
                row.className = 'staged-file-upload__item';
                row.innerHTML = `<span class="staged-file-upload__file-icon"><i data-lucide="paperclip"></i></span><span class="staged-file-upload__file-info"><strong></strong><small></small></span><button type="button" class="staged-file-upload__remove" aria-label="Remover arquivo" title="Remover arquivo"><i data-lucide="x"></i></button>`;
                row.querySelector('strong').textContent = file.name;
                row.querySelector('small').textContent = formatSize(file.size);
                row.querySelector('button').addEventListener('click', () => {
                    uploaded.splice(index, 1);
                    syncCommitted();
                    renderQueue();
                    status.className = 'staged-file-upload__status';
                    status.querySelector('span').textContent = uploaded.length ? `${uploaded.length} arquivo(s) pronto(s) para salvar.` : 'Nenhum arquivo selecionado.';
                    if (window.lucide) window.lucide.createIcons();
                });
                queue.appendChild(row);
            });
            if (window.lucide) window.lucide.createIcons();
        };

        selectButton.addEventListener('click', () => picker.click());

        picker.addEventListener('change', () => {
            pending = Array.from(picker.files || []);
            sendButton.disabled = pending.length === 0;
            status.className = 'staged-file-upload__status is-pending';
            status.querySelector('span').textContent = pending.length
                ? `${pending.length} arquivo(s) selecionado(s). Clique em “Enviar arquivo”.`
                : 'Nenhum arquivo selecionado.';
        });

        const form = root.closest('form');
        if (form) {
            form.addEventListener('submit', (event) => {
                if (event.submitter && event.submitter.hasAttribute('formaction')) return;
                if (!pending.length) return;
                event.preventDefault();
                status.className = 'staged-file-upload__status is-error';
                status.querySelector('span').textContent = 'Clique em “Enviar arquivo” antes de salvar o formulário.';
                root.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }

        sendButton.addEventListener('click', () => {
            if (!pending.length) return;
            const errors = [];
            const existing = new Set(uploaded.map(fileKey));
            for (const file of pending) {
                if (file.size > maxSize) {
                    errors.push(`${file.name}: excede ${formatSize(maxSize)}.`);
                    continue;
                }
                if (existing.has(fileKey(file))) continue;
                if (uploaded.length >= maxFiles) {
                    errors.push(`Limite de ${maxFiles} arquivos atingido.`);
                    break;
                }
                uploaded.push(file);
                existing.add(fileKey(file));
            }
            pending = [];
            picker.value = '';
            sendButton.disabled = true;
            syncCommitted();
            renderQueue();
            status.className = `staged-file-upload__status ${errors.length ? 'is-error' : 'is-ready'}`;
            status.querySelector('span').textContent = errors.length
                ? errors.join(' ')
                : `${uploaded.length} arquivo(s) pronto(s). Clique em “Salvar” ou “Salvar alterações” para concluir.`;
        });
    };

    const init = (scope = document) => scope.querySelectorAll('[data-staged-file-upload]').forEach(boot);
    document.addEventListener('DOMContentLoaded', () => init());
    document.addEventListener('monitoring:drawer-opened', (event) => init(event.target || document));
    window.StagedFileUpload = { init };
})();
