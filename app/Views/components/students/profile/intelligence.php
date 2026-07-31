<?php
$studentIntelligence = (array) ($studentIntelligence ?? []);
$risk = (array) ($studentIntelligence['risk'] ?? ['score' => 0, 'level' => 'LOW']);
$attendance = (array) ($studentIntelligence['attendance'] ?? []);
$occurrences = (array) ($studentIntelligence['occurrences'] ?? []);
$monitoringTimeline = (array) ($studentMonitoring['monitoring_timeline'] ?? []);
$timelineInitialLimit = 8;
$timelineTotal = count($monitoringTimeline);
$activeFollowersCount = (int) ($studentMonitoring['active_followers_count'] ?? 0);
$currentRecommendation = (array) ($studentMonitoring['current_recommendation'] ?? []);
$hasMonitoring = !empty($studentMonitoring['monitoring']);
$riskLevel = strtoupper((string) ($risk['level'] ?? 'LOW'));
$riskScore = (int) ($risk['score'] ?? 0);
$riskLabels = ['LOW' => 'Baixo', 'MODERATE' => 'Atenção', 'HIGH' => 'Alto', 'CRITICAL' => 'Crítico'];
$riskIcons = ['LOW' => 'shield-check', 'MODERATE' => 'shield-alert', 'HIGH' => 'triangle-alert', 'CRITICAL' => 'siren'];
$historicalTrends = (array) ($studentIntelligence['historical_trends'] ?? []);
$trendItems = [
    ['key' => 'frequency', 'label' => 'Frequência'],
    ['key' => 'occurrences', 'label' => 'Ocorrências'],
    ['key' => 'risk', 'label' => 'Risco'],
    ['key' => 'interventions', 'label' => 'Intervenções'],
];
$trendTone = static fn(string $status): string => match ($status) {
    'IMPROVING', 'ADEQUATE' => 'positive',
    'WORSENING', 'DELAYED', 'NO_MONITORING' => 'negative',
    'STABLE' => 'stable',
    default => 'insufficient',
};
$confidenceLabels = ['LOW' => 'baixa', 'MEDIUM' => 'média', 'HIGH' => 'alta'];
$prediction = (array) ($studentIntelligence['prediction'] ?? []);
$predictionStatus = strtoupper((string) ($prediction['status'] ?? 'INSUFFICIENT'));
$predictionTone = match ($predictionStatus) {
    'IMPROVEMENT' => 'positive',
    'DETERIORATION' => 'attention',
    'CRITICAL_ESCALATION' => 'critical',
    'STABLE' => 'stable',
    default => 'insufficient',
};
$predictionIcon = match ($predictionStatus) {
    'IMPROVEMENT' => 'trending-up',
    'DETERIORATION' => 'triangle-alert',
    'CRITICAL_ESCALATION' => 'siren',
    'STABLE' => 'arrow-right',
    default => 'hourglass',
};

$attendanceTrend = strtoupper((string) (($attendance['trend']['status'] ?? 'STABLE')));
$occurrenceTrend = strtoupper((string) (($occurrences['trend']['status'] ?? 'STABLE')));
$openOccurrences = (int) ($occurrences['open'] ?? 0);
$unjustifiedAbsences = (int) ($attendance['unjustified_absences'] ?? 0);

if (!$hasMonitoring) {
    if (in_array($riskLevel, ['HIGH', 'CRITICAL'], true) || $attendanceTrend === 'WORSENING' || $occurrenceTrend === 'WORSENING') {
        $analysisText = 'O aluno não possui acompanhamento ativo e apresenta sinais recentes que exigem atenção. Recomenda-se avaliar a abertura de um acompanhamento e definir a primeira intervenção.';
    } elseif ($openOccurrences > 0 || $unjustifiedAbsences > 0) {
        $analysisText = 'O aluno não possui acompanhamento ativo, mas há registros que merecem observação. Recomenda-se revisar as pendências antes de decidir pela abertura de um acompanhamento.';
    } else {
        $analysisText = 'O aluno não possui acompanhamento ativo e o cenário recente não apresenta sinais prioritários. Recomenda-se manter a observação pelos registros regulares.';
    }
} elseif ($activeFollowersCount === 0) {
    $analysisText = 'O acompanhamento permanece registrado, mas está sem acompanhante ativo. Recomenda-se designar um responsável para dar continuidade às intervenções e ao monitoramento.';
} elseif ($attendanceTrend === 'WORSENING' && ($occurrenceTrend === 'WORSENING' || $openOccurrences > 0)) {
    $analysisText = 'O acompanhamento está ativo, porém a frequência piorou e existem sinais relacionados a ocorrências. Recomenda-se revisar a estratégia e registrar uma nova intervenção.';
} elseif ($attendanceTrend === 'WORSENING') {
    $analysisText = 'O acompanhamento está ativo e os dados indicam piora recente na frequência. Recomenda-se priorizar uma intervenção voltada à infrequência.';
} elseif ($occurrenceTrend === 'WORSENING' || $openOccurrences > 0) {
    $analysisText = 'O acompanhamento está ativo e há ocorrências que ainda exigem atenção. Recomenda-se acompanhar os encaminhamentos e registrar a próxima providência.';
} elseif ($attendanceTrend === 'IMPROVING' && $occurrenceTrend !== 'WORSENING') {
    $analysisText = 'O acompanhamento está ativo e o histórico recente indica evolução positiva. Recomenda-se manter o monitoramento para confirmar a continuidade da melhora.';
} else {
    $analysisText = 'O acompanhamento está ativo e os indicadores permanecem estáveis. Recomenda-se manter as ações planejadas e observar a evolução nos próximos registros.';
}

$eventLabels = [
    'CASE_CREATED' => ['Caso criado', 'folder-plus', 'positive'],
    'MONITORING_STARTED' => ['Acompanhamento iniciado', 'play-circle', 'positive'],
    'MONITORING_RESUMED' => ['Acompanhamento retomado', 'rotate-ccw', 'information'],
    'CASE_REOPENED' => ['Caso reaberto', 'rotate-ccw', 'information'],
    'CASE_CLOSED' => ['Caso encerrado', 'circle-check-big', 'attention'],
    'STATUS_CHANGED' => ['Status alterado', 'refresh-cw', 'information'],
    'PLAN_CREATED' => ['Plano criado', 'notebook-pen', 'positive'],
    'PERIOD_EXTENDED' => ['Período prorrogado', 'calendar-plus', 'information'],
    'ATTACHMENT_ADDED' => ['Anexo adicionado', 'paperclip', 'information'],
    'ATTACHMENT_REMOVED' => ['Anexo removido', 'paperclip-off', 'attention'],
    'PARTICIPANT_ADDED' => ['Acompanhante adicionado', 'user-plus', 'information'],
    'PARTICIPANT_REMOVED' => ['Acompanhante removido', 'user-minus', 'attention'],
    'NO_ACTIVE_FOLLOWERS' => ['Sem acompanhante ativo', 'user-x', 'critical'],
    'ACTION' => ['Intervenção registrada', 'clipboard-check', 'positive'],
    'ACTION_CREATED' => ['Intervenção registrada', 'clipboard-check', 'positive'],
];
?>

<section class="card panel-hover student-intelligence-panel student-intelligence-panel--concise" id="studentIntelligence">
    <header class="student-intelligence-header student-intelligence-header--concise">
        <div>
            <span class="student-intelligence-eyebrow"><i data-lucide="brain-circuit"></i> Inteligência escolar</span>
            <h3>Visão atual do aluno</h3>
            <p>Resumo objetivo, análise do cenário e histórico do acompanhamento.</p>
        </div>
    </header>

    <div class="student-current-overview">
        <article class="student-current-status <?= $activeFollowersCount > 0 ? 'is-active' : 'is-inactive' ?>">
            <div class="student-current-card__heading">
                <span><i data-lucide="activity"></i> Situação atual</span>
            </div>
            <div class="student-current-status__main">
                <i data-lucide="<?= $activeFollowersCount > 0 ? 'users-round' : 'user-x' ?>"></i>
                <div>
                    <strong><?= $activeFollowersCount > 0 ? 'Acompanhamento ativo' : 'Sem acompanhamento ativo' ?></strong>
                    <?php if ($activeFollowersCount > 0): ?><small><?= $activeFollowersCount ?> acompanhante(s) ativo(s)</small><?php endif; ?>
                </div>
            </div>
            <div class="student-current-status__risk risk-<?= e(strtolower($riskLevel)) ?>">
                <i data-lucide="<?= e($riskIcons[$riskLevel] ?? 'shield-check') ?>"></i>
                <span><small>Nível de risco</small><strong><?= e($riskLabels[$riskLevel] ?? 'Baixo') ?></strong></span>
            </div>
            <div class="student-current-status__recommendation">
                <small>Recomendação atual</small>
                <strong><?= e((string) ($currentRecommendation['title'] ?? ($hasMonitoring ? 'Manter o acompanhamento e observar a evolução.' : 'Nenhuma recomendação pendente.'))) ?></strong>
            </div>
        </article>

        <article class="student-current-analysis">
            <div class="student-current-card__heading">
                <span><i data-lucide="sparkles"></i> Análise atual</span>
            </div>
            <p><?= e($analysisText) ?></p>
            <?php if (!empty($currentRecommendation['reason'])): ?>
                <div class="student-current-analysis__guidance">
                    <i data-lucide="lightbulb"></i>
                    <span><?= e((string) $currentRecommendation['reason']) ?></span>
                </div>
            <?php endif; ?>
        </article>
    </div>

    <details class="student-intelligence-explanation is-<?= e(strtolower($riskLevel)) ?>" aria-label="Explicação do nível de risco do aluno">
        <summary>
            <span><i data-lucide="circle-help"></i><strong>Por que esta análise?</strong></span>
            <small>Clique para ver os fatores considerados</small>
            <i data-lucide="chevron-down" class="student-intelligence-explanation__chevron"></i>
        </summary>
        <div class="student-intelligence-explanation__content">
            <?php $studentReasons = (array) ($studentIntelligence['reasons'] ?? []); ?>
            <?php if ($studentReasons !== []): ?>
                <p>O nível <b><?= e(strtolower($riskLabels[$riskLevel] ?? $riskLevel)) ?></b> e a pontuação de <?= $riskScore ?>/100 consideram os seguintes registros:</p>
                <ul><?php foreach (array_slice($studentReasons, 0, 4) as $reason): ?><li><?= e((string) $reason) ?></li><?php endforeach; ?></ul>
            <?php else: ?>
                <p>Não foram encontrados sinais prioritários na janela atual. A classificação permanece baixa com base nos registros disponíveis.</p>
            <?php endif; ?>
            <small>A análise resume dados registrados e regras configuradas; não representa diagnóstico.</small>
        </div>
    </details>

    <section class="student-prediction-panel is-<?= e($predictionTone) ?>">
        <div class="student-prediction-panel__header">
            <div>
                <span><i data-lucide="brain-circuit"></i> Inteligência preditiva</span>
                <h4><?= e((string) ($prediction['headline'] ?? 'Ainda não há dados suficientes para projetar o cenário.')) ?></h4>
                <p><?= e((string) ($prediction['summary'] ?? 'A previsão será disponibilizada quando o histórico possuir snapshots em dias diferentes.')) ?></p>
            </div>
            <span class="student-prediction-panel__badge"><i data-lucide="<?= e($predictionIcon) ?>"></i><?= e((string) ($prediction['label'] ?? 'Histórico insuficiente')) ?></span>
        </div>

        <div class="student-prediction-panel__projection">
            <div><small>Risco atual</small><strong><?= e($riskLabels[$prediction['current_level'] ?? $riskLevel] ?? (string) ($prediction['current_level'] ?? $riskLevel)) ?></strong><span><?= (int) ($prediction['current_score'] ?? $riskScore) ?> pontos</span></div>
            <i data-lucide="arrow-right"></i>
            <div><small>Cenário em <?= (int) ($prediction['horizon_days'] ?? 14) ?> dias</small><strong><?= e($riskLabels[$prediction['projected_level'] ?? $riskLevel] ?? (string) ($prediction['projected_level'] ?? $riskLevel)) ?></strong><span><?= (int) ($prediction['projected_score'] ?? $riskScore) ?> pontos projetados</span></div>
            <div class="student-prediction-panel__confidence"><small>Confiança</small><strong><?= e($confidenceLabels[$prediction['confidence'] ?? 'LOW'] ?? 'baixa') ?></strong></div>
        </div>

        <?php if (!empty($prediction['evidence'])): ?>
            <details class="student-prediction-panel__expandable">
                <summary><span><i data-lucide="list-tree"></i> Ver evidências e ações sugeridas</span><i data-lucide="chevron-down"></i></summary>
                <div class="student-prediction-panel__details">
                    <div><h5>Por que o sistema projeta isso?</h5><ul><?php foreach ((array) $prediction['evidence'] as $evidence): ?><li><i data-lucide="circle-check"></i><?= e((string) $evidence) ?></li><?php endforeach; ?></ul></div>
                    <div><h5>Ações preventivas sugeridas</h5><ul><?php foreach ((array) $prediction['preventive_actions'] as $action): ?><li><i data-lucide="lightbulb"></i><?= e((string) $action) ?></li><?php endforeach; ?></ul></div>
                </div>
            </details>
        <?php endif; ?>
        <p class="student-prediction-panel__note"><i data-lucide="info"></i>Projeção explicável baseada no histórico registrado. Não representa diagnóstico nem probabilidade estatística.</p>
    </section>

    <section class="student-trends-panel">
        <div class="student-trends-panel__header">
            <div>
                <span><i data-lucide="line-chart"></i> Evolução histórica</span>
                <h4>Tendências dos últimos 30 dias</h4>
                <p><?= e((string) ($historicalTrends['summary'] ?? 'O histórico ainda está sendo formado.')) ?></p>
            </div>
            <span class="student-trends-panel__confidence">Confiança <?= e($confidenceLabels[$historicalTrends['confidence'] ?? 'LOW'] ?? 'baixa') ?> · <?= (int) ($historicalTrends['samples'] ?? 0) ?> captura(s)</span>
        </div>
        <div class="student-trends-grid">
            <?php foreach ($trendItems as $trendItem): ?>
                <?php
                $trend = (array) ($historicalTrends[$trendItem['key']] ?? []);
                $status = strtoupper((string) ($trend['status'] ?? 'INSUFFICIENT'));
                ?>
                <article class="student-trend-card is-<?= e($trendTone($status)) ?>" tabindex="0" title="<?= e((string) ($trend['explanation'] ?? 'Histórico insuficiente.')) ?>">
                    <span><i data-lucide="<?= e((string) ($trend['icon'] ?? 'minus')) ?>"></i><?= e($trendItem['label']) ?></span>
                    <strong><?= e((string) ($trend['label'] ?? 'Dados insuficientes')) ?></strong>
                    <p><?= e((string) ($trend['explanation'] ?? 'São necessários mais snapshots para calcular esta tendência.')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="student-followup-timeline student-followup-timeline--complete">
        <div class="student-followup-timeline__header">
            <div>
                <span>Histórico completo</span>
                <h4>Linha do tempo do acompanhamento</h4>
                <p>Intervenções, participantes e mudanças de situação, do evento mais recente ao mais antigo.</p>
            </div>
            <span class="student-followup-timeline__count"><?= count($monitoringTimeline) ?> evento(s)</span>
        </div>

        <?php if ($monitoringTimeline === []): ?>
            <div class="student-followup-timeline__empty">
                <i data-lucide="history"></i>
                <div><strong>Nenhum evento registrado</strong><span>Quando houver um acompanhamento, seus acontecimentos aparecerão aqui.</span></div>
            </div>
        <?php else: ?>
            <div class="student-followup-timeline__list" data-progressive-timeline data-initial-limit="<?= $timelineInitialLimit ?>" data-step="<?= $timelineInitialLimit ?>">
                <?php foreach ($monitoringTimeline as $eventIndex => $event): ?>
                    <?php
                    $type = (string) ($event['type'] ?? '');
                    $meta = $eventLabels[$type] ?? ['Evento do acompanhamento', 'circle', 'information'];
                    ?>
                    <article class="student-followup-timeline__item is-<?= e($meta[2]) ?><?= $eventIndex >= $timelineInitialLimit ? ' is-timeline-hidden' : '' ?>" data-timeline-item>
                        <div class="student-followup-timeline__icon"><i data-lucide="<?= e($meta[1]) ?>"></i></div>
                        <div class="student-followup-timeline__body">
                            <div>
                                <strong><?= e($type === 'ACTION' ? (string) ($event['action_label'] ?? $meta[0]) : $meta[0]) ?></strong>
                                <time><?= date('d/m/Y H:i', strtotime((string) ($event['event_at'] ?? 'now'))) ?></time>
                            </div>
                            <?php if (in_array($type, ['ACTION', 'ACTION_CREATED'], true)): ?>
                                <p><?= e((string) ($event['action_description'] ?? '')) ?></p>
                                <small>Realizada por <b><?= e((string) ($event['author_name'] ?? 'Responsável não identificado')) ?></b>.</small>
                            <?php elseif ($type === 'NO_ACTIVE_FOLLOWERS'): ?>
                                <p>O aluno ficou sem nenhum acompanhante ativo.</p>
                                <small>Operação registrada por <b><?= e((string) ($event['actor_name'] ?? 'Responsável não identificado')) ?></b>.</small>
                            <?php elseif (in_array($type, ['MONITORING_STARTED', 'MONITORING_RESUMED'], true)): ?>
                                <p><?= $type === 'MONITORING_STARTED' ? 'O acompanhamento do aluno foi iniciado.' : 'O acompanhamento do aluno foi retomado.' ?></p>
                                <small>Operação realizada por <b><?= e((string) ($event['actor_name'] ?? 'Responsável não identificado')) ?></b>.</small>
                            <?php elseif (in_array($type, ['PARTICIPANT_ADDED', 'PARTICIPANT_REMOVED'], true)): ?>
                                <p><b><?= e((string) ($event['target_name'] ?? 'Usuário')) ?></b> <?= $type === 'PARTICIPANT_REMOVED' ? 'deixou de acompanhar o aluno.' : 'passou a acompanhar o aluno.' ?></p>
                                <small>Operação realizada por <b><?= e((string) ($event['actor_name'] ?? 'Responsável não identificado')) ?></b>.</small>
                            <?php else: ?>
                                <p><?= e((string) ($event['details'] ?? $meta[0])) ?></p>
                                <small>Operação realizada por <b><?= e((string) ($event['actor_name'] ?? 'Responsável não identificado')) ?></b>.</small>
                            <?php endif; ?>
                            <?php $timelineAttachments = is_array($event['attachments'] ?? null) ? $event['attachments'] : []; ?>
                            <?php if ($timelineAttachments !== []): ?>
                                <?php component('media/attachment-carousel', ['attachments' => $timelineAttachments, 'title' => 'Imagens anexadas', 'compact' => true]); ?>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($timelineTotal > $timelineInitialLimit): ?>
                <div class="student-followup-timeline__controls">
                    <button
                        type="button"
                        class="student-followup-timeline__toggle"
                        data-timeline-toggle
                        data-more-label="Ver mais eventos"
                        data-less-label="Recolher histórico"
                        aria-expanded="false"
                    >
                        <i data-lucide="chevron-down"></i>
                        <span>Ver mais <?= min($timelineInitialLimit, $timelineTotal - $timelineInitialLimit) ?> evento(s)</span>
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</section>
