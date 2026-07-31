<?php
$notice=$notice??[]; $priorities=$priorities??[]; $targets=$targets??[]; $categories=$categories??[]; $classes=$classes??[]; $attachments=$attachments??[];
$formAction=$formAction??base_url('avisos'); $submitLabel=$submitLabel??'Salvar aviso'; $isEdit=(bool)($isEdit??false);
$dt=function($v){ if(!$v)return ''; $t=strtotime((string)$v); return $t?date('Y-m-d\TH:i',$t):''; };
?>
<form class="notice-studio" action="<?=e($formAction)?>" method="post" enctype="multipart/form-data" id="noticeStudioForm">
<?php if($isEdit): ?><input type="hidden" name="id" value="<?=(int)($notice['id']??0)?>"><?php endif; ?>
<input type="hidden" name="existing_banner_path" value="<?=e((string)($notice['banner_path']??''))?>">
<input type="hidden" name="banner_cropped_data" id="bannerCroppedData">
<div class="notice-studio-grid">
  <main class="notice-studio-main">
    <section class="notice-editor-card" id="noticeTextSection">
      <div class="notice-section-heading"><span><i data-lucide="file-text"></i></span><div><h2>Informações do aviso</h2><p>Escreva uma comunicação clara e objetiva.</p></div></div>
      <div class="form-group"><label for="title">Título</label><input id="title" name="title" maxlength="180" required value="<?=e((string)($notice['title']??''))?>" placeholder="Ex.: Reunião pedagógica nesta sexta-feira"></div>
      <div class="form-group"><label for="summary">Resumo para o banner</label><textarea id="summary" name="summary" rows="3" maxlength="320" placeholder="Uma frase curta que será exibida no carrossel."><?=e((string)($notice['summary']??''))?></textarea><small>Até 320 caracteres.</small></div>
      <div class="form-group"><label for="content">Conteúdo completo</label><textarea id="content" name="content" rows="10" required placeholder="Detalhe o aviso, orientações, horários e informações importantes."><?=e((string)($notice['content']??''))?></textarea></div>
    </section>

    <section class="notice-editor-card" id="noticeMediaSection">
      <div class="notice-tv-only-hint" id="noticeTvOnlyHint" hidden><i data-lucide="monitor-play"></i><div><strong>Aviso exclusivo do Painel-TV</strong><span>Para este público, envie somente a Imagem do cartão. Todas as informações devem estar inseridas no próprio banner.</span></div></div>
      <div class="notice-section-heading"><span><i data-lucide="image"></i></span><div><h2>Imagem do cartão</h2><p>Use uma imagem vertical na proporção <strong>4:5</strong>. Tamanho ideal: <strong>1080 × 1350 px</strong>.</p></div></div>
      <div class="notice-banner-upload">
        <div class="notice-banner-preview" id="noticeBannerPreview" style="<?=!empty($notice['banner_path'])?'background-image:url(\''.e(base_url((string)$notice['banner_path'])).'\')':''?>"><div><i data-lucide="image-plus"></i><strong>Imagem do aviso</strong><span>1080 × 1350 px • proporção 4:5</span></div></div>
        <div class="notice-banner-guidance">
          <i data-lucide="info"></i>
          <div><strong>Dimensões aceitas na proporção 4:5</strong><span>1080 × 1350 px, 800 × 1000 px ou 600 × 750 px. Imagens em outra proporção serão ajustadas no editor.</span></div>
        </div>
        <div class="notice-banner-actions">
          <label class="btn-secondary notice-file-button"><i data-lucide="image-plus"></i> Selecionar imagem<input type="file" id="bannerInput" accept="image/jpeg,image/png,image/webp" hidden></label>
          <button type="button" class="btn-primary" id="bannerPrepare" disabled><i data-lucide="upload"></i> Enviar imagem</button>
        </div>
        <div class="notice-banner-selection" id="bannerSelectionStatus" aria-live="polite">
          <i data-lucide="circle-dashed"></i><span>Nenhuma nova imagem selecionada.</span>
        </div>
      </div>
      <div class="form-group notice-non-tv-field"><label for="youtube_url">Vídeo do YouTube</label><input type="url" id="youtube_url" name="youtube_url" value="<?=e((string)($notice['youtube_url']??''))?>" placeholder="https://www.youtube.com/watch?v=..."><small id="youtubeDetectionHint">Ao reconhecer o vídeo, o sistema mostrará a miniatura no mural e incorporará o player nos detalhes.</small></div>
      <div class="form-group notice-non-tv-field"><label>Anexos</label><?php component('media/staged-file-upload', [
        'inputId' => 'noticeAttachments',
        'inputName' => 'attachments[]',
        'maxFiles' => 10,
        'maxSizeMb' => 25,
        'help' => 'PDF, imagens, documentos, planilhas, áudio, vídeo e ZIP. Até 10 arquivos, 25 MB cada.',
      ]); ?></div>
      <div class="notice-non-tv-field"><?php if($attachments): ?><?php component('media/attachment-carousel', ['attachments' => $attachments, 'title' => 'Imagens atuais do aviso']); ?><div class="notice-existing-files"><h3>Arquivos atuais</h3><?php foreach($attachments as $file): ?><div class="notice-file-row"><a href="<?=e(base_url((string)$file['relative_path']))?>" target="_blank"><i data-lucide="paperclip"></i><span><?=e((string)$file['original_name'])?></span></a><button type="submit" class="icon-button danger" formaction="<?=base_url('avisos/anexos/excluir')?>" formmethod="post" name="attachment_id" value="<?=(int)$file['id']?>" formnovalidate onclick="return confirm('Excluir este anexo?')"><i data-lucide="trash-2"></i></button></div><?php endforeach; ?></div><?php endif; ?></div>
    </section>
  </main>

  <aside class="notice-studio-sidebar">
    <section class="notice-editor-card sticky">
      <div class="notice-section-heading compact notice-non-tv-field"><span><i data-lucide="eye"></i></span><div><h2>Prévia do mural</h2><p>Visualize o cartão enquanto edita.</p></div></div>
      <div class="notice-live-preview notice-non-tv-field priority-<?=strtolower(e((string)($notice['priority']??'INFO')))?>" id="noticeLivePreview">
        <div class="notice-live-preview-image" id="noticeLivePreviewImage" style="<?= !empty($notice['banner_path']) ? 'background-image:url(&quot;' . e(base_url((string) $notice['banner_path'])) . '&quot;)' : '' ?>"></div>
        <div class="notice-live-preview-shade"></div>
        <div class="notice-live-preview-content"><span id="noticePreviewCategory"><?=e((string)($categories[strtoupper((string)($notice['category']??'GENERAL'))]??'Geral'))?></span><h3 id="noticePreviewTitle"><?=e((string)($notice['title']??'Título do aviso'))?></h3><p id="noticePreviewSummary"><?=e((string)($notice['summary']??'O resumo aparecerá aqui.'))?></p><button type="button" tabindex="-1" id="noticePreviewButton">Ver aviso <i data-lucide="arrow-right"></i></button></div>
      </div>
      <div class="notice-section-heading compact notice-publish-heading"><span><i data-lucide="send"></i></span><div><h2>Publicação</h2><p>Defina alcance e período.</p></div></div>
      <div class="form-group"><label for="category">Categoria</label><select id="category" name="category"><?php foreach($categories as $v=>$label): ?><option value="<?=e($v)?>" <?=strtoupper((string)($notice['category']??'GENERAL'))===$v?'selected':''?>><?=e($label)?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label for="priority">Prioridade</label><select id="priority" name="priority"><?php foreach($priorities as $v=>$label): ?><option value="<?=e($v)?>" <?=strtoupper((string)($notice['priority']??'INFO'))===$v?'selected':''?>><?=e($label)?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label for="target">Público-alvo</label><select id="target" name="target"><?php foreach($targets as $v=>$label): ?><option value="<?=e($v)?>" <?=strtoupper((string)($notice['target']??'ALL'))===$v?'selected':''?>><?=e($label)?></option><?php endforeach; ?></select></div>
      <div class="form-group" id="noticeTargetClassGroup"><label for="target_class_id">Turma</label><select id="target_class_id" name="target_class_id"><option value="">Selecione</option><?php foreach($classes as $class): ?><option value="<?=(int)$class['id']?>" <?=(int)($notice['target_class_id']??0)===(int)$class['id']?'selected':''?>><?=e((string)($class['name']??''))?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label for="published_at">Início da publicação</label><input type="datetime-local" id="published_at" name="published_at" value="<?=e($dt($notice['published_at']??''))?>"></div>
      <div class="form-group"><label for="expires_at">Expiração</label><input type="datetime-local" id="expires_at" name="expires_at" value="<?=e($dt($notice['expires_at']??''))?>"></div>
      <?php foreach([['active','circle-check','Aviso ativo','Exibir quando o período começar.'],['pinned','pin','Fixar no topo','Prioridade na listagem.'],['featured','sparkles','Aviso em destaque','Dar maior presença no carrossel.']] as [$name,$icon,$label,$hint]): ?><label class="notice-switch"><input type="checkbox" name="<?=$name?>" value="1" <?=!empty($notice[$name])?'checked':''?>><span class="notice-switch-control"></span><span><strong><i data-lucide="<?=$icon?>"></i><?=$label?></strong><small><?=$hint?></small></span></label><?php endforeach; ?>
      <div class="notice-form-actions"><a href="<?=base_url('avisos')?>" class="btn-secondary">Cancelar</a><button type="submit" class="btn-primary" id="noticeSubmitButton"><i data-lucide="save"></i><?=e($submitLabel)?></button></div>
    </section>
  </aside>
</div>
</form>
<style>.notice-tv-only-hint{display:flex;gap:.8rem;align-items:center;padding:1rem;border:1px solid color-mix(in srgb,var(--primary) 35%,var(--border-color));background:color-mix(in srgb,var(--primary) 8%,var(--surface-secondary));border-radius:14px;margin-bottom:1rem}.notice-tv-only-hint i{width:32px;height:32px;color:var(--primary)}.notice-tv-only-hint span{display:block;color:var(--text-secondary);margin-top:.2rem}.notice-studio.is-tv-panel #noticeTextSection,.notice-studio.is-tv-panel .notice-non-tv-field{display:none!important}.notice-studio.is-tv-panel #noticeMediaSection{grid-column:1/-1}.notice-studio.is-tv-panel .notice-banner-upload{max-width:760px;margin:auto}.notice-studio.is-tv-panel .notice-banner-preview{min-height:560px}</style>

<div class="notice-cropper" id="noticeCropper" aria-hidden="true">
 <div class="notice-cropper-backdrop"></div><div class="notice-cropper-dialog"><header><div><h2>Ajustar imagem do aviso</h2><p>Posicione a imagem dentro da área vertical 4:5. O resultado será salvo em 1080 × 1350 px.</p></div><button type="button" class="icon-button" id="cropClose"><i data-lucide="x"></i></button></header>
 <div class="notice-crop-stage"><div class="notice-crop-frame"><canvas id="cropCanvas" width="540" height="675"></canvas></div></div>
 <div class="notice-crop-controls"><label>Zoom <input type="range" id="cropZoom" min="1" max="3" value="1" step="0.01"></label><p>Arraste a imagem para reposicionar.</p></div>
 <footer><button type="button" class="btn-secondary" id="cropCancel">Voltar</button><button type="button" class="btn-primary" id="cropApply"><i data-lucide="check"></i> Confirmar recorte</button></footer>
 </div>
</div>
<script src="<?=base_url('assets/js/pages/notices-studio.js?v=3034')?>"></script>
