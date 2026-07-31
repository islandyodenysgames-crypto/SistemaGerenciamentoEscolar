document.addEventListener('DOMContentLoaded', () => {
    const target = document.getElementById('target');
    const group = document.getElementById('noticeTargetClassGroup');
    const classSelect = document.getElementById('target_class_id');
    const syncTarget = () => {
        const show = target?.value === 'CLASS';
        const isTv = target?.value === 'TV_PANEL';
        if (group) group.hidden = !show;
        if (classSelect) {
            classSelect.required = show;
            if (!show) classSelect.value = '';
        }
        const studio = document.getElementById('noticeStudioForm');
        studio?.classList.toggle('is-tv-panel', isTv);
        const hint = document.getElementById('noticeTvOnlyHint');
        if (hint) hint.hidden = !isTv;
        ['title','content'].forEach((id) => {
            const field = document.getElementById(id);
            if (field) field.required = !isTv;
        });
        const bannerInput = document.getElementById('bannerCroppedData');
        if (isTv && document.getElementById('title')?.value.trim() === '') document.getElementById('title').value = 'Banner do Painel TV';
        if (isTv && document.getElementById('content')?.value.trim() === '') document.getElementById('content').value = 'Conteúdo apresentado integralmente na imagem do cartão.';
    };
    target?.addEventListener('change', syncTarget);
    syncTarget();

    const form = document.getElementById('noticeStudioForm');
    const input = document.getElementById('bannerInput');
    const prepareButton = document.getElementById('bannerPrepare');
    const submitButton = document.getElementById('noticeSubmitButton');
    const status = document.getElementById('bannerSelectionStatus');
    const modal = document.getElementById('noticeCropper');
    const canvas = document.getElementById('cropCanvas');
    const ctx = canvas?.getContext('2d');
    const zoom = document.getElementById('cropZoom');
    const hidden = document.getElementById('bannerCroppedData');
    const preview = document.getElementById('noticeBannerPreview');
    const applyButton = document.getElementById('cropApply');

    if (!form || !input || !prepareButton || !modal || !canvas || !ctx || !zoom || !hidden || !preview || !applyButton) return;

    let selectedFile = null;
    let img = null;
    let scale = 1;
    let x = 0;
    let y = 0;
    let drag = false;
    let startX = 0;
    let startY = 0;
    let originX = 0;
    let originY = 0;
    let cropReady = false;

    const setStatus = (type, text) => {
        if (!status) return;
        status.className = `notice-banner-selection ${type || ''}`.trim();
        const icon = type === 'ready' ? 'circle-check' : type === 'pending' ? 'clock-3' : type === 'warning' ? 'triangle-alert' : 'circle-dashed';
        status.innerHTML = `<i data-lucide="${icon}"></i><span>${text}</span>`;
        window.lucide?.createIcons?.();
    };

    const lockSubmit = (locked) => {
        if (!submitButton) return;
        submitButton.disabled = locked;
        submitButton.classList.toggle('is-disabled', locked);
        submitButton.title = locked ? 'Confirme o recorte da imagem antes de salvar.' : '';
    };

    const draw = () => {
        if (!img) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const cover = Math.max(canvas.width / img.width, canvas.height / img.height);
        const finalScale = cover * scale;
        const width = img.width * finalScale;
        const height = img.height * finalScale;
        x = Math.min(0, Math.max(canvas.width - width, x));
        y = Math.min(0, Math.max(canvas.height - height, y));
        ctx.drawImage(img, x, y, width, height);
    };

    const open = () => {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('notice-modal-open');
        window.setTimeout(() => draw(), 20);
    };

    const close = () => {
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('notice-modal-open');
    };

    input.addEventListener('change', () => {
        const file = input.files?.[0] || null;
        selectedFile = null;
        cropReady = false;
        hidden.value = '';

        if (!file) {
            prepareButton.disabled = true;
            lockSubmit(false);
            setStatus('', 'Nenhuma nova imagem selecionada.');
            return;
        }

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            alert('Selecione uma imagem JPG, PNG ou WebP.');
            input.value = '';
            prepareButton.disabled = true;
            lockSubmit(false);
            setStatus('warning', 'O arquivo escolhido não é uma imagem permitida.');
            return;
        }

        if (file.size > 8 * 1024 * 1024) {
            alert('A imagem deve ter no máximo 8 MB.');
            input.value = '';
            prepareButton.disabled = true;
            lockSubmit(false);
            setStatus('warning', 'A imagem ultrapassa o limite de 8 MB.');
            return;
        }

        selectedFile = file;
        prepareButton.disabled = false;
        lockSubmit(true);
        setStatus('pending', `${file.name} selecionada. Clique em “Enviar imagem” para ajustar o corte 4:5.`);
    });

    prepareButton.addEventListener('click', () => {
        if (!selectedFile) return;

        const reader = new FileReader();
        prepareButton.disabled = true;
        prepareButton.classList.add('is-loading');
        setStatus('pending', 'Preparando a imagem para o editor…');

        reader.onerror = () => {
            prepareButton.disabled = false;
            prepareButton.classList.remove('is-loading');
            setStatus('warning', 'Não foi possível abrir a imagem selecionada.');
        };

        reader.onload = () => {
            img = new Image();
            img.onerror = () => {
                prepareButton.disabled = false;
                prepareButton.classList.remove('is-loading');
                setStatus('warning', 'O arquivo selecionado não pôde ser lido como imagem.');
            };
            img.onload = () => {
                if (img.width < 600 || img.height < 750) {
                    setStatus('warning', `A imagem possui ${img.width} × ${img.height} px. Ela pode perder qualidade; recomendamos pelo menos 600 × 750 px.`);
                } else {
                    setStatus('pending', `Imagem carregada (${img.width} × ${img.height} px). Ajuste o enquadramento e confirme.`);
                }
                scale = 1;
                zoom.value = '1';
                x = 0;
                y = 0;
                draw();
                prepareButton.disabled = false;
                prepareButton.classList.remove('is-loading');
                open();
            };
            img.src = String(reader.result || '');
        };
        reader.readAsDataURL(selectedFile);
    });

    zoom.addEventListener('input', () => {
        scale = Number(zoom.value);
        draw();
    });

    canvas.addEventListener('pointerdown', (event) => {
        drag = true;
        startX = event.clientX;
        startY = event.clientY;
        originX = x;
        originY = y;
        canvas.setPointerCapture(event.pointerId);
        canvas.classList.add('is-dragging');
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!drag) return;
        const rect = canvas.getBoundingClientRect();
        const ratioX = canvas.width / rect.width;
        const ratioY = canvas.height / rect.height;
        x = originX + (event.clientX - startX) * ratioX;
        y = originY + (event.clientY - startY) * ratioY;
        draw();
    });
    const endDrag = () => {
        drag = false;
        canvas.classList.remove('is-dragging');
    };
    canvas.addEventListener('pointerup', endDrag);
    canvas.addEventListener('pointercancel', endDrag);

    applyButton.addEventListener('click', () => {
        if (!img) return;
        const output = document.createElement('canvas');
        output.width = 1080;
        output.height = 1350;
        const outputContext = output.getContext('2d');
        outputContext.drawImage(canvas, 0, 0, output.width, output.height);
        const data = output.toDataURL('image/jpeg', 0.9);
        hidden.value = data;
        preview.style.backgroundImage = `url(${data})`;
        preview.classList.add('has-image');
        cropReady = true;
        lockSubmit(false);
        setStatus('ready', 'Imagem pronta em 1080 × 1350 px. Agora você pode salvar o aviso.');
        close();
    });

    ['cropClose', 'cropCancel'].forEach((id) => document.getElementById(id)?.addEventListener('click', close));
    modal.querySelector('.notice-cropper-backdrop')?.addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('open')) close();
    });

    form.addEventListener('submit', (event) => {
        const isTv = target?.value === 'TV_PANEL';
        const hasExistingBanner = String(form.querySelector('[name=existing_banner_path]')?.value || '').trim() !== '';
        if (isTv && hidden.value === '' && !hasExistingBanner) {
            event.preventDefault();
            alert('Para o Painel-TV, envie obrigatoriamente a Imagem do cartão.');
            input.focus();
            return;
        }
        if (selectedFile && !cropReady && hidden.value === '') {
            event.preventDefault();
            alert('Clique em “Enviar imagem”, ajuste o recorte e confirme antes de salvar o aviso.');
            prepareButton.focus();
        }
    });
});

// Sprint 3.0.3.2 - prévia dinâmica e detecção do YouTube.
document.addEventListener('DOMContentLoaded', () => {
    const title = document.getElementById('title');
    const summary = document.getElementById('summary');
    const category = document.getElementById('category');
    const priority = document.getElementById('priority');
    const youtube = document.getElementById('youtube_url');
    const preview = document.getElementById('noticeLivePreview');
    const previewImage = document.getElementById('noticeLivePreviewImage');
    const previewTitle = document.getElementById('noticePreviewTitle');
    const previewSummary = document.getElementById('noticePreviewSummary');
    const previewCategory = document.getElementById('noticePreviewCategory');
    const previewButton = document.getElementById('noticePreviewButton');
    const hint = document.getElementById('youtubeDetectionHint');
    const cropped = document.getElementById('bannerCroppedData');

    if (!preview) return;

    const youtubeId = (value) => {
        const match = String(value || '').match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/))([A-Za-z0-9_-]{6,})/i);
        return match ? match[1] : '';
    };

    const syncText = () => {
        if (previewTitle) previewTitle.textContent = title?.value.trim() || 'Título do aviso';
        if (previewSummary) previewSummary.textContent = summary?.value.trim() || 'O resumo aparecerá aqui.';
        if (previewCategory) previewCategory.textContent = category?.selectedOptions?.[0]?.textContent || 'Geral';
        preview.className = `notice-live-preview priority-${String(priority?.value || 'INFO').toLowerCase()}`;
    };

    const syncYoutube = () => {
        const id = youtubeId(youtube?.value);
        if (id) {
            if (previewImage && !cropped?.value) previewImage.style.backgroundImage = `url("https://img.youtube.com/vi/${id}/hqdefault.jpg")`;
            if (previewButton) previewButton.innerHTML = 'Assistir vídeo <i data-lucide="play"></i>';
            if (hint) {
                hint.textContent = 'Vídeo reconhecido. A miniatura será usada como imagem do cartão quando não houver banner personalizado, e o player será incorporado nos detalhes.';
                hint.classList.add('is-detected');
            }
        } else {
            if (previewButton) previewButton.innerHTML = 'Ver aviso <i data-lucide="arrow-right"></i>';
            if (hint) {
                hint.textContent = 'Ao reconhecer o vídeo, o sistema mostrará a miniatura no mural e incorporará o player nos detalhes.';
                hint.classList.remove('is-detected');
            }
        }
        window.lucide?.createIcons?.();
    };

    [title, summary].forEach((field) => field?.addEventListener('input', syncText));
    [category, priority].forEach((field) => field?.addEventListener('change', syncText));
    youtube?.addEventListener('input', syncYoutube);

    const apply = document.getElementById('cropApply');
    apply?.addEventListener('click', () => {
        window.setTimeout(() => {
            if (cropped?.value && previewImage) previewImage.style.backgroundImage = `url("${cropped.value}")`;
        }, 30);
    });

    syncText();
    syncYoutube();
});
