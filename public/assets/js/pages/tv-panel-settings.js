document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('dailyTipBannerInput');
  const prepare = document.getElementById('dailyTipBannerPrepare');
  const hidden = document.getElementById('dailyTipBannerCroppedData');
  const preview = document.getElementById('dailyTipBannerPreview');
  const status = document.getElementById('dailyTipBannerStatus');
  const modal = document.getElementById('dailyTipCropper');
  const canvas = document.getElementById('dailyTipCropCanvas');
  const zoom = document.getElementById('dailyTipCropZoom');
  const apply = document.getElementById('dailyTipCropApply');
  const form = input?.closest('form');
  if (!input || !prepare || !hidden || !preview || !status || !modal || !canvas || !zoom || !apply || !form) return;

  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  let sourceFile = null;
  let image = null;
  let scale = 1;
  let offsetX = 0;
  let offsetY = 0;
  let dragging = false;
  let startX = 0;
  let startY = 0;
  let originX = 0;
  let originY = 0;
  let cropReady = false;
  let previewUrl = null;

  const setStatus = (type, text) => {
    status.className = `tv-tip-banner-status${type ? ` is-${type}` : ''}`;
    const icon = type === 'ready' ? 'circle-check' : type === 'warning' ? 'triangle-alert' : 'circle-dashed';
    status.innerHTML = `<i data-lucide="${icon}"></i><span>${text}</span>`;
    window.lucide?.createIcons?.();
  };

  const draw = () => {
    if (!image) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    const cover = Math.max(canvas.width / image.width, canvas.height / image.height);
    const finalScale = cover * scale;
    const width = image.width * finalScale;
    const height = image.height * finalScale;
    offsetX = Math.min(0, Math.max(canvas.width - width, offsetX));
    offsetY = Math.min(0, Math.max(canvas.height - height, offsetY));
    ctx.drawImage(image, offsetX, offsetY, width, height);
  };

  const open = () => {
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('tv-tip-modal-open');
    window.setTimeout(draw, 20);
  };

  const close = () => {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('tv-tip-modal-open');
  };

  input.addEventListener('change', () => {
    sourceFile = input.files?.[0] || null;
    hidden.value = '';
    cropReady = false;

    if (!sourceFile) {
      prepare.disabled = true;
      setStatus('', 'Nenhuma nova imagem selecionada.');
      return;
    }

    if (!['image/jpeg', 'image/png', 'image/webp'].includes(sourceFile.type) || sourceFile.size > 8 * 1024 * 1024) {
      alert('Selecione uma imagem JPG, PNG ou WebP de até 8 MB.');
      input.value = '';
      sourceFile = null;
      prepare.disabled = true;
      setStatus('warning', 'Arquivo inválido.');
      return;
    }

    prepare.disabled = false;
    setStatus('', `${sourceFile.name} selecionada. Clique em “Enviar imagem” para ajustar o recorte 4:3.`);
  });

  prepare.addEventListener('click', () => {
    if (!sourceFile) return;
    const reader = new FileReader();
    prepare.disabled = true;
    reader.onload = () => {
      image = new Image();
      image.onload = () => {
        scale = 1;
        zoom.value = '1';
        offsetX = 0;
        offsetY = 0;
        draw();
        prepare.disabled = false;
        setStatus('', `Imagem carregada (${image.width} × ${image.height} px). Ajuste e confirme.`);
        open();
      };
      image.onerror = () => {
        prepare.disabled = false;
        setStatus('warning', 'Não foi possível ler a imagem.');
      };
      image.src = String(reader.result || '');
    };
    reader.onerror = () => {
      prepare.disabled = false;
      setStatus('warning', 'Não foi possível abrir a imagem.');
    };
    reader.readAsDataURL(sourceFile);
  });

  zoom.addEventListener('input', () => {
    scale = Number(zoom.value);
    draw();
  });

  canvas.addEventListener('pointerdown', (event) => {
    dragging = true;
    startX = event.clientX;
    startY = event.clientY;
    originX = offsetX;
    originY = offsetY;
    canvas.setPointerCapture(event.pointerId);
    canvas.classList.add('is-dragging');
  });

  canvas.addEventListener('pointermove', (event) => {
    if (!dragging) return;
    const rect = canvas.getBoundingClientRect();
    const ratioX = canvas.width / rect.width;
    const ratioY = canvas.height / rect.height;
    offsetX = originX + (event.clientX - startX) * ratioX;
    offsetY = originY + (event.clientY - startY) * ratioY;
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
    const output = document.createElement('canvas');
    output.width = 1200;
    output.height = 900;
    const outputContext = output.getContext('2d');
    if (!outputContext) return;
    outputContext.drawImage(canvas, 0, 0, output.width, output.height);

    output.toBlob((blob) => {
      if (!blob) {
        setStatus('warning', 'Não foi possível gerar o banner recortado.');
        return;
      }

      const croppedFile = new File([blob], 'dica-do-dia-1200x900.jpg', { type: 'image/jpeg', lastModified: Date.now() });
      try {
        const transfer = new DataTransfer();
        transfer.items.add(croppedFile);
        input.files = transfer.files;
      } catch (error) {
        console.warn('O navegador não permitiu substituir o arquivo do input; será usado o recorte em Data URL.', error);
      }
      sourceFile = croppedFile;
      // Envia o recorte por duas rotas: arquivo e Data URL.
      // Alguns navegadores locais bloqueiam silenciosamente a substituição de input.files.
      hidden.value = output.toDataURL('image/jpeg', 0.82);
      cropReady = true;

      if (previewUrl) URL.revokeObjectURL(previewUrl);
      previewUrl = URL.createObjectURL(blob);
      preview.style.backgroundImage = `url("${previewUrl}")`;
      preview.classList.add('has-image');
      setStatus('ready', 'Banner pronto em 1200 × 900 px. Clique em “Salvar configurações” para concluir.');
      close();
    }, 'image/jpeg', 0.86);
  });

  form.addEventListener('submit', (event) => {
    if (sourceFile && !cropReady) {
      event.preventDefault();
      alert('Clique em “Enviar imagem”, ajuste o recorte e confirme antes de salvar.');
      prepare.focus();
    }
  });

  ['dailyTipCropClose', 'dailyTipCropCancel'].forEach((id) => document.getElementById(id)?.addEventListener('click', close));
  modal.querySelector('.tv-tip-cropper-backdrop')?.addEventListener('click', close);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modal.classList.contains('open')) close();
  });
});
