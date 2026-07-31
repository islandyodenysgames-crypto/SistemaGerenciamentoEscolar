<?php
$timelineEvents = $timelineEvents ?? [];
$icons = ['OCCURRENCE'=>'clipboard-list','ACTION'=>'message-square-more','ATTENDANCE'=>'calendar-check','NOTIFICATION'=>'bell-ring','INSIGHT'=>'lightbulb'];
?>
<section class="card student-timeline-card" id="studentTimeline">
    <div class="student-timeline-header"><div><h3>Linha do tempo do aluno</h3><p>Ocorrências, providências, frequência e alertas em ordem cronológica.</p></div><span class="student-timeline-total"><?= count($timelineEvents) ?> evento(s)</span></div>
    <?php if ($timelineEvents === []): ?><div class="activity-empty">Nenhum evento encontrado.</div><?php else: ?>
    <div class="student-timeline-list">
        <?php foreach ($timelineEvents as $event): $type=strtoupper((string)($event['event_type']??'OTHER')); $severity=strtolower((string)($event['severity']??'info')); $occurredAt=strtotime((string)($event['occurred_at']??'')); ?>
        <article class="student-timeline-item severity-<?= e($severity) ?>">
            <div class="student-timeline-marker"><i data-lucide="<?= e($icons[$type]??'circle') ?>"></i></div>
            <div class="student-timeline-content">
                <div class="student-timeline-meta"><span><?= e($event['category']??'Evento') ?></span><time><?= $occurredAt?date('d/m/Y H:i',$occurredAt):'Data não informada' ?></time></div>
                <h4><?= e($event['title']??'Evento') ?></h4>
                <?php if(!empty($event['description'])): ?><p><?= nl2br(e((string)$event['description'])) ?></p><?php endif; ?>
                <div class="student-timeline-details">
                    <?php if(!empty($event['subject_name'])): ?><span><i data-lucide="book-open"></i><?= e($event['subject_name']) ?></span><?php endif; ?>
                    <?php if(!empty($event['author_name'])): ?><span><i data-lucide="user-round"></i><?= e($event['author_name']) ?></span><?php endif; ?>
                    <?php if(!empty($event['status'])): ?><span><i data-lucide="circle-dot"></i><?= e($event['status']) ?></span><?php endif; ?>
                </div>
                <?php if(!empty($event['action_url'])&&!empty($event['action_label'])): ?><a class="student-timeline-action" href="<?= e($event['action_url']) ?>"><?= e($event['action_label']) ?><i data-lucide="arrow-right"></i></a><?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div><?php endif; ?>
</section>
