<?php component('base/page-header',['title'=>'Alunos acompanhados','subtitle'=>'Uma entrada por caso, com plano e participantes compartilhados.']); ?>
<?php if($success): component('base/alert',['type'=>'success','message'=>$success]); endif; ?>
<?php if($error): component('base/alert',['type'=>'danger','message'=>$error]); endif; ?>
<?php
$filters=(array)($filters??[]);$pagination=(array)($pagination??[]);$summary=(array)($summary??[]);$filterOptions=(array)($filterOptions??[]);
$viewValue=$allSchool?'todos':'meus';
$queryBase=['visao'=>$viewValue,'turma'=>$filters['class_id']??0,'status'=>$filters['status']??'','motivo'=>$filters['reason']??'','professor'=>$filters['teacher_id']??0,'inicio'=>$filters['start_date']??'','fim'=>$filters['end_date']??'','risco'=>$filters['risk']??'','ordem'=>$filters['order']??'end_desc','por_pagina'=>$filters['per_page']??10];
$riskLabels=['CRITICAL'=>'Crítico','HIGH'=>'Alto','ATTENTION'=>'Atenção','LOW'=>'Baixo'];
?>
<div class="monitoring-toolbar"><?php if($canViewAll): ?><a class="<?= !$allSchool?'is-active':'' ?>" href="<?= base_url('acompanhamentos?visao=meus') ?>">Meus acompanhamentos</a><a class="<?= $allSchool?'is-active':'' ?>" href="<?= base_url('acompanhamentos?visao=todos') ?>">Todos os acompanhamentos</a><?php endif; ?></div>
<section class="monitoring-case-summary" aria-label="Resumo dos casos"><article><small>Casos encontrados</small><strong><?= (int)($summary['total']??0) ?></strong></article><article><small>Ativos</small><strong><?= (int)($summary['active']??0) ?></strong></article><article><small>Concluídos</small><strong><?= (int)($summary['concluded']??0) ?></strong></article><article><small>Para revisar</small><strong><?= (int)($summary['review']??0) ?></strong></article></section>
<form method="get" action="<?= base_url('acompanhamentos') ?>" class="monitoring-case-filters">
<input type="hidden" name="visao" value="<?= e($viewValue) ?>">
<label>Turma<select name="turma"><option value="0">Todas</option><?php foreach((array)($filterOptions['classes']??[]) as $id=>$label): ?><option value="<?= (int)$id ?>" <?= (int)($filters['class_id']??0)===(int)$id?'selected':'' ?>><?= e((string)$label) ?></option><?php endforeach; ?></select></label>
<label>Status<select name="status"><option value="">Todos</option><option value="ACTIVE" <?= ($filters['status']??'')==='ACTIVE'?'selected':'' ?>>Ativos</option><option value="CONCLUDED" <?= ($filters['status']??'')==='CONCLUDED'?'selected':'' ?>>Concluídos</option></select></label>
<label>Motivo<select name="motivo"><option value="">Todos</option><?php foreach((array)($filterOptions['reasons']??[]) as $code=>$label): ?><option value="<?= e((string)$code) ?>" <?= ($filters['reason']??'')===$code?'selected':'' ?>><?= e((string)$label) ?></option><?php endforeach; ?></select></label>
<label>Professor<select name="professor"><option value="0">Todos</option><?php foreach((array)($filterOptions['teachers']??[]) as $id=>$name): ?><option value="<?= (int)$id ?>" <?= (int)($filters['teacher_id']??0)===(int)$id?'selected':'' ?>><?= e((string)$name) ?></option><?php endforeach; ?></select></label>
<label>Risco<select name="risco"><option value="">Todos</option><?php foreach($riskLabels as $code=>$label): ?><option value="<?= e($code) ?>" <?= ($filters['risk']??'')===$code?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
<label>De<input type="date" name="inicio" value="<?= e((string)($filters['start_date']??'')) ?>"></label><label>Até<input type="date" name="fim" value="<?= e((string)($filters['end_date']??'')) ?>"></label>
<label>Ordenar<select name="ordem"><option value="end_desc" <?= ($filters['order']??'')==='end_desc'?'selected':'' ?>>Prazo mais recente</option><option value="end_asc" <?= ($filters['order']??'')==='end_asc'?'selected':'' ?>>Prazo mais próximo</option><option value="student_asc" <?= ($filters['order']??'')==='student_asc'?'selected':'' ?>>Aluno A–Z</option><option value="risk_desc" <?= ($filters['order']??'')==='risk_desc'?'selected':'' ?>>Maior risco</option></select></label>
<label>Por página<select name="por_pagina"><?php foreach([5,10,20,30] as $size): ?><option value="<?= $size ?>" <?= (int)($filters['per_page']??10)===$size?'selected':'' ?>><?= $size ?></option><?php endforeach; ?></select></label>
<div class="monitoring-case-filters__actions"><button type="submit" class="btn-primary"><i data-lucide="filter"></i> Aplicar filtros</button><a class="btn-secondary" href="<?= base_url('acompanhamentos?visao='.$viewValue) ?>"><i data-lucide="rotate-ccw"></i> Limpar</a></div>
</form>
<?php $reviewItems=array_values(array_filter($items,static fn(array $entry): bool=>!empty($entry['needs_review']))); ?>
<section class="monitoring-review-queue">
<div class="monitoring-review-queue__header"><div><span><i data-lucide="list-checks"></i> Casos para revisar hoje</span><small>Prioridades calculadas pela frequência, ocorrências e tempo sem intervenção.</small></div><strong><?= count($reviewItems) ?></strong></div>
<?php if($reviewItems): ?><div class="monitoring-review-queue__items"><?php foreach(array_slice($reviewItems,0,8) as $review): ?><a href="#aluno-<?= (int)$review['student_id'] ?>"><span><?= e((string)$review['student_name']) ?></span><small><?= e((string)($review['recommendation']['title']??'Revisar acompanhamento')) ?></small><b><?= (int)($review['days_without_action']??0) ?> dia(s) sem ação</b></a><?php endforeach; ?></div><?php else: ?><p class="monitoring-review-queue__empty"><i data-lucide="circle-check-big"></i> Nenhum caso urgente neste momento.</p><?php endif; ?>
</section>
<div class="monitoring-grid">
<?php if(!$items): ?><div class="empty-state"><i data-lucide="user-round-search"></i><h3>Nenhum aluno em acompanhamento</h3><p>Abra o perfil de um aluno para iniciar.</p></div><?php endif; ?>
<?php foreach($items as $item): $actions=(array)($item['actions']??[]); $isConcluded=!empty($item['is_concluded']); ?>
<article class="monitoring-card" id="aluno-<?= (int)$item['student_id'] ?>" data-monitoring-card>
<header><div><h3><?= e((string)$item['student_name']) ?></h3><p><?= e(trim((string)($item['class_year']??'').' '.(string)($item['class_name']??''))) ?></p></div><div class="monitoring-card__status-area"><span class="monitoring-status-badge <?= $isConcluded?'is-concluded':'is-active' ?>"><i data-lucide="<?= $isConcluded?'circle-check-big':'activity' ?>"></i><?= $isConcluded?'Caso concluído':'Caso ativo' ?></span><small>até <?= date('d/m/Y',strtotime((string)$item['end_date'])) ?></small></div></header>
<div class="monitoring-card__metrics"><span><small>Frequência no período</small><strong><?= number_format((float)($item['metrics']['attendance_percentage']??0),1,',','.') ?>%</strong></span><span><small>Ocorrências</small><strong><?= (int)($item['metrics']['total_occurrences']??0) ?></strong></span><span><small>Ações</small><strong><?= count($actions) ?></strong></span></div>
<div class="monitoring-card__teachers"><small>Participantes do caso (<?= (int)($item['participants_count']??count($item['users'])) ?>)</small><?php foreach($item['users'] as $u): if(($u['status']??'')!=='ACTIVE')continue; ?><span><?= e((string)$u['name']) ?></span><?php endforeach; ?></div>
<?php if(!empty($item['plan'])): ?><div class="monitoring-card__plan"><small>Plano compartilhado do caso</small><strong><?= e((string)$item['plan']['objective_label']) ?></strong><?php if(($item['plan']['target_metric']??'NONE')!=='NONE'&&$item['plan']['target_value']!==null): ?><span>Meta: <?= e((string)$item['plan']['target_value']) ?><?= ($item['plan']['target_metric']==='ATTENDANCE')?'%':' ocorrência(s)' ?></span><?php endif; ?></div><?php endif; ?>
<?php $recommendation=(array)($item['recommendation']??[]); $recommendationPriority=strtolower((string)($recommendation['priority']??'attention')); ?>
<?php if($recommendation&&!$isConcluded): ?>
<section class="monitoring-recommendation monitoring-recommendation--<?= e($recommendationPriority) ?>">
<div class="monitoring-recommendation__icon"><i data-lucide="route"></i></div>
<div class="monitoring-recommendation__content"><small>Ação recomendada · <?= e((string)($recommendation['status']??'PENDING')) ?></small><strong><?= e((string)$recommendation['title']) ?></strong><p><?= e((string)$recommendation['reason']) ?></p>
<div class="monitoring-recommendation__quick-actions">
<?php if(!empty($item['can_register_action'])): ?><button type="button" data-action-open><i data-lucide="plus"></i> Registrar ação</button><?php endif; ?>
<a href="<?= base_url('alunos/perfil?id='.(int)$item['student_id'].'#studentMonitoring') ?>"><i data-lucide="user-plus"></i> Adicionar acompanhante</a>
<a href="#historico-<?= (int)$item['id'] ?>"><i data-lucide="history"></i> Ver histórico</a>
<a href="<?= base_url('alunos/perfil?id='.(int)$item['student_id']) ?>"><i data-lucide="user-round"></i> Ver perfil</a>
</div>
<form method="post" action="<?= base_url('acompanhamentos/recomendacoes/atualizar') ?>" class="monitoring-recommendation__status-form">
<input type="hidden" name="recommendation_id" value="<?= (int)$recommendation['id'] ?>">
<select name="status" aria-label="Situação da recomendação"><option value="PENDING" <?= ($recommendation['status']??'')==='PENDING'?'selected':'' ?>>Pendente</option><option value="IN_PROGRESS" <?= ($recommendation['status']??'')==='IN_PROGRESS'?'selected':'' ?>>Em andamento</option><option value="COMPLETED">Atendida</option><option value="DISMISSED">Dispensada</option></select>
<input type="text" name="resolution_notes" placeholder="Observação ou motivo da dispensa">
<button type="submit"><i data-lucide="save"></i> Atualizar</button>
</form></div>
</section>
<?php endif; ?>
<?php $monitoringInsights=(array)($item['monitoring_insights']??[]); if($monitoringInsights&&!$isConcluded): ?>
<section class="monitoring-card__intelligence">
<div class="monitoring-card__intelligence-title"><span><i data-lucide="brain-circuit"></i> Inteligência do acompanhamento</span><small><?= count($monitoringInsights) ?> sinal(is)</small></div>
<?php foreach($monitoringInsights as $insight): $level=strtolower((string)($insight['level']??'info')); ?>
<article class="monitoring-insight monitoring-insight--<?= e($level) ?>">
<i data-lucide="<?= e((string)($insight['icon']??'lightbulb')) ?>"></i>
<div><strong><?= e((string)$insight['title']) ?></strong><p><?= e((string)$insight['message']) ?></p><small><?= e((string)$insight['recommendation']) ?></small></div>
</article>
<?php endforeach; ?>
</section>
<?php endif; ?>
<?php if(!empty($item['can_register_action'])): ?>
<div class="monitoring-card__action-toolbar"><button type="button" class="btn-primary" data-action-open><i data-lucide="plus"></i> Registrar Ação</button></div>
<form method="post" enctype="multipart/form-data" action="<?= base_url('acompanhamentos/acoes/registrar') ?>" class="student-monitoring-action-form monitoring-card__action-form" data-action-form hidden>
<input type="hidden" name="monitoring_id" value="<?= (int)$item['id'] ?>">
<div class="student-monitoring-field"><label>Data</label><input type="date" name="action_date" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required></div>
<div class="student-monitoring-field"><label>Tipo da ação</label><select name="action_type" required><option value="">Selecione</option><?php foreach($actionTypeOptions as $code=>$label): ?><option value="<?= e((string)$code) ?>"><?= e((string)$label) ?></option><?php endforeach; ?></select></div>
<div class="student-monitoring-field student-monitoring-field--wide"><label>Descrição da ação</label><textarea name="description" rows="3" required placeholder="Descreva o que foi realizado."></textarea></div>
<div class="student-monitoring-field"><label>Problema relacionado</label><select name="issue_type" required><?php foreach($issueTypeOptions as $code=>$label): ?><option value="<?= e((string)$code) ?>"><?= e((string)$label) ?></option><?php endforeach; ?></select></div>
<div class="student-monitoring-field"><label>Próxima ação <span>(opcional)</span></label><textarea name="next_action" rows="2"></textarea></div>
<div class="student-monitoring-field student-monitoring-field--wide monitoring-attachments-field"><label><i data-lucide="paperclip"></i> Anexos <span>(opcional)</span></label><?php component('media/staged-file-upload', ['inputName'=>'attachments[]','maxFiles'=>10,'maxSizeMb'=>25,'help'=>'Até 10 arquivos, com no máximo 25 MB por arquivo.']); ?></div>
<div class="student-monitoring-actions student-monitoring-field--wide"><button type="submit" class="btn-primary"><i data-lucide="save"></i> Salvar ação</button><button type="button" class="btn-secondary" data-action-cancel>Cancelar</button></div>
</form>
<?php endif; ?>
<?php if($isConcluded): ?><div class="monitoring-card__concluded-notice"><i data-lucide="archive"></i><div><strong>Acompanhamento concluído</strong><p>Não é mais possível registrar novas ações. O histórico abaixo permanece preservado para consulta e relatórios.</p></div></div><?php endif; ?>
<?php if($actions): ?>
<details class="monitoring-card__recent-actions <?= $isConcluded?'is-collapsed':'' ?>" id="historico-<?= (int)$item['id'] ?>" <?= $isConcluded?'':'open' ?>>
<summary><span><i data-lucide="history"></i><?= $isConcluded?'Exibir ações realizadas':'Histórico de ações' ?> <b>(<?= count($actions) ?>)</b></span><small data-actions-toggle-label><?= $isConcluded?'Clique para consultar o registro':'Ações do acompanhamento' ?></small></summary>
<div class="monitoring-card__actions-body">
<?php foreach($actions as $action): $canManage=!empty($action['can_manage']); ?>
<div class="monitoring-card__recent-action" data-action-item>
<div class="monitoring-card__recent-action-content">
<strong><?= date('d/m/Y',strtotime((string)$action['action_date'])) ?> — <?= e((string)$action['action_type_label']) ?></strong>
<span><?= e((string)$action['author_name']) ?></span>
<p><?= nl2br(e((string)$action['description'])) ?></p>
<small>Motivo: <?= e((string)($issueTypeOptions[$action['issue_type']??'GENERAL']??'Acompanhamento geral')) ?></small>
<?php if(!empty($action['next_action'])): ?><small>Próxima ação: <?= e((string)$action['next_action']) ?></small><?php endif; ?>
<?php $actionAttachments=(array)($action['attachments']??[]); if($actionAttachments): ?>
<?php component('media/attachment-carousel', ['attachments' => $actionAttachments, 'title' => 'Imagens da intervenção']); ?>
<div class="monitoring-action-attachments"><div class="monitoring-action-attachments__title"><i data-lucide="paperclip"></i><strong>Arquivos</strong><span><?= count($actionAttachments) ?></span></div><div class="monitoring-action-attachments__list">
<?php foreach($actionAttachments as $attachment): ?><div class="monitoring-action-attachment"><a href="<?= e((string)$attachment['url']) ?>" target="_blank" rel="noopener noreferrer"><i data-lucide="<?= e((string)$attachment['icon']) ?>"></i><span><strong><?= e((string)$attachment['original_name']) ?></strong><small><?= e((string)$attachment['size_label']) ?></small></span></a><?php if($canManage): ?><form method="post" action="<?= base_url('acompanhamentos/acoes/anexos/excluir') ?>" onsubmit="return confirm('Excluir este anexo?');"><input type="hidden" name="attachment_id" value="<?= (int)$attachment['id'] ?>"><input type="hidden" name="monitoring_id" value="<?= (int)$item['id'] ?>"><button type="submit" title="Excluir anexo"><i data-lucide="trash-2"></i></button></form><?php endif; ?></div><?php endforeach; ?>
</div></div>
<?php endif; ?>
</div>
<?php if($canManage): ?>
<div class="monitoring-card__recent-action-controls" aria-label="Gerenciar ação">
<button type="button" class="monitoring-action-button monitoring-action-button--edit" data-action-edit-open title="Editar ação"><i data-lucide="pencil"></i><span>Editar ação</span></button>
<form method="post" action="<?= base_url('acompanhamentos/acoes/excluir') ?>" onsubmit="return confirm('Excluir esta ação? Ela deixará de aparecer no histórico e nas leituras do acompanhamento.');">
<input type="hidden" name="action_id" value="<?= (int)$action['id'] ?>">
<button type="submit" class="monitoring-action-button monitoring-action-button--delete" title="Excluir ação"><i data-lucide="trash-2"></i><span>Excluir ação</span></button>
</form>
</div>
<form method="post" enctype="multipart/form-data" action="<?= base_url('acompanhamentos/acoes/atualizar') ?>" class="student-monitoring-action-form monitoring-card__action-edit-form monitoring-action-drawer" data-action-edit-form role="dialog" aria-modal="true" aria-label="Editar ação" hidden>
<div class="monitoring-action-drawer__backdrop" data-action-edit-backdrop></div>
<aside class="monitoring-action-drawer__panel" data-action-edit-panel>
<input type="hidden" name="action_id" value="<?= (int)$action['id'] ?>">
<div class="monitoring-action-edit__header student-monitoring-field--wide">
<div class="monitoring-action-edit__heading"><span class="monitoring-action-edit__icon"><i data-lucide="pencil-line"></i></span><div><small>Edição de ação</small><strong><?= e((string)$action['action_type_label']) ?></strong><p>Atualize os dados registrados sem alterar o histórico anterior do acompanhamento.</p></div></div>
<button type="button" class="monitoring-action-edit__close" data-action-edit-cancel aria-label="Fechar edição"><i data-lucide="x"></i></button>
</div>
<section class="monitoring-action-edit__section student-monitoring-field--wide">
<div class="monitoring-action-edit__section-title"><span><i data-lucide="clipboard-pen-line"></i></span><div><strong>Dados da ação</strong><small>Informações principais do registro.</small></div></div>
<div class="monitoring-action-edit__grid">
<div class="student-monitoring-field"><label>Data da ação</label><input type="date" name="action_date" max="<?= date('Y-m-d') ?>" value="<?= e((string)$action['action_date']) ?>" required></div>
<div class="student-monitoring-field"><label>Tipo da ação</label><select name="action_type" required><?php foreach($actionTypeOptions as $code=>$label): ?><option value="<?= e((string)$code) ?>" <?= ($action['action_type']??'')===$code?'selected':'' ?>><?= e((string)$label) ?></option><?php endforeach; ?></select></div>
<div class="student-monitoring-field student-monitoring-field--wide"><label>Problema relacionado</label><select name="issue_type" required><?php foreach($issueTypeOptions as $code=>$label): ?><option value="<?= e((string)$code) ?>" <?= ($action['issue_type']??'GENERAL')===$code?'selected':'' ?>><?= e((string)$label) ?></option><?php endforeach; ?></select></div>
</div>
</section>
<section class="monitoring-action-edit__section student-monitoring-field--wide">
<div class="monitoring-action-edit__section-title"><span><i data-lucide="align-left"></i></span><div><strong>Descrição e continuidade</strong><small>Detalhe o que foi realizado e indique a próxima ação, quando houver.</small></div></div>
<div class="monitoring-action-edit__grid">
<div class="student-monitoring-field student-monitoring-field--wide"><label>Descrição da ação</label><textarea name="description" rows="4" required><?= e((string)$action['description']) ?></textarea></div>
<div class="student-monitoring-field student-monitoring-field--wide"><label>Próxima ação <span>(opcional)</span></label><textarea name="next_action" rows="3" placeholder="Descreva o próximo encaminhamento, se necessário."><?= e((string)($action['next_action']??'')) ?></textarea></div>
</div>
</section>
<section class="monitoring-action-edit__section monitoring-action-edit__section--attachments student-monitoring-field--wide">
<div class="monitoring-action-edit__section-title"><span><i data-lucide="paperclip"></i></span><div><strong>Arquivos da ação</strong><small>Adicione novos documentos, imagens, vídeos ou áudios.</small></div></div>
<div class="student-monitoring-field monitoring-attachments-field"><label>Adicionar anexos <span>(opcional)</span></label><?php component('media/staged-file-upload', ['inputName'=>'attachments[]','maxFiles'=>10,'maxSizeMb'=>25,'help'=>'Os arquivos existentes serão preservados. Até 10 novos arquivos, com no máximo 25 MB por arquivo.']); ?></div>
<?php if($actionAttachments): ?><div class="monitoring-action-edit__existing-files"><i data-lucide="check-circle-2"></i><span><strong><?= count($actionAttachments) ?> arquivo(s) já vinculado(s)</strong><small>Gerencie os anexos existentes na visualização da ação, acima deste formulário.</small></span></div><?php endif; ?>
</section>
<div class="monitoring-action-edit__footer student-monitoring-field--wide"><button type="button" class="btn-secondary" data-action-edit-cancel><i data-lucide="x"></i> Cancelar</button><button type="submit" class="btn-primary"><i data-lucide="save"></i> Salvar alterações</button></div>
</aside>
</form>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</details>
<?php endif; ?>

<footer><a href="<?= base_url('alunos/perfil?id='.(int)$item['student_id']) ?>">Ver perfil</a><?php if(!empty($item['can_register_action'])): ?><form method="post" action="<?= base_url('acompanhamentos/encerrar') ?>"><input type="hidden" name="monitoring_id" value="<?= (int)$item['id'] ?>"><button type="submit">Encerrar meu vínculo</button></form><?php endif; ?><?php if(!empty($item['can_delete_concluded']) && (int)($item['monitoring_user_id']??0)>0): ?><form method="post" action="<?= base_url('acompanhamentos/vinculo-concluido/excluir') ?>" onsubmit="return confirm('Excluir definitivamente este vínculo concluído e todas as ações vinculadas a ele? Esta operação não poderá ser desfeita.');"><input type="hidden" name="monitoring_user_id" value="<?= (int)$item['monitoring_user_id'] ?>"><button type="submit" class="monitoring-delete-concluded-link"><i data-lucide="trash-2"></i> Excluir vínculo concluído</button></form><?php endif; ?></footer>
</article>
<?php endforeach; ?>
</div>
<?php if((int)($pagination['pages']??1)>1): ?><nav class="monitoring-pagination" aria-label="Paginação dos acompanhamentos"><?php for($pageNumber=1;$pageNumber<=(int)$pagination['pages'];$pageNumber++): $pageQuery=$queryBase;$pageQuery['pagina']=$pageNumber; ?><a class="<?= $pageNumber===(int)($pagination['page']??1)?'is-active':'' ?>" href="<?= base_url('acompanhamentos?'.http_build_query($pageQuery)) ?>"><?= $pageNumber ?></a><?php endfor; ?></nav><?php endif; ?>

<style>
.monitoring-attachments-field label{display:flex;align-items:center;gap:.45rem}.monitoring-attachments-field label svg{width:17px;height:17px}.monitoring-attachments-field small{display:block;margin-top:.35rem;color:var(--text-muted,#64748b)}.monitoring-action-attachments{margin-top:.75rem;padding:.75rem;border:1px solid var(--border-color,#e2e8f0);border-radius:12px;background:var(--surface-soft,#f8fafc)}.monitoring-action-attachments__title{display:flex;align-items:center;gap:.45rem;margin-bottom:.55rem}.monitoring-action-attachments__title svg{width:16px;height:16px}.monitoring-action-attachments__title span{margin-left:auto;font-size:.75rem;padding:.1rem .45rem;border-radius:999px;background:#e2e8f0}.monitoring-action-attachments__list{display:grid;gap:.45rem}.monitoring-action-attachment{display:flex;align-items:center;gap:.5rem;padding:.5rem .6rem;border-radius:9px;background:#fff;border:1px solid #e5e7eb}.monitoring-action-attachment>a{min-width:0;flex:1;display:flex;align-items:center;gap:.55rem;text-decoration:none;color:inherit}.monitoring-action-attachment>a>svg{width:18px;height:18px;flex:none}.monitoring-action-attachment>a span{min-width:0;display:grid}.monitoring-action-attachment>a strong{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.monitoring-action-attachment>a small{font-size:.72rem;color:#64748b}.monitoring-action-attachment form{margin:0}.monitoring-action-attachment button{display:grid;place-items:center;border:0;background:transparent;color:#b91c1c;cursor:pointer;padding:.35rem;border-radius:7px}.monitoring-action-attachment button:hover{background:#fee2e2}.monitoring-action-attachment button svg{width:16px;height:16px}
.monitoring-card__status-area{display:grid;justify-items:end;gap:.25rem}.monitoring-card__status-area>small{font-size:.75rem;color:#64748b}.monitoring-status-badge{display:inline-flex;align-items:center;gap:.4rem;padding:.42rem .7rem;border-radius:999px;font-weight:800;font-size:.78rem}.monitoring-status-badge svg{width:15px;height:15px}.monitoring-status-badge.is-active{background:#dcfce7;color:#166534}.monitoring-status-badge.is-concluded{background:#e2e8f0;color:#334155}.monitoring-card__concluded-notice{display:flex;gap:.75rem;margin:1rem 0;padding:1rem;border:1px solid #cbd5e1;border-left:4px solid #64748b;border-radius:12px;background:#f8fafc;color:#334155}.monitoring-card__concluded-notice>svg{width:21px;height:21px;flex:none}.monitoring-card__concluded-notice p{margin:.25rem 0 0}.monitoring-card__recent-actions>summary{cursor:pointer;display:flex;justify-content:space-between;gap:1rem;align-items:center;padding:.85rem 0;list-style:none}.monitoring-card__recent-actions>summary::-webkit-details-marker{display:none}.monitoring-card__recent-actions>summary span{display:flex;align-items:center;gap:.45rem;font-weight:800}.monitoring-card__recent-actions>summary svg{width:17px;height:17px}.monitoring-card__actions-body{display:grid;gap:.75rem}.monitoring-action-edit__footer .btn-primary{border:1px solid var(--primary,#16803c)!important;background:var(--primary,#16803c)!important;color:#fff!important;box-shadow:0 6px 16px rgba(22,128,60,.2)!important}.monitoring-action-edit__footer .btn-primary:hover{filter:brightness(.92)!important;color:#fff!important}.monitoring-card footer .monitoring-delete-concluded-link{display:inline-flex;align-items:center;gap:.4rem;background:#b91c1c;color:#fff;border:1px solid #991b1b}.monitoring-card footer .monitoring-delete-concluded-link:hover{background:#991b1b}.monitoring-card footer .monitoring-delete-concluded-link svg{width:16px;height:16px}
</style>
<script>
(()=>{
 let activeDrawer=null;
 let activeTrigger=null;
 let savedScrollY=0;
 let initialState='';

 const serializeForm=form=>{
  const data=new FormData(form);
  data.delete('attachments[]');
  return JSON.stringify(Array.from(data.entries()));
 };
 const lockPage=()=>{
  savedScrollY=window.scrollY;
  document.body.classList.add('monitoring-drawer-open');
  document.body.style.top=`-${savedScrollY}px`;
 };
 const unlockPage=()=>{
  document.body.classList.remove('monitoring-drawer-open');
  document.body.style.top='';
  window.scrollTo(0,savedScrollY);
 };
 const hasChanges=form=>serializeForm(form)!==initialState||Array.from(form.querySelectorAll('input[type=file]')).some(input=>input.files&&input.files.length);
 const closeDrawer=(force=false)=>{
  if(!activeDrawer)return;
  if(!force&&hasChanges(activeDrawer)&&!window.confirm('Há alterações não salvas. Deseja fechar a edição mesmo assim?'))return;
  const drawer=activeDrawer,trigger=activeTrigger;
  drawer.classList.remove('is-open');
  window.setTimeout(()=>{drawer.hidden=true;},220);
  activeDrawer=null;activeTrigger=null;initialState='';
  unlockPage();
  trigger?.focus();
 };
 const openDrawer=(drawer,trigger)=>{
  if(activeDrawer&&activeDrawer!==drawer)closeDrawer(true);
  activeDrawer=drawer;activeTrigger=trigger;
  drawer.hidden=false;
  initialState=serializeForm(drawer);
  lockPage();
  requestAnimationFrame(()=>{
   drawer.classList.add('is-open');
   drawer.querySelector('input:not([type=hidden]),select,textarea,button')?.focus();
  });
 };

 document.querySelectorAll('[data-monitoring-card]').forEach(card=>{
  const opens=card.querySelectorAll('[data-action-open]'), form=card.querySelector('[data-action-form]'), cancel=card.querySelector('[data-action-cancel]');
  if(form) opens.forEach(open=>open.addEventListener('click',()=>{form.hidden=false;opens.forEach(button=>button.hidden=true);form.querySelector('select,input,textarea')?.focus();}));
  if(cancel&&form) cancel.addEventListener('click',()=>{form.hidden=true;opens.forEach(button=>button.hidden=false);opens[0]?.focus();});
  card.querySelectorAll('[data-action-item]').forEach(item=>{
   const editOpen=item.querySelector('[data-action-edit-open]'), editForm=item.querySelector('[data-action-edit-form]');
   if(editOpen&&editForm)editOpen.addEventListener('click',()=>openDrawer(editForm,editOpen));
   editForm?.querySelectorAll('[data-action-edit-cancel]').forEach(button=>button.addEventListener('click',()=>closeDrawer(false)));
   editForm?.querySelector('[data-action-edit-backdrop]')?.addEventListener('click',()=>closeDrawer(false));
   editForm?.addEventListener('submit',()=>closeDrawer(true));
  });
 });
 document.querySelectorAll('.monitoring-card__recent-actions').forEach(details=>{const label=details.querySelector('[data-actions-toggle-label]');if(!label)return;details.addEventListener('toggle',()=>{if(details.classList.contains('is-collapsed'))label.textContent=details.open?'Ocultar ações realizadas':'Clique para consultar o registro';});});
 document.addEventListener('keydown',event=>{
  if(event.key==='Escape'&&activeDrawer){event.preventDefault();closeDrawer(false);}
 });
})();
</script>
<style>
.monitoring-case-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin:1rem 0}.monitoring-case-summary article{padding:1rem 1.1rem;border:1px solid var(--border-color,#e2e8f0);border-radius:14px;background:#fff;display:grid;gap:.25rem}.monitoring-case-summary small{color:#64748b;font-weight:700}.monitoring-case-summary strong{font-size:1.55rem;color:#0f172a}.monitoring-case-filters{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.8rem;padding:1rem;margin:0 0 1rem;border:1px solid var(--border-color,#e2e8f0);border-radius:14px;background:#fff}.monitoring-case-filters label{display:grid;gap:.35rem;font-size:.78rem;font-weight:800;color:#475569}.monitoring-case-filters select,.monitoring-case-filters input{width:100%;min-height:40px;border:1px solid #cbd5e1;border-radius:9px;padding:.5rem .65rem;background:#fff;color:#0f172a}.monitoring-case-filters__actions{grid-column:1/-1;display:flex;justify-content:flex-end;gap:.6rem}.monitoring-case-filters__actions a,.monitoring-case-filters__actions button{display:inline-flex;align-items:center;gap:.4rem}.monitoring-pagination{display:flex;justify-content:center;flex-wrap:wrap;gap:.4rem;margin:1.25rem 0}.monitoring-pagination a{min-width:38px;height:38px;display:grid;place-items:center;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#334155;text-decoration:none;font-weight:800}.monitoring-pagination a.is-active{background:var(--primary,#16803c);border-color:var(--primary,#16803c);color:#fff}@media(max-width:1000px){.monitoring-case-summary,.monitoring-case-filters{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.monitoring-case-summary,.monitoring-case-filters{grid-template-columns:1fr}.monitoring-case-filters__actions{justify-content:stretch;flex-direction:column}.monitoring-case-filters__actions a,.monitoring-case-filters__actions button{justify-content:center}}
</style>
