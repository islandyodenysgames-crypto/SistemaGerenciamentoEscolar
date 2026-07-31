<?php
$photoPath = trim((string)($photoPath ?? ''));
$inputId = (string)($inputId ?? 'class-photo');
?>
<div class="form-group class-photo-field" data-class-photo-field>
    <label>Foto da turma</label>
    <input type="hidden" name="class_photo_cropped_data" data-class-photo-output>

    <div class="class-photo-uploader">
        <div class="class-photo-preview<?= $photoPath !== '' ? ' has-image' : '' ?>" data-class-photo-preview
             <?php if ($photoPath !== ''): ?>style="background-image:url('<?= asset($photoPath) ?><?= !empty($photoUpdatedAt) ? '?v=' . urlencode((string)$photoUpdatedAt) : '' ?>')"<?php endif; ?>>
            <div class="class-photo-placeholder">
                <i data-lucide="image"></i>
                <strong>Nenhuma foto cadastrada</strong>
                <span>A imagem será exibida somente quando esta turma vencer o ranking diário do Painel TV.</span>
            </div>
        </div>

        <div class="class-photo-actions">
            <div class="class-photo-buttons">
                <label class="btn-secondary class-photo-file-button" for="<?= e($inputId) ?>">
                    <i data-lucide="image-plus"></i>
                    Selecionar imagem
                </label>
                <input id="<?= e($inputId) ?>" type="file" accept="image/jpeg,image/png,image/webp" data-class-photo-input hidden>

                <button type="button" class="btn-primary" data-class-photo-prepare disabled>
                    <i data-lucide="upload"></i>
                    Enviar imagem
                </button>
            </div>

            <div class="class-photo-selection" data-class-photo-status>
                <i data-lucide="circle-dashed"></i>
                <span><?= $photoPath !== '' ? 'Foto atual mantida. Selecione outra imagem para substituir.' : 'Nenhuma nova imagem selecionada.' ?></span>
            </div>

            <?php if ($photoPath !== ''): ?>
                <label class="class-photo-remove">
                    <input type="checkbox" name="remove_class_photo" value="1" data-class-photo-remove>
                    Remover foto atual ao salvar
                </label>
            <?php endif; ?>
        </div>
    </div>

    <div class="class-photo-guidance">
        <i data-lucide="monitor-up"></i>
        <div>
            <strong>Enquadramento para o Painel TV: 16:9</strong>
            <span>O recorte final será gerado em 1600 × 900 px e corresponderá exatamente ao card “Destaque do Dia”. Formatos: JPG, PNG ou WebP, até 8 MB.</span>
        </div>
    </div>

    <div class="class-photo-cropper" data-class-photo-modal aria-hidden="true">
        <div class="class-photo-cropper-backdrop" data-class-photo-close></div>
        <div class="class-photo-cropper-dialog" role="dialog" aria-modal="true" aria-label="Ajustar foto da turma">
            <header>
                <div>
                    <h2>Ajustar foto da turma</h2>
                    <p>Posicione a imagem na área horizontal 16:9. O resultado será salvo em 1600 × 900 px.</p>
                </div>
                <button type="button" class="icon-button" data-class-photo-close aria-label="Fechar editor"><i data-lucide="x"></i></button>
            </header>

            <div class="class-photo-crop-stage">
                <div class="class-photo-crop-frame" aria-label="Área final do recorte 16 por 9">
                    <canvas width="800" height="450" data-class-photo-canvas></canvas>
                    <div class="class-photo-crop-grid" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
                    <div class="class-photo-crop-label">ÁREA FINAL • 1600 × 900 px</div>
                </div>
            </div>

            <div class="class-photo-crop-controls">
                <label>Zoom <input type="range" min="1" max="3" value="1" step="0.01" data-class-photo-zoom></label>
                <p>Arraste a imagem para reposicionar.</p>
            </div>

            <footer>
                <button type="button" class="btn-secondary" data-class-photo-close>Voltar</button>
                <button type="button" class="btn-primary" data-class-photo-apply><i data-lucide="check"></i> Confirmar recorte</button>
            </footer>
        </div>
    </div>
</div>
<script src="<?= asset('assets/js/components/class-photo-cropper.js?v=171') ?>"></script>
