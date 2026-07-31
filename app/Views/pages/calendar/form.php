<?php
$editing=!empty($event['id']);
$startTime=substr((string)($event['start_time']??''),0,5);
$endTime=substr((string)($event['end_time']??''),0,5);
component('base/page-header',['title'=>$editing?'Editar evento':'Novo evento','subtitle'=>'Organize datas importantes com informações claras para toda a escola.']);
?>
<section class="calendar-form-shell">
<div class="calendar-form-intro card">
<div class="calendar-form-intro__icon"><i data-lucide="calendar-range"></i></div>
<div><span>Agenda escolar</span><h2><?=$editing?'Atualizar evento':'Cadastrar novo evento'?></h2><p>O evento poderá aparecer no calendário geral e, quando destacado, ganhará prioridade visual no Dashboard.</p></div>
</div>
<div class="card school-calendar-form-card">
<?php if(!empty($calendarError)): ?><div class="alert alert-danger"><?=e($calendarError)?></div><?php endif; ?>
<form class="school-calendar-form" method="post" action="<?=base_url($editing?'calendario/editar':'calendario')?>">
<input type="hidden" name="_token" value="<?=e((string)$csrfToken)?>">
<?php if($editing): ?><input type="hidden" name="id" value="<?=(int)$event['id']?>"><?php endif; ?>
<label class="field wide"><span>Título *</span><input required maxlength="180" name="title" placeholder="Ex.: Reunião pedagógica" value="<?=e((string)($event['title']??''))?>"></label>
<label class="field"><span>Tipo *</span><select name="type"><?php foreach($types as $value=>$label): ?><option value="<?=e($value)?>" <?=($event['type']??'OTHER')===$value?'selected':''?>><?=e($label)?></option><?php endforeach;?></select></label>
<label class="field"><span>Local</span><input maxlength="180" name="location" placeholder="Ex.: Auditório" value="<?=e((string)($event['location']??''))?>"></label>
<div class="calendar-form-section wide"><span><i data-lucide="calendar-clock"></i> Data e horário</span></div>
<label class="field"><span>Data inicial *</span><input required type="date" name="start_date" value="<?=e((string)($event['start_date']??date('Y-m-d')))?>"></label>
<label class="field"><span>Data final</span><input type="date" name="end_date" value="<?=e((string)($event['end_date']??''))?>"></label>
<label class="field"><span>Ano letivo</span><select name="school_year_id"><option value="">Não vincular</option><?php foreach($schoolYears as $year): ?><option value="<?=(int)$year['id']?>" <?=((int)($event['school_year_id']??0)===(int)$year['id'])?'selected':''?>><?=e((string)$year['name'])?></option><?php endforeach;?></select></label>
<label class="field"><span>Período letivo</span><select name="school_period_id"><option value="">Identificação automática</option><?php foreach($schoolPeriods as $period): ?><option value="<?=(int)$period['id']?>" <?=((int)($event['school_period_id']??0)===(int)$period['id'])?'selected':''?>><?=e((string)$period['name'])?></option><?php endforeach;?></select></label>
<label class="field"><span>Horário inicial</span><input type="time" id="calendar-start-time" name="start_time" value="<?=e($startTime)?>"></label>
<label class="field"><span>Horário final</span><input type="time" id="calendar-end-time" name="end_time" value="<?=e($endTime)?>"></label>
<label class="field wide"><span>Descrição</span><textarea rows="5" name="description" placeholder="Inclua orientações e informações importantes..."><?=e((string)($event['description']??''))?></textarea></label>
<div class="school-calendar-options wide">
<label class="calendar-option"><input type="hidden" name="affects_school_day" value="0"><input type="checkbox" name="affects_school_day" value="1" <?=!empty($event['affects_school_day'])?'checked':''?>><span><i data-lucide="calendar-check"></i><strong>Alterar calendário letivo</strong><small>Sincroniza este evento com os dias escolares</small></span></label>
<label class="field"><span>Classificação do dia</span><select name="school_day_type"><?php foreach(['SCHOOL_DAY'=>'Dia letivo','HOLIDAY'=>'Feriado','RECESS'=>'Recesso','VACATION'=>'Férias','PLANNING'=>'Planejamento','COUNCIL'=>'Conselho','STOPPAGE'=>'Paralisação','MAKEUP'=>'Reposição','SATURDAY_SCHOOL'=>'Sábado letivo','OPTIONAL'=>'Ponto facultativo'] as $value=>$label): ?><option value="<?=e($value)?>" <?=($event['school_day_type']??'HOLIDAY')===$value?'selected':''?>><?=e($label)?></option><?php endforeach;?></select></label>
<label class="calendar-option"><input type="hidden" name="all_day" value="0"><input id="calendar-all-day" type="checkbox" name="all_day" value="1" <?=!empty($event['all_day'])?'checked':''?>><span><i data-lucide="sun"></i><strong>Dia inteiro</strong><small>Sem horário definido</small></span></label>
<label class="calendar-option is-featured"><input type="hidden" name="featured" value="0"><input type="checkbox" name="featured" value="1" <?=!empty($event['featured'])?'checked':''?>><span><i data-lucide="star"></i><strong>Destacar no Dashboard</strong><small>Prioridade na página inicial</small></span></label>
<label class="calendar-option"><input type="hidden" name="active" value="0"><input type="checkbox" name="active" value="1" <?=!isset($event['active'])||!empty($event['active'])?'checked':''?>><span><i data-lucide="circle-check"></i><strong>Evento ativo</strong><small>Visível para os usuários</small></span></label>
</div>
<div class="form-actions wide"><a class="btn-secondary" href="<?=base_url('calendario')?>">Cancelar</a><button class="btn-primary" type="submit"><i data-lucide="save"></i><?=$editing?'Salvar alterações':'Cadastrar evento'?></button></div>
</form></div></section>
<script>
(function(){
 const allDay=document.getElementById('calendar-all-day');
 const start=document.getElementById('calendar-start-time');
 const end=document.getElementById('calendar-end-time');
 if(!allDay||!start||!end)return;
 function sync(){
   start.disabled=allDay.checked; end.disabled=allDay.checked;
   if(allDay.checked){start.value='';end.value='';}
 }
 allDay.addEventListener('change',sync); sync();
})();
</script>
