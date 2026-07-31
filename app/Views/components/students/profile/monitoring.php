<?php
$context=is_array($studentMonitoring??null)?$studentMonitoring:[];
$intelligencePrefill=(array)($context['intelligence_prefill']??[]);
$hasIntelligencePrefill=$intelligencePrefill!==[];
$prefillPlan=$hasIntelligencePrefill?['objective_code'=>(string)($intelligencePrefill['objective_code']??''),'strategies'=>(array)($intelligencePrefill['strategies']??[]),'target_metric'=>(string)($intelligencePrefill['target_metric']??'NONE')]:null;
$monitoring=$context['monitoring']??null;
$cases=(array)($context['cases']??[]);
$activeCasesCount=(int)($context['active_cases_count']??0);
$concludedCasesCount=(int)($context['concluded_cases_count']??0);
$currentLink=$context['current_link']??null;
$displayLink=$currentLink??($monitoring?['start_date'=>$monitoring['start_date']??'','end_date'=>$monitoring['end_date']??'','reason'=>$monitoring['reason']??'']:null);
$users=$context['users']??[];
$teachers=$context['teachers']??[];
$isFollowing=(bool)($context['is_following']??false);
$hasHistory=(bool)($context['has_monitoring_history']??false);
$isConcluded=(bool)($context['is_concluded']??false);
$selectedClosedBy=(string)($monitoring['closed_by_name']??'');
$selectedClosedAt=(string)($monitoring['closed_at']??'');
$monitoringActions=(array)($context['actions']??[]);
$canAssign=(bool)($context['can_assign_others']??false);
$currentUserId=(int)($context['current_user_id']??0);
$reasonOptions=$context['reason_options']??[];
$objectiveOptions=$context['objective_options']??[];
$strategyOptions=$context['strategy_options']??[];
$plan=is_array($context['plan']??null)?$context['plan']:null;
$metrics=$context['metrics']??[];
$referenceStart=(string)($context['reference_start']??date('Y-01-01'));
$attendanceCurrent=(float)($metrics['attendance_percentage']??0);
$attendanceRecords=(int)($metrics['total_records']??0);
$occurrencesCurrent=(int)($metrics['total_occurrences']??0);
$activeFollowerIds=[];
foreach($users as $follower){if(($follower['status']??'')==='ACTIVE')$activeFollowerIds[]=(int)$follower['user_id'];}

$renderPlanFields=function(string $prefix, ?array $savedPlan=null) use($objectiveOptions,$strategyOptions,$attendanceCurrent,$attendanceRecords,$occurrencesCurrent,$referenceStart): void {
  $selectedMetric=(string)($savedPlan['target_metric']??'NONE');
  $target=(string)($savedPlan['target_value']??'');
?>
  <div class="student-monitoring-section-title student-monitoring-field--wide"><div><i data-lucide="target"></i><span><strong>Plano de acompanhamento <b>(obrigatório)</b></strong><small>O acompanhamento e o plano serão criados juntos em uma única operação.</small></span></div></div>
  <div class="student-monitoring-field student-monitoring-field--wide">
    <label for="<?= $prefix ?>-objective">Objetivo principal</label>
    <select id="<?= $prefix ?>-objective" name="objective_code" required>
      <option value="">Selecione um objetivo</option>
      <?php foreach($objectiveOptions as $code=>$label): ?><option value="<?= e((string)$code) ?>" <?= (($savedPlan['objective_code']??'')===$code)?'selected':'' ?>><?= e((string)$label) ?></option><?php endforeach; ?>
    </select>
  </div>
  <fieldset class="student-monitoring-assignment student-monitoring-field--wide">
    <legend>Estratégias planejadas — selecione ao menos uma</legend>
    <div class="student-monitoring-strategies">
      <?php foreach($strategyOptions as $code=>$label): ?><label><input type="checkbox" name="strategies[]" value="<?= e((string)$code) ?>" <?= in_array($code,(array)($savedPlan['strategies']??[]),true)?'checked':'' ?>><span><?= e((string)$label) ?></span></label><?php endforeach; ?>
    </div>
  </fieldset>
  <div class="student-monitoring-field student-monitoring-field--wide">
    <label for="<?= $prefix ?>-metric">Indicador da meta</label>
    <select id="<?= $prefix ?>-metric" name="target_metric" data-monitoring-metric>
      <option value="NONE" <?= $selectedMetric==='NONE'?'selected':'' ?>>Sem meta numérica — acompanhar pelas observações</option>
      <option value="ATTENDANCE" <?= $selectedMetric==='ATTENDANCE'?'selected':'' ?>>Frequência do aluno</option>
      <option value="OCCURRENCES" <?= $selectedMetric==='OCCURRENCES'?'selected':'' ?>>Quantidade de ocorrências</option>
    </select>
    <small>Ao escolher um indicador, a situação atual do aluno aparece como referência para definir a meta.</small>
  </div>
  <div class="student-monitoring-goal-context student-monitoring-field--wide" data-monitoring-context="ATTENDANCE" <?= $selectedMetric==='ATTENDANCE'?'':'hidden' ?>>
    <div class="student-monitoring-goal-context__current"><span>Frequência atual de referência</span><?php if($attendanceRecords>0): ?><strong><?= number_format($attendanceCurrent,1,',','.') ?>%</strong><small><?= $attendanceRecords ?> registro(s) desde <?= date('d/m/Y',strtotime($referenceStart)) ?>.</small><?php else: ?><strong>Sem dados</strong><small>Ainda não há registros suficientes. A meta poderá ser definida mesmo assim.</small><?php endif; ?></div>
    <div class="student-monitoring-field"><label>Frequência desejada <span>(opcional)</span></label><div class="student-monitoring-goal-input"><input type="number" min="0" max="100" step="0.1" data-monitoring-target="ATTENDANCE" value="<?= $selectedMetric==='ATTENDANCE'?e($target):'' ?>" placeholder="Ex.: 90"><span>%</span></div><small>Use o percentual atual acima como base.</small></div>
  </div>
  <div class="student-monitoring-goal-context student-monitoring-field--wide" data-monitoring-context="OCCURRENCES" <?= $selectedMetric==='OCCURRENCES'?'':'hidden' ?>>
    <div class="student-monitoring-goal-context__current"><span>Ocorrências atuais de referência</span><strong><?= $occurrencesCurrent ?></strong><small>Total registrado desde <?= date('d/m/Y',strtotime($referenceStart)) ?>.</small></div>
    <div class="student-monitoring-field"><label>Quantidade máxima desejada <span>(opcional)</span></label><input type="number" min="0" step="1" data-monitoring-target="OCCURRENCES" value="<?= $selectedMetric==='OCCURRENCES'?e($target):'' ?>" placeholder="Ex.: 0"><small>Para não haver novas ocorrências, use 0.</small></div>
  </div>
  <input type="hidden" name="target_value" data-monitoring-target-hidden value="<?= e($target) ?>">
  <div class="student-monitoring-field student-monitoring-field--wide"><label for="<?= $prefix ?>-notes">Observações do plano <span>(opcional)</span></label><textarea id="<?= $prefix ?>-notes" name="notes" rows="3" placeholder="Descreva como e com que frequência as estratégias serão realizadas."><?= e((string)($savedPlan['notes']??'')) ?></textarea></div>
<?php };
?>
<section id="studentMonitoring" class="intelligence-panel panel-hover student-monitoring-panel" data-intelligence-prefill="<?= $hasIntelligencePrefill?'1':'0' ?>">
  <div class="intelligence-panel__header student-monitoring-panel__header">
    <div><span class="intelligence-panel__icon"><i data-lucide="user-round-check"></i></span><div><h3>Acompanhamento</h3><p>Período, motivo, participantes e plano em um único processo.</p></div></div>
    <a class="student-monitoring-panel__link" href="<?= base_url('acompanhamentos') ?>"><i data-lucide="users"></i> Meus acompanhamentos</a>
  </div>
  <?php if($monitoringSuccess): ?><?php component('base/alert',['type'=>'success','message'=>(string)$monitoringSuccess]); ?><?php endif; ?>
  <?php if($monitoringError): ?><?php component('base/alert',['type'=>'danger','message'=>(string)$monitoringError]); ?><?php endif; ?>

  <?php if($cases): ?>
    <div class="student-monitoring-cases-overview">
      <div class="student-monitoring-cases-overview__header">
        <div><strong>Casos de acompanhamento</strong><small>Selecione um caso para visualizar seu plano, participantes, ações e inteligência específica.</small></div>
        <div class="student-monitoring-cases-overview__counts"><span><?= $activeCasesCount ?> ativo(s)</span><span><?= $concludedCasesCount ?> concluído(s)</span></div>
      </div>
      <div class="student-monitoring-cases-list" role="list">
        <?php foreach($cases as $case):
          $caseId=(int)($case['id']??0);
          $caseActive=(bool)($case['is_active']??false);
          $caseStatus=(string)($case['display_status']??($caseActive?'IN_PROGRESS':'CONCLUDED'));
          $caseStatusLabel=(string)($case['display_status_label']??($caseActive?'Em andamento':'Concluído'));
          $caseSelected=(bool)($case['is_selected']??false);
          $caseTitle=trim((string)($case['case_title']??'')) ?: ((string)($case['reason']??'Acompanhamento'));
        ?>
          <a role="listitem" class="student-monitoring-case-card <?= $caseSelected?'is-selected':'' ?>" href="<?= base_url('alunos/perfil') ?>?id=<?= (int)($student['id']??0) ?>&case_id=<?= $caseId ?>#studentMonitoring" aria-current="<?= $caseSelected?'true':'false' ?>">
            <span class="student-monitoring-case-card__status <?= $caseActive?'is-active':'is-concluded' ?> status-<?= strtolower(e($caseStatus)) ?>"><?= e($caseStatusLabel) ?></span>
            <strong><?= e($caseTitle) ?></strong>
            <small><?= e((string)($case['reason']??'Motivo não informado')) ?></small>
            <span class="student-monitoring-case-card__meta"><i data-lucide="calendar-range"></i> <?= !empty($case['start_date'])?date('d/m/Y',strtotime((string)$case['start_date'])):'—' ?> até <?= !empty($case['end_date'])?date('d/m/Y',strtotime((string)$case['end_date'])):'—' ?></span>
            <?php if($caseActive): ?>
              <span class="student-monitoring-case-card__meta"><i data-lucide="users"></i> <?= (int)($case['active_followers']??0) ?> acompanhante(s) ativo(s)</span>
            <?php else: ?>
              <?php $caseFollowers=(array)($case['followers']??[]); ?>
              <span class="student-monitoring-case-card__meta student-monitoring-case-card__participants"><i data-lucide="user-round-check"></i> <?= $caseFollowers?e(implode(', ',$caseFollowers)):'Participantes não identificados' ?></span>
              <?php if(!empty($case['closed_by_name'])||!empty($case['closed_at'])): ?>
                <span class="student-monitoring-case-card__meta"><i data-lucide="check-circle-2"></i> Encerrado<?= !empty($case['closed_by_name'])?' por '.e((string)$case['closed_by_name']):'' ?><?= !empty($case['closed_at'])?' em '.date('d/m/Y',strtotime((string)$case['closed_at'])):'' ?></span>
              <?php endif; ?>
            <?php endif; ?>
            <span class="student-monitoring-case-card__open"><i data-lucide="arrow-right"></i> <?= $caseSelected?'Caso selecionado':'Abrir caso' ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if(!$monitoring): ?>
    <div class="student-monitoring-empty">
      <p>Este aluno ainda não possui casos de acompanhamento.</p>
      <button type="button" class="btn-primary" data-monitoring-open><i data-lucide="user-plus"></i> Acompanhar aluno</button>
    </div>
    <form method="post" action="<?= base_url('acompanhamentos/iniciar') ?>" class="student-monitoring-form" data-monitoring-form hidden>
      <input type="hidden" name="student_id" value="<?= (int)($student['id']??0) ?>">
      <?php if($hasIntelligencePrefill): ?><input type="hidden" name="alert_type" value="<?= e((string)($intelligencePrefill['alert_type']??'')) ?>"><input type="hidden" name="alert_key" value="<?= e((string)($intelligencePrefill['alert_key']??'')) ?>"><?php endif; ?>
      <?php if(!$canAssign): ?><input type="hidden" name="follow_self" value="1"><?php else: ?><label class="student-monitoring-follow-self student-monitoring-field--wide"><input type="checkbox" name="follow_self" value="1"><span>Eu também acompanharei este aluno</span></label><?php endif; ?>
      <div class="student-monitoring-section-title student-monitoring-field--wide"><div><i data-lucide="calendar-range"></i><span><strong>Dados do acompanhamento</strong><small>Preencha todos os dados para iniciar o vínculo e o plano juntos.</small></span></div></div>
      <div class="student-monitoring-field student-monitoring-field--wide"><label for="new-case-title">Título do caso <span>(opcional)</span></label><input id="new-case-title" type="text" name="case_title" maxlength="180" value="<?= e((string)($intelligencePrefill['case_title']??'')) ?>" placeholder="Ex.: Recuperação de aprendizagem em Física"></div>
      <div class="student-monitoring-field"><label for="new-start">Início</label><input id="new-start" type="date" name="start_date" value="<?= date('Y-m-d') ?>" required></div>
      <div class="student-monitoring-field"><label for="new-end">Fim</label><input id="new-end" type="date" name="end_date" required></div>
      <div class="student-monitoring-field student-monitoring-field--wide"><label for="new-reason">Motivo</label><select id="new-reason" name="reason_code" required><option value="">Selecione um motivo</option><?php foreach($reasonOptions as $code=>$label): ?><option value="<?= e((string)$code) ?>" <?= (($intelligencePrefill['reason_code']??'')===$code)?'selected':'' ?>><?= e((string)$label) ?></option><?php endforeach; ?></select></div>
      <div class="student-monitoring-field student-monitoring-field--wide"><label for="new-details">Detalhes complementares <span>(opcional)</span></label><textarea id="new-details" name="reason_details" rows="2"><?= e((string)($intelligencePrefill['reason_details']??'')) ?></textarea></div>
      <?php if($canAssign&&$teachers): ?><fieldset class="student-monitoring-assignment student-monitoring-field--wide"><legend>Outros professores que receberão este mesmo plano inicial</legend><div class="student-monitoring-teachers"><?php foreach($teachers as $teacher): if(in_array((int)$teacher['id'],$activeFollowerIds,true))continue; ?><label><input type="checkbox" name="teacher_ids[]" value="<?= (int)$teacher['id'] ?>"><span><?= e((string)$teacher['name']) ?></span></label><?php endforeach; ?></div></fieldset><?php endif; ?>
      <?php $renderPlanFields('new',$prefillPlan); ?>
      <div class="student-monitoring-actions student-monitoring-field--wide"><button type="submit" class="btn-primary"><i data-lucide="check-circle-2"></i> Iniciar acompanhamento com plano</button><button type="button" class="btn-secondary" data-monitoring-cancel>Cancelar</button></div>
    </form>
  <?php else: ?>
    <div class="student-monitoring-case-heading"><span>Caso de acompanhamento</span><strong><?= e((string)($monitoring['case_title']??$monitoring['reason']??'Acompanhamento')) ?></strong><small>Cada caso possui problema, plano, ações e participantes próprios.</small></div>
    <div class="student-monitoring-summary">
      <div><span>Período</span><strong><?= date('d/m/Y',strtotime((string)$displayLink['start_date'])) ?> a <?= date('d/m/Y',strtotime((string)$displayLink['end_date'])) ?></strong></div>
      <div><span>Situação</span><strong class="student-monitoring-status <?= $isConcluded?'is-concluded':'' ?>"><i data-lucide="<?= $isConcluded?'check-circle-2':'activity' ?>"></i> <?= $isConcluded?'Acompanhamento concluído':'Em acompanhamento com plano' ?></strong></div>
      <div class="student-monitoring-summary__reason"><span>Motivo</span><strong><?= e((string)$displayLink['reason']) ?></strong></div>
    </div>
    <div class="student-monitoring-followers"><div class="student-monitoring-section-title"><div><i data-lucide="users-round"></i><span><strong><?= $isConcluded?'Quem realizou o acompanhamento':'Pessoas acompanhadoras' ?></strong><small><?= $isConcluded?'Participantes preservados no histórico deste caso.':'Somente vínculos ativos são exibidos.' ?></small></span></div></div><div class="student-monitoring-followers__list"><?php foreach($users as $u): if(!$isConcluded&&($u['status']??'')!=='ACTIVE')continue; ?><span class="<?= ($u['status']??'')==='ACTIVE'?'is-active':'is-concluded' ?>"><i data-lucide="user"></i><?= e((string)$u['name']) ?><?php if($isConcluded&&!empty($u['ended_at'])): ?><small>até <?= date('d/m/Y',strtotime((string)$u['ended_at'])) ?></small><?php endif; ?><?php if(!$isConcluded&&$canAssign&&(int)$u['user_id']!==$currentUserId): ?><form method="post" action="<?= base_url('acompanhamentos/encerrar') ?>" class="student-monitoring-remove-person" onsubmit="return confirm('Remover esta pessoa do acompanhamento?');"><input type="hidden" name="monitoring_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="case_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="student_id" value="<?= (int)($student['id']??0) ?>"><input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>"><button type="submit" title="Remover"><i data-lucide="x"></i></button></form><?php endif; ?></span><?php endforeach; ?></div></div>
    <?php if($isConcluded&&($selectedClosedBy!==''||$selectedClosedAt!=='')): ?><div class="student-monitoring-completion-summary"><i data-lucide="badge-check"></i><div><strong>Conclusão do acompanhamento</strong><span><?= $selectedClosedBy!==''?'Encerrado por '.e($selectedClosedBy):'Encerramento registrado' ?><?= $selectedClosedAt!==''?' em '.date('d/m/Y \à\s H:i',strtotime($selectedClosedAt)):'' ?>.</span></div></div><?php endif; ?>

    <?php if(!$isFollowing): ?><div class="student-monitoring-readonly-notice"><i data-lucide="eye"></i><div><strong>Visualização do caso</strong><span>Você não participa deste caso. Os dados são exibidos sem liberar ações do vínculo.</span></div></div><?php endif; ?>
    <div class="student-monitoring-maintenance-actions">
      <?php if($isFollowing): ?>
        <button type="button" class="btn-primary" data-plan-toggle><i data-lucide="clipboard-pen-line"></i> Atualizar plano do acompanhamento</button>
        <button type="button" class="btn-secondary" data-extension-toggle><i data-lucide="calendar-plus"></i> Prorrogar acompanhamento</button>
      <?php endif; ?>
      <?php if($canAssign): ?><button type="button" class="btn-secondary" data-companion-toggle><i data-lucide="users-round"></i> Adicionar ao mesmo plano</button><?php endif; ?>
      <button type="button" class="btn-secondary" data-new-case-toggle><i data-lucide="folder-plus"></i> Novo problema e outro plano</button>
    </div>

    <form method="post" action="<?= base_url('acompanhamentos/prorrogar') ?>" class="student-monitoring-form" data-extension-form hidden>
      <input type="hidden" name="monitoring_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="case_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="student_id" value="<?= (int)($student['id']??0) ?>">
      <div class="student-monitoring-section-title student-monitoring-field--wide"><div><i data-lucide="calendar-plus"></i><span><strong>Prorrogar acompanhamento</strong><small>A data anterior será preservada no histórico.</small></span></div></div>
      <div class="student-monitoring-field"><label>Data final atual</label><input type="date" value="<?= e((string)$displayLink['end_date']) ?>" disabled></div>
      <div class="student-monitoring-field"><label>Nova data final</label><input type="date" name="new_end_date" min="<?= date('Y-m-d',strtotime((string)$displayLink['end_date'].' +1 day')) ?>" required></div>
      <div class="student-monitoring-field student-monitoring-field--wide"><label>Motivo da prorrogação</label><textarea name="extension_reason" rows="3" required placeholder="Explique por que o acompanhamento precisa continuar."></textarea></div>
      <div class="student-monitoring-actions student-monitoring-field--wide"><button type="submit" class="btn-primary"><i data-lucide="check"></i> Confirmar prorrogação</button><button type="button" class="btn-secondary" data-extension-cancel>Cancelar</button></div>
    </form>
    <?php if(!empty($extensions)): ?><div class="student-monitoring-extension-history"><small>Histórico de prorrogações</small><?php foreach($extensions as $extension): ?><p><strong><?= date('d/m/Y',strtotime((string)$extension['previous_end_date'])) ?> → <?= date('d/m/Y',strtotime((string)$extension['new_end_date'])) ?></strong> · <?= e((string)$extension['extended_by_name']) ?><br><?= e((string)$extension['reason']) ?></p><?php endforeach; ?></div><?php endif; ?>

    <form method="post" action="<?= base_url('acompanhamentos/plano/salvar') ?>" class="student-monitoring-form" data-plan-form hidden>
      <input type="hidden" name="monitoring_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="case_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="student_id" value="<?= (int)($student['id']??0) ?>">
      <?php $renderPlanFields('current',$plan); ?>
      <div class="student-monitoring-actions student-monitoring-field--wide"><button type="submit" class="btn-primary"><i data-lucide="save"></i> Salvar atualização do plano</button><button type="button" class="btn-secondary" data-plan-cancel>Cancelar</button></div>
    </form>

    <?php if($canAssign): ?>
      <?php $availableTeachers=array_values(array_filter($teachers,fn($teacher)=>!in_array((int)$teacher['id'],$activeFollowerIds,true))); ?>
      <form method="post" action="<?= base_url('acompanhamentos/acompanhantes/adicionar') ?>" class="student-monitoring-form" data-companion-form hidden>
        <input type="hidden" name="monitoring_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="case_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="student_id" value="<?= (int)($student['id']??0) ?>">
        <div class="student-monitoring-section-title student-monitoring-field--wide"><div><i data-lucide="user-plus"></i><span><strong>Adicionar ao acompanhamento atual</strong><small>O participante entrará neste mesmo caso e compartilhará o plano atual, sem criar outro problema ou duplicar dados.</small></span></div></div>
        <div class="student-monitoring-section-title student-monitoring-field--wide"><div><i data-lucide="calendar-range"></i><span><strong>Dados do acompanhamento</strong><small>Defina o período do novo acompanhante dentro do acompanhamento atual.</small></span></div></div>
        <div class="student-monitoring-field"><label for="companion-start">Início</label><input id="companion-start" type="date" name="start_date" value="<?= date('Y-m-d') ?>" min="<?= e((string)$monitoring['start_date']) ?>" max="<?= e((string)$monitoring['end_date']) ?>" required></div>
        <div class="student-monitoring-field"><label for="companion-end">Fim</label><input id="companion-end" type="date" name="end_date" value="<?= e((string)$monitoring['end_date']) ?>" min="<?= date('Y-m-d') ?>" required></div>
        <div class="student-monitoring-section-title student-monitoring-field--wide"><div><i data-lucide="notebook-pen"></i><span><strong>Observações internas do vínculo</strong><small>Campo opcional, visível apenas como informação do participante. O problema, motivo e plano continuam sendo os mesmos do caso.</small></span></div></div>
        <div class="student-monitoring-field student-monitoring-field--wide"><label for="companion-notes">Observações internas <span>(opcional)</span></label><textarea id="companion-notes" name="internal_notes" rows="3" maxlength="3000" placeholder="Ex.: professor incluído para acompanhar a rotina em determinada disciplina."></textarea></div>
        <div class="student-monitoring-section-title student-monitoring-field--wide"><div><i data-lucide="contact-round"></i><span><strong>Dados do participante</strong><small>Selecione quem será incluído neste acompanhamento.</small></span></div></div>
        <?php if($availableTeachers): ?><fieldset class="student-monitoring-assignment student-monitoring-field--wide"><legend>Selecione um ou mais professores</legend><div class="student-monitoring-teachers"><?php foreach($availableTeachers as $teacher): ?><label><input type="checkbox" name="teacher_ids[]" value="<?= (int)$teacher['id'] ?>"><span><?= e((string)$teacher['name']) ?></span></label><?php endforeach; ?></div></fieldset><?php else: ?><div class="student-monitoring-actions-empty student-monitoring-field--wide"><p>Todos os professores disponíveis já participam deste acompanhamento.</p></div><?php endif; ?>
        <div class="student-monitoring-actions student-monitoring-field--wide"><?php if($availableTeachers): ?><button type="submit" class="btn-primary"><i data-lucide="user-plus"></i> Adicionar ao mesmo caso</button><?php endif; ?><button type="button" class="btn-secondary" data-companion-cancel>Cancelar</button></div>
      </form>
    <?php endif; ?>
    <form method="post" action="<?= base_url('acompanhamentos/iniciar') ?>" class="student-monitoring-form" data-new-case-form hidden>
      <input type="hidden" name="student_id" value="<?= (int)($student['id']??0) ?>">
      <?php if($hasIntelligencePrefill): ?><input type="hidden" name="alert_type" value="<?= e((string)($intelligencePrefill['alert_type']??'')) ?>"><input type="hidden" name="alert_key" value="<?= e((string)($intelligencePrefill['alert_key']??'')) ?>"><?php endif; ?>
      <?php if(!$canAssign): ?><input type="hidden" name="follow_self" value="1"><?php else: ?><label class="student-monitoring-follow-self student-monitoring-field--wide"><input type="checkbox" name="follow_self" value="1"><span>Eu também acompanharei este novo caso</span></label><?php endif; ?>
      <div class="student-monitoring-section-title student-monitoring-field--wide"><div><i data-lucide="folder-plus"></i><span><strong>Novo acompanhamento para outro problema</strong><small>Este formulário criará um caso independente, com motivo, plano, período, ações e participantes próprios.</small></span></div></div>
      <div class="student-monitoring-field student-monitoring-field--wide"><label for="case-title-active">Título do caso <span>(opcional)</span></label><input id="case-title-active" type="text" name="case_title" maxlength="180" value="<?= e((string)($intelligencePrefill['case_title']??'')) ?>" placeholder="Ex.: Dificuldade de aprendizagem em Matemática"></div>
      <div class="student-monitoring-field"><label for="case-start-active">Início</label><input id="case-start-active" type="date" name="start_date" value="<?= date('Y-m-d') ?>" required></div>
      <div class="student-monitoring-field"><label for="case-end-active">Fim</label><input id="case-end-active" type="date" name="end_date" required></div>
      <div class="student-monitoring-field student-monitoring-field--wide"><label for="case-reason-active">Problema/motivo</label><select id="case-reason-active" name="reason_code" required><option value="">Selecione um motivo</option><?php foreach($reasonOptions as $code=>$label): ?><option value="<?= e((string)$code) ?>" <?= (($intelligencePrefill['reason_code']??'')===$code)?'selected':'' ?>><?= e((string)$label) ?></option><?php endforeach; ?></select></div>
      <div class="student-monitoring-field student-monitoring-field--wide"><label for="case-details-active">Detalhes do problema <span>(opcional)</span></label><textarea id="case-details-active" name="reason_details" rows="2" placeholder="Descreva o foco específico deste novo acompanhamento."><?= e((string)($intelligencePrefill['reason_details']??'')) ?></textarea></div>
      <?php $renderPlanFields('new-case',$prefillPlan); ?>
      <?php if($canAssign&&$teachers): ?><fieldset class="student-monitoring-assignment student-monitoring-field--wide"><legend>Participantes deste novo caso</legend><div class="student-monitoring-teachers"><?php foreach($teachers as $teacher): ?><label><input type="checkbox" name="teacher_ids[]" value="<?= (int)$teacher['id'] ?>"><span><?= e((string)$teacher['name']) ?></span></label><?php endforeach; ?></div></fieldset><?php endif; ?>
      <div class="student-monitoring-actions student-monitoring-field--wide"><button type="submit" class="btn-primary"><i data-lucide="check-circle-2"></i> Criar caso com outro plano</button><button type="button" class="btn-secondary" data-new-case-cancel>Cancelar</button></div>
    </form>
    <?php if($isFollowing): ?><form method="post" action="<?= base_url('acompanhamentos/encerrar') ?>" class="student-monitoring-join" onsubmit="return confirm('Encerrar sua participação neste caso? O caso, o plano e as ações continuarão disponíveis aos demais participantes.');"><input type="hidden" name="monitoring_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="case_id" value="<?= (int)$monitoring['id'] ?>"><input type="hidden" name="student_id" value="<?= (int)($student['id']??0) ?>"><button type="submit" class="btn-secondary"><i data-lucide="user-minus"></i> Encerrar acompanhamento</button></form><?php endif; ?>
  <?php endif; ?>
</section>
<script>
(()=>{
  document.querySelectorAll('.student-monitoring-panel').forEach(panel=>{
    const openButton=panel.querySelector('[data-monitoring-open]');
    const cancelButton=panel.querySelector('[data-monitoring-cancel]');
    const emptyState=panel.querySelector('.student-monitoring-empty');
    const startForm=panel.querySelector('[data-monitoring-form][hidden]');
    const intelligencePrefill=panel.dataset.intelligencePrefill==='1';
    if(intelligencePrefill){
      const target=panel.querySelector('[data-new-case-form]')||startForm;
      if(target){target.hidden=false;if(emptyState)emptyState.hidden=true;panel.querySelector('[data-new-case-toggle]')?.setAttribute('hidden','hidden');setTimeout(()=>target.querySelector('input:not([type=hidden]),select,textarea')?.focus(),0);}
    }

    if(openButton&&startForm){
      openButton.addEventListener('click',()=>{
        startForm.hidden=false;
        if(emptyState)emptyState.hidden=true;
        const firstField=startForm.querySelector('input:not([type="hidden"]), select, textarea');
        if(firstField)firstField.focus();
      });
    }

    if(cancelButton&&startForm){
      cancelButton.addEventListener('click',()=>{
        startForm.hidden=true;
        if(emptyState)emptyState.hidden=false;
        if(openButton)openButton.focus();
      });
    }
  });

  document.querySelectorAll('.student-monitoring-panel [data-monitoring-form], .student-monitoring-panel [data-plan-form], .student-monitoring-panel [data-companion-form], .student-monitoring-panel [data-new-case-form]').forEach(form=>{
    const metric=form.querySelector('[data-monitoring-metric]'); if(!metric)return;
    const hidden=form.querySelector('[data-monitoring-target-hidden]');
    const update=()=>{form.querySelectorAll('[data-monitoring-context]').forEach(box=>{const active=box.dataset.monitoringContext===metric.value;box.hidden=!active;const input=box.querySelector('[data-monitoring-target]');if(input){input.disabled=!active;if(active)hidden.value=input.value;}});if(metric.value==='NONE')hidden.value='';};
    form.querySelectorAll('[data-monitoring-target]').forEach(input=>input.addEventListener('input',()=>{if(input.dataset.monitoringTarget===metric.value)hidden.value=input.value;}));
    metric.addEventListener('change',update); form.addEventListener('submit',(event)=>{update();if(form.querySelectorAll('input[name="strategies[]"]:checked').length===0){event.preventDefault();alert('Selecione ao menos uma estratégia para iniciar o acompanhamento.');}}); update();
  });

  document.querySelectorAll('.student-monitoring-panel').forEach(panel=>{
    const bindToggle=(openSelector,formSelector,cancelSelector)=>{
      const open=panel.querySelector(openSelector), form=panel.querySelector(formSelector), cancel=panel.querySelector(cancelSelector);
      if(open&&form)open.addEventListener('click',()=>{form.hidden=false;open.hidden=true;form.querySelector('select,input,textarea')?.focus();});
      if(cancel&&form)cancel.addEventListener('click',()=>{form.hidden=true;if(open){open.hidden=false;open.focus();}});
    };
    bindToggle('[data-plan-toggle]','[data-plan-form]','[data-plan-cancel]');
    bindToggle('[data-extension-toggle]','[data-extension-form]','[data-extension-cancel]');
    bindToggle('[data-companion-toggle]','[data-companion-form]','[data-companion-cancel]');
    bindToggle('[data-new-case-toggle]','[data-new-case-form]','[data-new-case-cancel]');
  });

})();
</script>
