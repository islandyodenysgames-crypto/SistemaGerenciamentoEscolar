<?php
$calendar = is_array($calendar ?? null) ? $calendar : [];
$year = (int) ($calendar['year'] ?? date('Y'));
$month = (int) ($calendar['month'] ?? date('n'));
$events = is_array($calendar['events'] ?? null) ? $calendar['events'] : [];
$upcoming = is_array($calendar['upcoming'] ?? null) ? $calendar['upcoming'] : [];
$monthNames = [1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'];
$first = sprintf('%04d-%02d-01', $year, $month);
$days = (int) date('t', strtotime($first));
$offset = (int) date('w', strtotime($first));
$byDay = [];
foreach ($events as $event) {
    $start = new DateTime((string) $event['start_date']);
    $end = new DateTime((string) ($event['end_date'] ?: $event['start_date']));
    for ($d = clone $start; $d <= $end; $d->modify('+1 day')) {
        $byDay[$d->format('Y-m-d')][] = $event;
    }
}
$typeLabels=['ASSESSMENT'=>'Avaliação','MEETING'=>'Reunião','COUNCIL'=>'Conselho','EVENT'=>'Evento','TRAINING'=>'Formação','HOLIDAY'=>'Feriado','DEADLINE'=>'Prazo','OTHER'=>'Outro'];
$typeClasses=['ASSESSMENT'=>'assessment','MEETING'=>'meeting','COUNCIL'=>'council','EVENT'=>'event','TRAINING'=>'training','HOLIDAY'=>'holiday','DEADLINE'=>'deadline','OTHER'=>'other'];
$featuredCount = count(array_filter($upcoming, fn($e) => !empty($e['featured'])));
$shortDate = static fn(string $date): string => date('d/m/Y', strtotime($date));
$shortTime = static fn($time): string => substr((string) ($time ?? ''), 0, 5);
?>
<section class="card dashboard-school-calendar">
<header class="dashboard-school-calendar__header"><div><span class="dashboard-school-calendar__eyebrow"><i data-lucide="calendar-days"></i>Agenda escolar</span><h2>Calendário Escolar</h2><p>Compromissos, prazos e datas importantes em um só lugar.</p></div><div class="dashboard-school-calendar__header-actions"><?php if($featuredCount):?><span class="dashboard-calendar-featured-count"><i data-lucide="star"></i><?=$featuredCount?> destaque(s)</span><?php endif;?><a class="btn-secondary" href="<?=base_url('calendario')?>">Abrir calendário <i data-lucide="arrow-up-right"></i></a></div></header>
<div class="dashboard-school-calendar__layout">
<div class="dashboard-mini-calendar"><div class="dashboard-mini-calendar__month"><div><span>Mês atual</span><strong><?=e($monthNames[$month])?></strong></div><b><?=$year?></b></div><div class="dashboard-mini-calendar__week"><?php foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $w):?><span><?=$w?></span><?php endforeach;?></div><div class="dashboard-mini-calendar__grid"><?php for($i=0;$i<$offset;$i++):?><span class="is-empty"></span><?php endfor;?><?php for($day=1;$day<=$days;$day++):$date=sprintf('%04d-%02d-%02d',$year,$month,$day);$dayEvents=$byDay[$date]??[];$today=$date===date('Y-m-d');?><a href="<?=base_url('calendario')?>#lista-eventos" class="dashboard-mini-calendar__day <?=!empty($dayEvents)?'has-event':''?> <?=$today?'is-today':''?>" title="<?=!empty($dayEvents)?e(implode(' • ',array_column($dayEvents,'title'))):''?>"><span class="dashboard-mini-calendar__number"><?=$day?></span><?php if(!empty($dayEvents)):?><div class="dashboard-mini-calendar__titles"><?php foreach(array_slice($dayEvents,0,2) as $ev):?><span class="dashboard-mini-calendar__event-title type-<?=e($typeClasses[$ev['type']]??'other')?>"><i></i><?=e((string)$ev['title'])?></span><?php endforeach;?><?php if(count($dayEvents)>2):?><small>+<?=count($dayEvents)-2?> evento(s)</small><?php endif;?></div><?php endif;?></a><?php endfor;?></div></div>
<div class="dashboard-school-calendar__upcoming"><div class="dashboard-school-calendar__upcoming-title"><div><span>Em seguida</span><strong>Próximos eventos</strong></div><span><?=count($upcoming)?> na agenda</span></div><?php if(empty($upcoming)):?><div class="dashboard-calendar-empty"><i data-lucide="calendar-check"></i><strong>Agenda livre</strong><p>Nenhum evento futuro cadastrado.</p></div><?php endif;?><?php foreach($upcoming as $event):
$startDate=(string)$event['start_date'];$endDate=(string)($event['end_date']?:$event['start_date']);$startTime=$shortTime($event['start_time']??'');$endTime=$shortTime($event['end_time']??'');$tc=$typeClasses[$event['type']]??'other';$sameDay=$startDate===$endDate;
?><a href="<?=base_url('calendario')?>#evento-<?=(int)$event['id']?>" class="dashboard-calendar-event type-<?=e($tc)?> <?=!empty($event['featured'])?'is-featured':''?>"><div class="dashboard-calendar-event__date-range"><span><small>Início</small><strong><?=$shortDate($startDate)?></strong><?php if(empty($event['all_day'])&&$startTime!==''):?><b><?=$startTime?></b><?php endif;?></span><?php if(!$sameDay||(!empty($endTime)&&$endTime!==$startTime)):?><i data-lucide="arrow-right"></i><span><small>Fim</small><strong><?=$shortDate($endDate)?></strong><?php if(empty($event['all_day'])&&$endTime!==''):?><b><?=$endTime?></b><?php endif;?></span><?php endif;?></div><div class="dashboard-calendar-event__content"><div class="dashboard-calendar-event__meta"><span class="dashboard-calendar-event__type"><?=e($typeLabels[$event['type']]??'Outro')?></span><?php if(!empty($event['featured'])):?><span class="dashboard-calendar-event__featured"><i data-lucide="star"></i>Destaque</span><?php endif;?></div><strong><?=e((string)$event['title'])?></strong><small><i data-lucide="clock"></i><?=!empty($event['all_day'])?'Dia inteiro':e(($startTime!==''?$startTime:'--:--').($endTime!==''?' às '.$endTime:''))?></small><?php if(!empty($event['location'])):?><small><i data-lucide="map-pin"></i><?=e((string)$event['location'])?></small><?php endif;?></div><i data-lucide="chevron-right"></i></a><?php endforeach;?></div>
</div><footer class="dashboard-calendar-legend"><?php foreach(['assessment'=>'Avaliação','meeting'=>'Reunião','event'=>'Evento','training'=>'Formação','holiday'=>'Feriado','deadline'=>'Prazo'] as $c=>$l):?><span><i class="type-<?=$c?>"></i><?=$l?></span><?php endforeach;?></footer></section>
