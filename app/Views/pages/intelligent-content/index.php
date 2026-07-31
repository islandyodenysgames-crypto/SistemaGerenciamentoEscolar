<?php
component('base/page-header',[
    'title'=>'Conteúdo Inteligente',
    'subtitle'=>'Revise, edite e acompanhe as mensagens produzidas automaticamente pelo sistema.',
]);
$labels=['pending'=>'Aguardando revisão','approved'=>'Aprovado','published'=>'Publicado','discarded'=>'Descartado'];
$types=[
'automatic_message'=>'Mensagem automática','daily_tip'=>'Dica do dia','support_text'=>'Texto de apoio','motivation'=>'Painel motivacional','institutional_slogan'=>'Slogan institucional','footer_slogan'=>'Rodapé','highlight_message'=>'Destaque do dia','did_you_know'=>'Sabia que...','hall_of_fame'=>'Hall da Fama','calendar_message'=>'Calendário'];
?>
<?php if(!empty($success)):?><div class="settings-feedback settings-feedback--success"><i data-lucide="circle-check"></i><span><?=e((string)$success)?></span></div><?php endif;?>
<?php if(!empty($error)):?><div class="settings-feedback settings-feedback--danger"><i data-lucide="triangle-alert"></i><span><?=e((string)$error)?></span></div><?php endif;?>

<section class="card ic-toolbar">
    <div><h2>Histórico e aprovação</h2><p>Os textos publicados automaticamente ficam registrados aqui. No modo “Revisar antes de publicar”, novas sugestões aguardam aprovação.</p></div>
    <div class="ic-actions">
        <a class="btn-secondary" href="<?=base_url('painel-tv/configuracoes')?>"><i data-lucide="settings"></i> Configurações</a>
        <form method="post" action="<?=base_url('configuracoes/conteudo-inteligente/limpar-cache')?>"><button class="btn-secondary" type="submit"><i data-lucide="refresh-cw"></i> Gerar novamente hoje</button></form>
    </div>
</section>

<nav class="ic-filters card">
    <?php foreach([''=>'Todos','pending'=>'Pendentes','published'=>'Publicados','approved'=>'Aprovados','discarded'=>'Descartados'] as $key=>$label):?>
        <a class="<?=($status??'')===$key?'active':''?>" href="<?=base_url('configuracoes/conteudo-inteligente'.($key!==''?'?status='.$key:''))?>"><?=e($label)?></a>
    <?php endforeach;?>
</nav>

<section class="ic-list">
<?php if(empty($items)):?>
    <div class="card ic-empty"><i data-lucide="sparkles"></i><h3>Nenhum conteúdo encontrado</h3><p>As mensagens aparecerão aqui quando o Painel TV gerar o conteúdo do dia.</p></div>
<?php endif;?>
<?php foreach((array)$items as $item):?>
    <article class="card ic-item">
        <header>
            <div><span class="ic-type"><?=e($types[$item['content_type']]??$item['content_type'])?></span><h3><?=e(date('d/m/Y',strtotime((string)$item['reference_date'])))?></h3></div>
            <span class="ic-status ic-status--<?=e((string)$item['status'])?>"><?=e($labels[$item['status']]??$item['status'])?></span>
        </header>
        <form method="post" action="<?=base_url('configuracoes/conteudo-inteligente/editar')?>">
            <input type="hidden" name="id" value="<?=(int)$item['id']?>">
            <textarea name="text_content" maxlength="1000" rows="3"><?=e((string)$item['text_content'])?></textarea>
            <div class="ic-meta"><span>Estilo: <?=e((string)$item['style'])?></span><span>Provedor: <?=e((string)$item['source_provider'])?></span><?php if(!empty($item['approved_by_name'])):?><span>Revisado por <?=e((string)$item['approved_by_name'])?></span><?php endif;?></div>
            <div class="ic-row-actions">
                <button class="btn-secondary" type="submit"><i data-lucide="pencil"></i> Editar e publicar</button>
            </div>
        </form>
        <?php if($item['status']==='pending'):?><div class="ic-decision"><form method="post" action="<?=base_url('configuracoes/conteudo-inteligente/aprovar')?>"><input type="hidden" name="id" value="<?=(int)$item['id']?>"><button class="btn-primary" type="submit"><i data-lucide="check"></i> Aprovar</button></form><form method="post" action="<?=base_url('configuracoes/conteudo-inteligente/descartar')?>"><input type="hidden" name="id" value="<?=(int)$item['id']?>"><button class="btn-danger" type="submit"><i data-lucide="x"></i> Descartar</button></form></div><?php endif;?>
    </article>
<?php endforeach;?>
</section>
<style>
.ic-toolbar{display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:1.25rem;margin-bottom:1rem}.ic-toolbar h2,.ic-item h3{margin:0}.ic-toolbar p{margin:.35rem 0 0;color:var(--text-secondary)}.ic-actions,.ic-decision,.ic-row-actions{display:flex;gap:.65rem;align-items:center;flex-wrap:wrap}.ic-actions form,.ic-decision form{margin:0}.ic-filters{display:flex;gap:.45rem;padding:.65rem;margin-bottom:1rem;overflow:auto}.ic-filters a{padding:.6rem .85rem;border-radius:10px;color:var(--text-secondary);font-weight:700;white-space:nowrap}.ic-filters a.active{background:var(--primary);color:#fff}.ic-list{display:grid;gap:1rem}.ic-item{padding:1.2rem}.ic-item header{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;margin-bottom:.8rem}.ic-type{font-weight:800;color:var(--primary);font-size:.84rem;text-transform:uppercase}.ic-status{padding:.35rem .65rem;border-radius:999px;font-weight:800;font-size:.8rem;background:var(--surface-secondary)}.ic-status--pending{color:#a55b00;background:#fff4d9}.ic-status--published,.ic-status--approved{color:#087a36;background:#e7f8ed}.ic-status--discarded{color:#a62929;background:#fdeaea}.ic-item textarea{width:100%;resize:vertical}.ic-meta{display:flex;gap:1rem;flex-wrap:wrap;color:var(--text-secondary);font-size:.85rem;margin:.65rem 0}.ic-decision{border-top:1px solid var(--border-color);padding-top:.8rem;margin-top:.8rem}.ic-empty{text-align:center;padding:3rem}.ic-empty i{width:48px;height:48px;color:var(--primary)}@media(max-width:760px){.ic-toolbar{align-items:flex-start;flex-direction:column}}
</style>
