(() => {
    const boot = () => {
        document.querySelectorAll('[data-class-photo-field]:not([data-class-photo-ready])').forEach((field) => {
            field.dataset.classPhotoReady = '1';

            const form = field.closest('form');
            const input = field.querySelector('[data-class-photo-input]');
            const prepare = field.querySelector('[data-class-photo-prepare]');
            const status = field.querySelector('[data-class-photo-status]');
            const output = field.querySelector('[data-class-photo-output]');
            const preview = field.querySelector('[data-class-photo-preview]');
            const modal = field.querySelector('[data-class-photo-modal]');
            const canvas = field.querySelector('[data-class-photo-canvas]');
            const zoom = field.querySelector('[data-class-photo-zoom]');
            const apply = field.querySelector('[data-class-photo-apply]');
            const remove = field.querySelector('[data-class-photo-remove]');
            const ctx = canvas?.getContext('2d');

            if (!form || !input || !prepare || !status || !output || !preview || !modal || !canvas || !zoom || !apply || !ctx) return;

            let selectedFile = null;
            let image = null;
            let scale = 1;
            let x = 0;
            let y = 0;
            let dragging = false;
            let startX = 0;
            let startY = 0;
            let originX = 0;
            let originY = 0;
            let cropReady = false;

            const setStatus = (type, text) => {
                status.className = `class-photo-selection ${type || ''}`.trim();
                const icon = type === 'ready' ? 'circle-check' : type === 'pending' ? 'clock-3' : type === 'warning' ? 'triangle-alert' : 'circle-dashed';
                status.innerHTML = `<i data-lucide="${icon}"></i><span>${text}</span>`;
                window.lucide?.createIcons?.();
            };

            const lockForm = (locked) => {
                const submit = form.querySelector('button[type="submit"]');
                if (!submit) return;
                submit.disabled = locked;
                submit.classList.toggle('is-disabled', locked);
                submit.title = locked ? 'Confirme o recorte da imagem antes de salvar a turma.' : '';
            };

            const draw = () => {
                if (!image) return;
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                const cover = Math.max(canvas.width / image.width, canvas.height / image.height);
                const finalScale = cover * scale;
                const width = image.width * finalScale;
                const height = image.height * finalScale;
                x = Math.min(0, Math.max(canvas.width - width, x));
                y = Math.min(0, Math.max(canvas.height - height, y));
                ctx.drawImage(image, x, y, width, height);
            };

            const open = () => {
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('class-photo-modal-open');
                window.setTimeout(draw, 20);
            };

            const close = () => {
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('class-photo-modal-open');
            };

            input.addEventListener('change', () => {
                const file = input.files?.[0] || null;
                selectedFile = null;
                cropReady = false;
                output.value = '';

                if (!file) {
                    prepare.disabled = true;
                    lockForm(false);
                    setStatus('', 'Nenhuma nova imagem selecionada.');
                    return;
                }
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    alert('Selecione uma imagem JPG, PNG ou WebP.');
                    input.value = '';
                    prepare.disabled = true;
                    lockForm(false);
                    setStatus('warning', 'O arquivo escolhido não é uma imagem permitida.');
                    return;
                }
                if (file.size > 8 * 1024 * 1024) {
                    alert('A imagem deve ter no máximo 8 MB.');
                    input.value = '';
                    prepare.disabled = true;
                    lockForm(false);
                    setStatus('warning', 'A imagem ultrapassa o limite de 8 MB.');
                    return;
                }

                selectedFile = file;
                prepare.disabled = false;
                lockForm(true);
                if (remove) remove.checked = false;
                setStatus('pending', `${file.name} selecionada. Clique em “Enviar imagem” para ajustar o recorte 16:9.`);
            });

            prepare.addEventListener('click', () => {
                if (!selectedFile) return;
                const reader = new FileReader();
                prepare.disabled = true;
                prepare.classList.add('is-loading');
                setStatus('pending', 'Preparando a imagem para o editor…');

                reader.onerror = () => {
                    prepare.disabled = false;
                    prepare.classList.remove('is-loading');
                    setStatus('warning', 'Não foi possível abrir a imagem selecionada.');
                };
                reader.onload = () => {
                    image = new Image();
                    image.onerror = () => {
                        prepare.disabled = false;
                        prepare.classList.remove('is-loading');
                        setStatus('warning', 'O arquivo selecionado não pôde ser lido como imagem.');
                    };
                    image.onload = () => {
                        if (image.width < 900 || image.height < 506) {
                            setStatus('warning', `A imagem possui ${image.width} × ${image.height} px. Recomendamos pelo menos 900 × 506 px.`);
                        } else {
                            setStatus('pending', `Imagem carregada (${image.width} × ${image.height} px). Ajuste o enquadramento e confirme.`);
                        }
                        scale = 1;
                        zoom.value = '1';
                        const cover = Math.max(canvas.width / image.width, canvas.height / image.height);
                        x = (canvas.width - image.width * cover) / 2;
                        y = (canvas.height - image.height * cover) / 2;
                        draw();
                        prepare.disabled = false;
                        prepare.classList.remove('is-loading');
                        open();
                    };
                    image.src = String(reader.result || '');
                };
                reader.readAsDataURL(selectedFile);
            });

            zoom.addEventListener('input', () => {
                if (!image) return;
                const oldScale = scale;
                const rect = canvas.getBoundingClientRect();
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2;
                scale = Number(zoom.value);
                const ratio = scale / oldScale;
                x = centerX - (centerX - x) * ratio;
                y = centerY - (centerY - y) * ratio;
                draw();
            });

            canvas.addEventListener('pointerdown', (event) => {
                if (!image) return;
                dragging = true;
                startX = event.clientX;
                startY = event.clientY;
                originX = x;
                originY = y;
                canvas.setPointerCapture(event.pointerId);
                canvas.classList.add('is-dragging');
            });
            canvas.addEventListener('pointermove', (event) => {
                if (!dragging) return;
                const rect = canvas.getBoundingClientRect();
                x = originX + (event.clientX - startX) * (canvas.width / rect.width);
                y = originY + (event.clientY - startY) * (canvas.height / rect.height);
                draw();
            });
            const endDrag = () => {
                dragging = false;
                canvas.classList.remove('is-dragging');
            };
            canvas.addEventListener('pointerup', endDrag);
            canvas.addEventListener('pointercancel', endDrag);

            apply.addEventListener('click', () => {
                if (!image) return;
                const finalCanvas = document.createElement('canvas');
                finalCanvas.width = 1600;
                finalCanvas.height = 900;
                const finalContext = finalCanvas.getContext('2d');
                finalContext.drawImage(canvas, 0, 0, finalCanvas.width, finalCanvas.height);
                const data = finalCanvas.toDataURL('image/jpeg', 0.9);
                output.value = data;
                preview.style.backgroundImage = `url("${data}")`;
                preview.classList.add('has-image');
                cropReady = true;
                lockForm(false);
                if (remove) remove.checked = false;
                setStatus('ready', 'Imagem pronta em 1600 × 900 px. Agora você pode salvar a turma.');
                close();
            });

            field.querySelectorAll('[data-class-photo-close]').forEach((button) => button.addEventListener('click', close));
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.classList.contains('open')) close();
            });
            remove?.addEventListener('change', () => {
                if (!remove.checked) return;
                input.value = '';
                selectedFile = null;
                cropReady = false;
                output.value = '';
                prepare.disabled = true;
                lockForm(false);
                setStatus('warning', 'A foto atual será removida quando a turma for salva.');
            });

            form.addEventListener('submit', (event) => {
                if (selectedFile && !cropReady && output.value === '') {
                    event.preventDefault();
                    alert('Clique em “Enviar imagem”, ajuste o recorte e confirme antes de salvar a turma.');
                    prepare.focus();
                }
            });
        });
    };

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
