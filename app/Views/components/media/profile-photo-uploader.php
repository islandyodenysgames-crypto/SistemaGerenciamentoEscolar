<?php
$prefix = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($prefix ?? 'profilePhoto')) ?: 'profilePhoto';
$currentPath = trim((string)($currentPath ?? ''));
$title = (string)($title ?? 'Foto');
?>
<section class="profile-photo-uploader" data-profile-photo-uploader data-prefix="<?= e($prefix) ?>">
    <div class="profile-photo-heading">
        <div><h3><?= e($title) ?></h3><p>Proporção recomendada: <strong>1:1</strong>. Tamanho ideal: <strong>800 × 800 px</strong>. JPG, PNG ou WEBP, até 10 MB.</p></div>
    </div>
    <input type="hidden" name="photo_cropped_data" data-photo-output>
    <input type="hidden" name="remove_photo" value="0" data-photo-remove>
    <div class="profile-photo-preview <?= $currentPath !== '' ? 'has-image' : '' ?>" data-photo-preview <?= $currentPath !== '' ? 'style="background-image:url(\'' . e(base_url($currentPath)) . '\')"' : '' ?>>
        <div><i data-lucide="user-round"></i><span>Sem foto</span></div>
    </div>
    <input type="file" accept="image/jpeg,image/png,image/webp" hidden data-photo-input>
    <div class="profile-photo-actions">
        <button type="button" class="btn-secondary" data-photo-select><i data-lucide="image-plus"></i> Selecionar imagem</button>
        <button type="button" class="btn-primary" data-photo-send disabled><i data-lucide="upload"></i> Enviar imagem</button>
        <?php if ($currentPath !== ''): ?><button type="button" class="btn-danger" data-photo-delete><i data-lucide="trash-2"></i> Remover foto</button><?php endif; ?>
    </div>
    <div class="profile-photo-selection" data-photo-status>Nenhuma nova imagem selecionada.</div>
    <div class="profile-photo-cropper" data-photo-modal aria-hidden="true">
        <div class="profile-photo-backdrop" data-photo-close></div>
        <div class="profile-photo-dialog">
            <header><div><h3>Ajustar foto</h3><p>Arraste e amplie a imagem dentro da área quadrada.</p></div><button type="button" class="icon-button" data-photo-close><i data-lucide="x"></i></button></header>
            <div class="profile-photo-stage"><div class="profile-photo-frame"><canvas width="700" height="700" data-photo-canvas></canvas></div></div>
            <div class="profile-photo-controls"><label>Zoom <input type="range" min="1" max="3" step="0.01" value="1" data-photo-zoom></label></div>
            <footer><button type="button" class="btn-secondary" data-photo-close>Voltar</button><button type="button" class="btn-primary" data-photo-apply><i data-lucide="check"></i> Confirmar recorte</button></footer>
        </div>
    </div>
</section>
