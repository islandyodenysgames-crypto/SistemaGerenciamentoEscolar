<?php
component('base/page-header', [
    'title' => 'Painel TV',
    'subtitle' => 'Configure a apresentação institucional exibida em televisores e monitores.',
]);
$enabled = (array)($tvConfig['slides'] ?? []);
?>
<?php if (!empty($success)): ?><div class="settings-feedback settings-feedback--success"><i data-lucide="circle-check"></i><span><?= e((string)$success) ?></span></div><?php endif; ?>

<section class="card tv-settings-intro">
    <div>
        <h2>Painel institucional para TV</h2>
        <p>Escolha as telas, o tempo, os textos institucionais e a aparência da apresentação automática.</p>
    </div>
    <a class="btn-primary" href="<?= base_url('painel-tv') ?>" target="_blank"><i data-lucide="monitor-play"></i> Abrir Painel TV</a>
</section>

<form method="post" action="<?= base_url('painel-tv/configuracoes') ?>" class="tv-settings-form" enctype="multipart/form-data">
    <section class="card tv-settings-card">
        <div class="tv-settings-heading"><div><span>1</span><h3>Telas da apresentação</h3></div><p>Marque os murais que participarão da rotação automática.</p></div>
        <div class="tv-settings-slides">
            <?php foreach (['ranking'=>'Ranking diário','frequency'=>'Resumo de frequência','notices'=>'Avisos e comunicados','highlights'=>'Nossa escola em destaque','hallOfFame'=>'Hall da Fama','calendar'=>'Calendário escolar','indicators'=>'Indicadores gerais','automaticMessages'=>'Mensagens automáticas','didYouKnow'=>'Sabia que...','studentRecognition'=>'Estrela da semana','motivation'=>'Painel motivacional'] as $key => $label): ?>
                <label class="tv-slide-option"><input type="checkbox" name="slides[]" value="<?= e($key) ?>" <?= in_array($key, $enabled, true) ? 'checked' : '' ?>><span><strong><?= e($label) ?></strong><small>Exibir na rotação automática</small></span></label>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="card tv-settings-card">
        <div class="tv-settings-heading"><div><span>2</span><h3>Exibição</h3></div><p>Defina o ritmo e a aparência do painel.</p></div>
        <div class="tv-settings-grid">
            <label>Tempo por tela<select name="duration"><option value="5" <?= (int)($tvConfig['duration'] ?? 15) === 5 ? 'selected' : '' ?>>5 segundos</option><?php foreach ([10,15,20,30,45,60] as $v): ?><option value="<?= $v ?>" <?= (int)($tvConfig['duration'] ?? 15) === $v ? 'selected' : '' ?>><?= $v ?> segundos</option><?php endforeach; ?></select></label>
            <label>Transição<select name="transition"><?php foreach (['fade'=>'Suave (fade)','slide'=>'Deslizamento','zoom'=>'Zoom leve'] as $v => $l): ?><option value="<?= $v ?>" <?= ($tvConfig['transition'] ?? 'fade') === $v ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
            <label>Modo de exibição<select name="theme"><?php foreach (['light'=>'Claro','dark'=>'Escuro','auto'=>'Automático'] as $v => $l): ?><option value="<?= $v ?>" <?= ($tvConfig['theme'] ?? 'light') === $v ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
            <label>Tema institucional<select name="color_theme"><?php foreach (['green'=>'Verde institucional','blue'=>'Azul','purple'=>'Roxo','red'=>'Vermelho','automatic'=>'Automático'] as $v => $l): ?><option value="<?= $v ?>" <?= ($tvConfig['colorTheme'] ?? 'green') === $v ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
            <label>Duração das animações<select name="animation_duration"><?php foreach ([400=>'Rápida (0,4 s)',800=>'Suave (0,8 s)',1200=>'Elegante (1,2 s)',1600=>'Lenta (1,6 s)'] as $v => $l): ?><option value="<?= $v ?>" <?= (int)($tvConfig['animationDuration'] ?? 800) === $v ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
            <label>Atualização automática<select name="auto_refresh"><?php foreach ([30,60,120,300,600] as $v): ?><option value="<?= $v ?>" <?= (int)($tvConfig['autoRefresh'] ?? 60) === $v ? 'selected' : '' ?>><?= $v < 60 ? $v . ' segundos' : ($v / 60) . ' minuto(s)' ?></option><?php endforeach; ?></select></label>
        </div>
        <label class="tv-check"><input type="checkbox" name="animations_enabled" value="1" <?= !array_key_exists('animationsEnabled', $tvConfig) || !empty($tvConfig['animationsEnabled']) ? 'checked' : '' ?>> Ativar animações e transições premium</label>
        <label class="tv-check"><input type="checkbox" name="show_clock" value="1" <?= !empty($tvConfig['showClock']) ? 'checked' : '' ?>> Exibir relógio e data</label>
    </section>

    <section class="card tv-settings-card tv-intelligent-settings">
        <div class="tv-settings-heading"><div><span>3</span><h3>Conteúdo inteligente</h3></div><p>Atualize diariamente as frases com base nos dados reais da escola.</p></div>
        <label class="tv-check tv-check-master"><input type="checkbox" name="intelligent_content_enabled" value="1" <?= !empty($tvConfig['intelligentContentEnabled']) ? 'checked' : '' ?>> <strong>Ativar Motor de Conteúdo Inteligente</strong></label>
        <div class="tv-settings-grid">
            <label>Estilo das mensagens<select name="content_style"><?php foreach (['institutional'=>'Institucional','formal'=>'Formal','inspiring'=>'Inspirador','young'=>'Jovem'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($tvConfig['contentStyle'] ?? 'institutional') === $v ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
            <label>Frequência de renovação<select name="content_frequency"><?php foreach (['daily'=>'Diariamente','weekly'=>'Semanalmente','manual'=>'Somente quando solicitado'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($tvConfig['contentFrequency'] ?? 'daily') === $v ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
            <label>Publicação<select name="content_approval_mode"><?php foreach (['automatic'=>'Publicar automaticamente','review'=>'Revisar antes de publicar'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($tvConfig['contentApprovalMode'] ?? 'automatic') === $v ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        </div>
        <div class="tv-auto-grid">
            <?php foreach ([
                'auto_institutional_slogan'=>'Slogan institucional',
                'auto_daily_tip'=>'Dica do dia',
                'auto_support_text'=>'Texto de apoio',
                'auto_motivation'=>'Painel motivacional',
                'auto_footer_slogan'=>'Frase do rodapé',
                'auto_highlight_message'=>'Mensagem do destaque do dia',
                'auto_automatic_messages'=>'Mensagens automáticas',
                'auto_did_you_know'=>'Painel Sabia que...',
                'auto_hall_of_fame'=>'Mensagem do Hall da Fama',
                'auto_calendar_message'=>'Mensagem do calendário',
            ] as $name=>$label): $key = lcfirst(str_replace(' ', '', ucwords(str_replace('_',' ', $name)))); ?>
                <label class="tv-auto-option"><input type="checkbox" name="<?= e($name) ?>" value="1" <?= !empty($tvConfig[$key]) ? 'checked' : '' ?>><span><strong><?= e($label) ?></strong><small>Gerar automaticamente todos os dias</small></span></label>
            <?php endforeach; ?>
        </div>
        <p class="tv-intelligent-note"><i data-lucide="sparkles"></i> Quando uma opção automática estiver marcada, o texto manual continuará salvo, mas será usado apenas se a geração automática for desativada.</p>
        <div class="tv-intelligent-links"><a class="btn-secondary" href="<?= base_url('configuracoes/conteudo-inteligente') ?>"><i data-lucide="history"></i> Histórico e sugestões</a></div>
    </section>

    <section class="card tv-settings-card">
        <div class="tv-settings-heading"><div><span>4</span><h3>Textos institucionais</h3></div><p>Textos manuais de reserva para os murais de destaque e motivação.</p></div>
        <div class="tv-settings-grid tv-settings-grid-texts">
            <label class="tv-field-wide">Slogan institucional<input type="text" name="institutional_slogan" maxlength="120" value="<?= e((string)($tvConfig['institutionalSlogan'] ?? 'Educação, presença e futuro.')) ?>"></label>
            <label>Título da dica do dia<input type="text" name="daily_tip_title" maxlength="80" value="<?= e((string)($tvConfig['dailyTipTitle'] ?? 'Dica do dia')) ?>"></label>
            <label class="tv-field-wide">Texto da dica do dia<textarea name="daily_tip_text" maxlength="220" rows="3"><?= e((string)($tvConfig['dailyTipText'] ?? 'Pequenas atitudes constroem grandes resultados.')) ?></textarea></label>
            <label>Título do painel inferior<input type="text" name="support_title" maxlength="80" value="<?= e((string)($tvConfig['supportTitle'] ?? 'Contamos com você!')) ?>"></label>
            <label class="tv-field-wide">Mensagem do painel inferior<textarea name="support_text" maxlength="220" rows="3"><?= e((string)($tvConfig['supportText'] ?? $tvConfig['dailyTipFooter'] ?? 'Sua presença transforma o hoje e constrói o amanhã.')) ?></textarea></label>
            <div class="tv-field-wide tv-tip-banner-field" data-tip-banner-uploader>
                <div><strong>Banner da Dica do Dia</strong><small>Opcional. Quando enviado, substitui título, texto e ilustração. O editor gera a arte no tamanho exato de <b>1200 × 900 px</b> (proporção 4:3).</small></div>
                <input type="hidden" name="daily_tip_banner_cropped_data" id="dailyTipBannerCroppedData">
                <div class="tv-tip-banner-preview <?= !empty($tvConfig['dailyTipBanner']) ? 'has-image' : '' ?>" id="dailyTipBannerPreview" style="<?= !empty($tvConfig['dailyTipBanner']) ? 'background-image:url(\'' . e(base_url((string)$tvConfig['dailyTipBanner'])) . '\')' : '' ?>">
                    <div><i data-lucide="image-plus"></i><strong>Banner da Dica do Dia</strong><span>1200 × 900 px • proporção 4:3</span></div>
                </div>
                <div class="tv-tip-banner-actions">
                    <label class="btn-secondary tv-tip-file-button"><i data-lucide="image-plus"></i> Selecionar imagem<input type="file" id="dailyTipBannerInput" name="daily_tip_banner" accept="image/jpeg,image/png,image/webp" hidden></label>
                    <button type="button" class="btn-primary" id="dailyTipBannerPrepare" disabled><i data-lucide="upload"></i> Enviar imagem</button>
                </div>
                <div class="tv-tip-banner-status" id="dailyTipBannerStatus" aria-live="polite"><i data-lucide="circle-dashed"></i><span>Nenhuma nova imagem selecionada.</span></div>
                <?php if (!empty($tvConfig['dailyTipBanner'])): ?>
                    <input type="hidden" name="remove_daily_tip_banner" id="dailyTipBannerRemoveValue" value="0">
                    <button type="button" class="btn-secondary tv-remove-banner" id="dailyTipBannerRemove">
                        <i data-lucide="trash-2"></i><span>Excluir banner</span>
                    </button>
                <?php endif; ?>
            </div>
            <label class="tv-field-wide">Frase principal do painel motivacional<textarea name="motivation_text" maxlength="240" rows="3"><?= e((string)($tvConfig['motivationText'] ?? 'Cada presença representa uma nova oportunidade de aprender.')) ?></textarea></label>
            <label class="tv-field-wide">Frase complementar do painel motivacional<textarea name="motivation_subtitle" maxlength="220" rows="3"><?= e((string)($tvConfig['motivationSubtitle'] ?? 'Educação se faz com presença, respeito e compromisso.')) ?></textarea></label>
            <label class="tv-field-wide">Frase do rodapé<input type="text" name="footer_slogan" maxlength="180" value="<?= e((string)($tvConfig['footerSlogan'] ?? 'Cada presença conta. Cada aluno importa.')) ?>"></label>
        </div>
    </section>

    <section class="card tv-settings-card tv-settings-help">
        <div class="tv-settings-heading"><div><span>5</span><h3>Banners do Painel TV</h3></div><p>Como publicar comunicados na televisão.</p></div>
        <div class="tv-banner-help"><i data-lucide="image"></i><div><strong>Use o módulo Avisos da Gestão</strong><p>Ao criar ou editar um aviso, selecione <b>Painel-TV</b> em Público-alvo, mantenha o aviso ativo e envie uma imagem de banner. A imagem será exibida automaticamente no mural “Avisos e comunicados”.</p></div><a class="btn-secondary" href="<?= base_url('avisos/novo') ?>"><i data-lucide="plus"></i> Criar aviso</a></div>
    </section>

    <div class="tv-settings-actions"><button class="btn-primary" type="submit"><i data-lucide="save"></i> Salvar configurações</button></div>
</form>
<style>
.tv-settings-intro,.tv-settings-card{padding:1.4rem;margin-bottom:1rem}.tv-settings-intro{display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap}.tv-settings-intro h2{margin:0 0 .4rem}.tv-settings-intro p{margin:0;color:var(--text-secondary)}.tv-settings-heading{display:flex;justify-content:space-between;gap:1rem;align-items:center;margin-bottom:1.1rem;flex-wrap:wrap}.tv-settings-heading>div{display:flex;align-items:center;gap:.7rem}.tv-settings-heading span{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:var(--primary);color:#fff;font-weight:800}.tv-settings-heading h3{margin:0}.tv-settings-heading p{margin:0;color:var(--text-secondary)}.tv-settings-slides{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:.8rem}.tv-slide-option{display:flex;gap:.7rem;align-items:center;padding:1rem;border:1px solid var(--border-color);border-radius:12px;background:var(--surface-secondary);cursor:pointer}.tv-slide-option:has(input:checked){border-color:var(--primary);box-shadow:0 0 0 2px color-mix(in srgb,var(--primary) 18%,transparent)}.tv-slide-option small{display:block;color:var(--text-secondary);margin-top:.2rem}.tv-settings-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}.tv-settings-grid label{display:grid;gap:.45rem;font-weight:700}.tv-settings-grid input,.tv-settings-grid textarea,.tv-settings-grid select{width:100%}.tv-settings-grid-texts{grid-template-columns:repeat(2,minmax(0,1fr))}.tv-field-wide{grid-column:span 2}.tv-check{display:flex;gap:.6rem;align-items:center;margin-top:1rem}.tv-banner-help{display:grid;grid-template-columns:auto 1fr auto;gap:1rem;align-items:center;padding:1rem;border-radius:14px;background:color-mix(in srgb,var(--primary) 8%,var(--surface-secondary));border:1px solid color-mix(in srgb,var(--primary) 25%,var(--border-color))}.tv-banner-help>i{width:36px;height:36px;color:var(--primary)}.tv-banner-help p{margin:.25rem 0 0;color:var(--text-secondary);line-height:1.5}.tv-tip-banner-field{display:grid;gap:.7rem;padding:1rem;border:1px dashed var(--border-color);border-radius:14px;background:var(--surface-secondary)}.tv-tip-banner-field small{display:block;color:var(--text-secondary);font-weight:500;margin-top:.25rem}.tv-tip-banner-preview{width:min(100%,520px);aspect-ratio:4/3;border-radius:14px;border:1px solid var(--border-color);background:var(--surface-primary);background-position:center;background-size:cover;display:grid;place-items:center;overflow:hidden;transition:opacity .2s ease,filter .2s ease}.tv-tip-banner-preview.is-pending-removal{opacity:.42;filter:grayscale(1)}.tv-tip-banner-preview>div{display:grid;justify-items:center;gap:.35rem;color:var(--text-secondary);text-align:center}.tv-tip-banner-preview>div i{width:38px;height:38px}.tv-tip-banner-preview.has-image>div{display:none}.tv-tip-banner-actions{display:flex;gap:.7rem;flex-wrap:wrap}.tv-tip-file-button{cursor:pointer}.tv-tip-banner-status{display:flex;align-items:center;gap:.55rem;color:var(--text-secondary);font-weight:600}.tv-tip-banner-status.is-ready{color:var(--success)}.tv-tip-banner-status.is-warning{color:var(--danger)}.tv-tip-banner-status i{width:20px;height:20px}.tv-remove-banner{display:inline-flex!important;width:max-content;align-items:center;justify-content:center;gap:.5rem;color:var(--danger);border-color:color-mix(in srgb,var(--danger) 55%,var(--border-color));font-weight:700}.tv-remove-banner:hover,.tv-remove-banner.is-marked{color:#fff;background:var(--danger);border-color:var(--danger)}.tv-remove-banner svg{width:18px;height:18px}.tv-settings-actions{display:flex;justify-content:flex-end;padding-bottom:1rem}.tv-check-master{padding:1rem;border-radius:14px;background:color-mix(in srgb,var(--primary) 10%,var(--surface-secondary));border:1px solid color-mix(in srgb,var(--primary) 28%,var(--border-color))}.tv-auto-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:.75rem;margin-top:1rem}.tv-auto-option{display:flex;gap:.7rem;align-items:flex-start;padding:.9rem;border:1px solid var(--border-color);border-radius:12px;background:var(--surface-secondary)}.tv-auto-option small{display:block;margin-top:.2rem;color:var(--text-secondary)}.tv-intelligent-note{display:flex;gap:.6rem;align-items:center;margin:1rem 0 0;color:var(--text-secondary)}.tv-intelligent-note i{width:20px;color:var(--primary)}@media(max-width:760px){.tv-settings-grid-texts{grid-template-columns:1fr}.tv-field-wide{grid-column:auto}.tv-banner-help{grid-template-columns:1fr}.tv-banner-help>a{justify-self:start}}
</style>

<div class="tv-tip-cropper" id="dailyTipCropper" aria-hidden="true">
  <div class="tv-tip-cropper-backdrop"></div>
  <div class="tv-tip-cropper-dialog">
    <header><div><h2>Ajustar banner da Dica do Dia</h2><p>Posicione a imagem dentro da área 4:3. O resultado será salvo em 1200 × 900 px.</p></div><button type="button" class="icon-button" id="dailyTipCropClose"><i data-lucide="x"></i></button></header>
    <div class="tv-tip-crop-stage">
        <div class="tv-tip-crop-frame" aria-label="Área de recorte 4 por 3">
            <canvas id="dailyTipCropCanvas" width="800" height="600"></canvas>
            <div class="tv-tip-crop-grid" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
            <div class="tv-tip-crop-label" aria-hidden="true">ÁREA FINAL • 1200 × 900 px</div>
        </div>
    </div>
    <div class="tv-tip-crop-controls"><label>Zoom <input type="range" id="dailyTipCropZoom" min="1" max="3" value="1" step="0.01"></label><p>Arraste a imagem para reposicionar.</p></div>
    <footer><button type="button" class="btn-secondary" id="dailyTipCropCancel">Voltar</button><button type="button" class="btn-primary" id="dailyTipCropApply"><i data-lucide="check"></i> Confirmar recorte</button></footer>
  </div>
</div>
<style>
.tv-tip-cropper{position:fixed;inset:0;z-index:10000;display:none;place-items:center;padding:1rem}.tv-tip-cropper.open{display:grid}.tv-tip-cropper-backdrop{position:absolute;inset:0;background:rgba(3,10,7,.78);backdrop-filter:blur(4px)}.tv-tip-cropper-dialog{position:relative;width:min(960px,96vw);max-height:95vh;overflow:auto;background:var(--surface-primary);border:1px solid var(--border-color);border-radius:20px;box-shadow:0 28px 80px rgba(0,0,0,.35)}.tv-tip-cropper-dialog header,.tv-tip-cropper-dialog footer{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 1.25rem}.tv-tip-cropper-dialog header{border-bottom:1px solid var(--border-color)}.tv-tip-cropper-dialog footer{border-top:1px solid var(--border-color);justify-content:flex-end}.tv-tip-cropper-dialog h2{margin:0}.tv-tip-cropper-dialog p{margin:.25rem 0 0;color:var(--text-secondary)}.tv-tip-crop-stage{padding:1rem;display:grid;place-items:center;background:var(--surface-secondary)}.tv-tip-crop-frame{position:relative;width:min(800px,88vw);aspect-ratio:4/3;border:4px solid #fff;border-radius:14px;overflow:hidden;box-shadow:0 0 0 3px var(--primary),0 12px 36px rgba(0,0,0,.34);background:#111}.tv-tip-crop-frame::after{content:"";position:absolute;inset:10px;border:2px dashed rgba(255,255,255,.9);border-radius:8px;pointer-events:none}#dailyTipCropCanvas{display:block;width:100%;height:100%;border-radius:10px;cursor:grab;touch-action:none}.tv-tip-crop-grid{position:absolute;inset:0;pointer-events:none}.tv-tip-crop-grid span{position:absolute;background:rgba(255,255,255,.48)}.tv-tip-crop-grid span:nth-child(1),.tv-tip-crop-grid span:nth-child(2){top:0;bottom:0;width:1px}.tv-tip-crop-grid span:nth-child(1){left:33.333%}.tv-tip-crop-grid span:nth-child(2){left:66.666%}.tv-tip-crop-grid span:nth-child(3),.tv-tip-crop-grid span:nth-child(4){left:0;right:0;height:1px}.tv-tip-crop-grid span:nth-child(3){top:33.333%}.tv-tip-crop-grid span:nth-child(4){top:66.666%}.tv-tip-crop-label{position:absolute;left:16px;bottom:16px;padding:.45rem .7rem;border-radius:999px;background:rgba(0,0,0,.68);color:#fff;font-size:.78rem;font-weight:800;letter-spacing:.04em;pointer-events:none}#dailyTipCropCanvas.is-dragging{cursor:grabbing}.tv-tip-crop-controls{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1.25rem}.tv-tip-crop-controls label{display:flex;align-items:center;gap:.7rem;font-weight:700}.tv-tip-crop-controls input{width:min(320px,45vw)}body.tv-tip-modal-open{overflow:hidden}
</style>
<script src="<?= base_url('assets/js/pages/tv-panel-settings.js?v=4') ?>"></script>
