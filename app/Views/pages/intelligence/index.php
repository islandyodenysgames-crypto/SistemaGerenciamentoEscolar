<?php

$data = $intelligence ?? [];
$summary = $data['summary'] ?? [];
$attendance = $data['attendance'] ?? [];
$occurrences = $data['occurrences'] ?? [];
$students = $data['students'] ?? [];
$classes = $data['classes'] ?? [];
$improvingClasses = is_array($data['improving_classes'] ?? null) ? $data['improving_classes'] : [];
$predictiveClasses = is_array($data['predictive_classes'] ?? null) ? $data['predictive_classes'] : [];
$monitoring = $data['monitoring'] ?? [];
$insights = $data['insights'] ?? [];
$period = $data['period'] ?? [];

$riskLabels = [
    'LOW' => 'Baixo',
    'MODERATE' => 'Moderado',
    'HIGH' => 'Alto',
    'CRITICAL' => 'Crítico',
];

$trendText = static function (array $trend): string {
    $percentage = (float) ($trend['percentage'] ?? 0);
    if (abs($percentage) < 0.1) {
        return 'Estável em relação ao período anterior';
    }
    return ($percentage > 0 ? '↑ ' : '↓ ') . number_format(abs($percentage), 1, ',', '.') . '% em relação ao período anterior';
};

$classMainSignal = static function (array $class): string {
    $absences = (int) ($class['unjustified_absences'] ?? 0);
    $occurrences = (int) ($class['total_occurrences'] ?? 0);
    $serious = (int) ($class['serious_occurrences'] ?? 0);
    if ($serious > 0) {
        return $serious . ' ocorrência(s) grave(s)';
    }
    if ($absences >= $occurrences) {
        return $absences . ' falta(s) sem justificativa';
    }
    return $occurrences . ' ocorrência(s) no período';
};

component('base/page-header', [
    'title' => 'Central de Inteligência',
    'subtitle' => 'Visão integrada das prioridades e da resposta da equipe escolar.',
]);
?>

<div class="intelligence-page intelligence-page--action-oriented">
    <section class="intelligence-hero intelligence-hero--compact panel-hover">
        <div>
            <span class="intelligence-eyebrow"><i data-lucide="brain-circuit"></i> Últimos <?= (int) ($period['days'] ?? 30) ?> dias</span>
            <h2>O que exige atenção agora?</h2>
            <p>Resumo dos sinais mais relevantes e da cobertura dos casos prioritários.</p>
        </div>
        <div class="intelligence-hero__actions">
            <a class="btn btn-secondary" href="<?= base_url('inteligencia/comparacoes') ?>"><i data-lucide="scale"></i> Comparar turmas</a>
            <span class="intelligence-period intelligence-period--inline">
                <i data-lucide="calendar-range"></i>
                <?= e(date('d/m/Y', strtotime((string) ($period['start'] ?? 'now')))) ?> a <?= e(date('d/m/Y', strtotime((string) ($period['end'] ?? 'now')))) ?>
            </span>
        </div>
    </section>

    <section class="intelligence-summary-grid intelligence-summary-grid--compact">
        <a class="intelligence-summary-card intelligence-summary-card--warning" href="<?= base_url('inteligencia/casos?tipo=students_attention') ?>" data-intelligence-tooltip="Total de alunos classificados nos níveis de atenção, alto ou crítico no período analisado. O número menor informa quantos estão em nível crítico.">
            <span><i data-lucide="user-round-search"></i></span>
            <div><small>Alunos prioritários</small><strong><?= (int) ($summary['students_in_attention'] ?? 0) ?></strong><p><?= (int) ($summary['critical_students'] ?? 0) ?> crítico(s)</p></div>
        </a>
        <a class="intelligence-summary-card intelligence-summary-card--info" href="<?= base_url('inteligencia/casos?tipo=attention_classes') ?>" data-intelligence-tooltip="Quantidade de turmas que concentram sinais relevantes de faltas, ocorrências ou risco entre os alunos.">
            <span><i data-lucide="school"></i></span>
            <div><small>Turmas em atenção</small><strong><?= (int) ($summary['classes_in_attention'] ?? 0) ?></strong><p>com sinais relevantes</p></div>
        </a>
        <a class="intelligence-summary-card intelligence-summary-card--primary" href="<?= base_url('inteligencia/casos?tipo=combined_risk') ?>" data-intelligence-tooltip="Alunos que apresentam simultaneamente sinais de frequência e ocorrências, indicando uma situação que merece análise integrada.">
            <span><i data-lucide="git-merge"></i></span>
            <div><small>Risco combinado</small><strong><?= (int) ($summary['combined_risk'] ?? 0) ?></strong><p>frequência e ocorrências</p></div>
        </a>
        <a class="intelligence-summary-card intelligence-summary-card--danger" href="<?= base_url('inteligencia/casos?tipo=stale_critical_occurrences') ?>" data-intelligence-tooltip="Ocorrências críticas que permanecem abertas ou sem tratamento dentro do prazo esperado.">
            <span><i data-lucide="clock-alert"></i></span>
            <div><small>Pendências críticas</small><strong><?= (int) ($summary['stale_critical_occurrences'] ?? 0) ?></strong><p>fora do prazo</p></div>
        </a>
    </section>

    <?php component('intelligence/insights-panel', [
        'insights' => array_slice($insights, 0, 4),
        'title' => 'Situações que exigem ação',
        'subtitle' => 'Prioridades atuais com orientação direta para a próxima providência.',
        'actionOriented' => true,
    ]); ?>

    <section class="intelligence-panel intelligence-monitoring-panel panel-hover">
        <header class="intelligence-panel__header">
            <div>
                <span class="intelligence-panel__icon"><i data-lucide="users-round"></i></span>
                <div><h3>Situação dos acompanhamentos</h3><p>Os casos prioritários estão recebendo resposta da equipe?</p></div>
            </div>
            <a class="intelligence-section-link" href="<?= base_url('acompanhamentos') ?>">Abrir acompanhamentos <i data-lucide="arrow-up-right"></i></a>
        </header>
        <div class="intelligence-coverage" tabindex="0" data-intelligence-tooltip="Percentual de alunos prioritários que possuem pelo menos um acompanhante ativo. A existência de um acompanhamento sem professor vinculado não conta como cobertura.">
            <div class="intelligence-coverage__main">
                <div class="intelligence-coverage__value"><?= (int) ($monitoring['coverage_percentage'] ?? 0) ?>%</div>
                <div><strong>Cobertura dos casos prioritários</strong><p><?= (int) ($monitoring['covered_cases'] ?? 0) ?> de <?= (int) ($monitoring['priority_students'] ?? 0) ?> alunos possuem ao menos um acompanhante ativo.</p></div>
            </div>
            <div class="intelligence-coverage__bar" aria-label="Cobertura dos acompanhamentos"><span style="width: <?= max(0, min(100, (int) ($monitoring['coverage_percentage'] ?? 0))) ?>%"></span></div>
        </div>
        <div class="intelligence-operational-grid">
            <a href="<?= base_url('inteligencia/casos?tipo=without_monitoring') ?>" data-intelligence-tooltip="Alunos prioritários sem qualquer participante ativo em seus casos. Inclui alunos que ainda não possuem caso e casos que estão sem acompanhante ativo."><i data-lucide="user-minus"></i><span>Sem acompanhamento</span><strong><?= (int) ($monitoring['without_follower'] ?? 0) ?></strong></a>
            <a href="<?= base_url('inteligencia/casos?tipo=stale_monitoring_actions') ?>" data-intelligence-tooltip="Alunos prioritários com acompanhamento ativo cuja última intervenção ocorreu há 14 dias ou mais. Quando ainda não existe intervenção, o prazo é contado desde o início do acompanhamento. Eventos administrativos não contam."><i data-lucide="history"></i><span>Sem atualização de intervenção há 14 dias</span><strong><?= (int) ($monitoring['without_recent_action'] ?? 0) ?></strong></a>
            <a href="<?= base_url('acompanhamentos') ?>" data-intelligence-tooltip="Recomendações geradas pela Inteligência Escolar que ainda aguardam início, conclusão ou registro de providência."><i data-lucide="list-checks"></i><span>Recomendações pendentes</span><strong><?= (int) ($monitoring['pending_recommendations'] ?? 0) ?></strong></a>
        </div>
    </section>

    <section class="intelligence-grid intelligence-grid--two intelligence-indicator-grid">
        <a class="intelligence-indicator-card intelligence-indicator-card--attendance panel-hover" href="<?= base_url('inteligencia/casos?tipo=unjustified_absences') ?>" data-intelligence-tooltip="Soma das faltas sem justificativa no período e quantidade de alunos afetados. FJ, AM e FO não entram neste indicador.">
            <span class="intelligence-indicator-card__icon"><i data-lucide="clipboard-check"></i></span>
            <div class="intelligence-indicator-card__body">
                <div><small>Frequência</small><strong><?= (int) ($attendance['unjustified_absences'] ?? 0) ?> faltas sem justificativa</strong></div>
                <p><?= (int) ($attendance['students_affected'] ?? 0) ?> aluno(s) envolvidos</p>
                <span class="trend trend--<?= strtolower((string) (($attendance['trend']['status'] ?? 'stable'))) ?>"><?= e($trendText((array) ($attendance['trend'] ?? []))) ?></span>
            </div>
            <i data-lucide="arrow-up-right"></i>
        </a>

        <a class="intelligence-indicator-card intelligence-indicator-card--occurrences panel-hover" href="<?= base_url('ocorrencias?data_inicial=' . urlencode((string) ($period['start'] ?? '')) . '&data_final=' . urlencode((string) ($period['end'] ?? ''))) ?>" data-intelligence-tooltip="Total de ocorrências registradas no período, com destaque para aquelas classificadas como de alta gravidade.">
            <span class="intelligence-indicator-card__icon"><i data-lucide="triangle-alert"></i></span>
            <div class="intelligence-indicator-card__body">
                <div><small>Ocorrências</small><strong><?= (int) ($occurrences['total'] ?? 0) ?> registros</strong></div>
                <p><?= (int) ($occurrences['serious'] ?? 0) ?> de alta gravidade</p>
                <span class="trend trend--<?= strtolower((string) (($occurrences['trend']['status'] ?? 'stable'))) ?>"><?= e($trendText((array) ($occurrences['trend'] ?? []))) ?></span>
            </div>
            <i data-lucide="arrow-up-right"></i>
        </a>
    </section>

    <section class="intelligence-grid intelligence-grid--two intelligence-priority-grid">
        <article class="intelligence-panel panel-hover">
            <header class="intelligence-panel__header">
                <div><span class="intelligence-panel__icon"><i data-lucide="users-round"></i></span><div><h3>Alunos prioritários</h3><p>Os cinco casos que mais exigem atenção.</p></div></div>
                <a class="intelligence-section-link" href="<?= base_url('inteligencia/casos?tipo=students_attention') ?>">Ver todos <i data-lucide="arrow-right"></i></a>
            </header>
            <div class="intelligence-student-list intelligence-student-list--compact">
                <?php if ($students === []): ?><div class="intelligence-empty">Nenhum aluno com sinal relevante no período.</div><?php endif; ?>
                <?php foreach ($students as $student): ?>
                    <?php
                    $studentMonitoring = (array) ($student['monitoring'] ?? []);
                    $monitoringLabel = 'Sem acompanhamento ativo';
                    $monitoringTone = 'danger';
                    if (!empty($studentMonitoring['has_active_monitoring'])) {
                        if (!empty($studentMonitoring['without_recent_action'])) {
                            $days = $studentMonitoring['days_without_action'];
                            $monitoringLabel = $days === null ? 'Sem intervenção registrada' : 'Sem atualização de intervenção há ' . (int) $days . ' dias';
                            $monitoringTone = 'warning';
                        } elseif ((int) ($studentMonitoring['pending_recommendations'] ?? 0) > 0) {
                            $monitoringLabel = 'Recomendação pendente';
                            $monitoringTone = 'warning';
                        } else {
                            $followers = (int) ($studentMonitoring['active_followers'] ?? 0);
                            $monitoringLabel = 'Acompanhamento ativo · ' . $followers . ' responsável(is)';
                            $monitoringTone = 'success';
                        }
                    }
                    ?>
                    <article class="intelligence-student intelligence-student--compact" tabindex="0" data-intelligence-tooltip="Este card reúne o nível de risco do aluno, sua turma e a situação atual do acompanhamento. A pontuação maior indica prioridade mais elevada.">
                        <div class="risk-score risk-score--<?= strtolower(e((string) $student['risk_level'])) ?>"><?= (int) $student['risk_score'] ?></div>
                        <div class="intelligence-student__main">
                            <div class="intelligence-student__title"><div><strong><?= e((string) $student['name']) ?></strong><span><?= e((string) $student['class_name']) ?></span></div><span class="risk-badge risk-badge--<?= strtolower(e((string) $student['risk_level'])) ?>"><?= e($riskLabels[$student['risk_level']] ?? $student['risk_level']) ?></span></div>
                            <span class="intelligence-monitoring-status intelligence-monitoring-status--<?= e($monitoringTone) ?>"><i data-lucide="<?= $monitoringTone === 'success' ? 'circle-check' : ($monitoringTone === 'warning' ? 'clock-3' : 'circle-alert') ?>"></i><?= e($monitoringLabel) ?></span>
                            <?php
                            $prediction = (array) ($student['prediction'] ?? []);
                            $predictionStatus = strtolower((string) ($prediction['status'] ?? 'insufficient'));
                            $predictionIcon = match (strtoupper($predictionStatus)) {
                                'IMPROVEMENT' => 'trending-up',
                                'DETERIORATION' => 'triangle-alert',
                                'CRITICAL_ESCALATION' => 'siren',
                                'STABLE' => 'arrow-right',
                                default => 'hourglass',
                            };
                            ?>
                            <span class="intelligence-prediction-chip is-<?= e($predictionStatus) ?>" title="<?= e((string) ($prediction['summary'] ?? 'Histórico insuficiente para previsão.')) ?>"><i data-lucide="<?= e($predictionIcon) ?>"></i>Previsão: <?= e((string) ($prediction['label'] ?? 'Histórico insuficiente')) ?></span>
                            <?php
                            $studentTrends = (array) ($student['historical_trends'] ?? []);
                            $miniTrends = [
                                ['key' => 'frequency', 'label' => 'Frequência'],
                                ['key' => 'occurrences', 'label' => 'Ocorrências'],
                                ['key' => 'risk', 'label' => 'Risco'],
                            ];
                            ?>
                            <div class="intelligence-student-trends">
                                <?php foreach ($miniTrends as $miniTrend): ?>
                                    <?php
                                    $trend = (array) ($studentTrends[$miniTrend['key']] ?? []);
                                    $status = strtolower((string) ($trend['status'] ?? 'insufficient'));
                                    ?>
                                    <span class="is-<?= e($status) ?>" title="<?= e((string) ($trend['explanation'] ?? 'Histórico insuficiente.')) ?>"><i data-lucide="<?= e((string) ($trend['icon'] ?? 'minus')) ?>"></i><?= e($miniTrend['label']) ?>: <?= e((string) ($trend['label'] ?? 'Sem dados')) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <a class="intelligence-link" href="<?= base_url('alunos/perfil?id=' . (int) $student['id']) ?>" title="Abrir perfil"><i data-lucide="arrow-up-right"></i></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="intelligence-panel panel-hover">
            <header class="intelligence-panel__header">
                <div><span class="intelligence-panel__icon"><i data-lucide="school"></i></span><div><h3>Turmas prioritárias</h3><p>As três maiores concentrações de sinais.</p></div></div>
                <a class="intelligence-section-link" href="<?= base_url('inteligencia/comparacoes') ?>">Comparar todas <i data-lucide="arrow-right"></i></a>
            </header>
            <div class="intelligence-class-list intelligence-class-list--compact">
                <?php foreach ($classes as $index => $class): ?>
                    <?php $classLevel = strtolower((string) ($class['risk_level'] ?? 'low')); ?>
                    <div class="intelligence-tooltip-host" tabindex="0" data-intelligence-tooltip="Turma priorizada pela concentração de faltas, ocorrências e alunos em risco. O texto abaixo mostra o principal sinal identificado no período.">
                        <article class="intelligence-class-card intelligence-class-card--<?= e($classLevel) ?>">
                            <span class="intelligence-class-position"><?= $index + 1 ?>º</span>
                            <div class="intelligence-class-card__content">
                                <div class="intelligence-class-card__title"><strong><?= e((string) $class['name']) ?></strong><span class="risk-badge risk-badge--<?= e($classLevel) ?>"><?= e($riskLabels[$class['risk_level']] ?? $class['risk_level']) ?></span></div>
                                <p>Principal sinal: <?= e($classMainSignal($class)) ?></p>
                                <?php $classTrend = (array) ($class['historical_trends'] ?? []); $classPrediction = (array) ($class['prediction'] ?? []); ?>
                                <div class="intelligence-class-trends">
                                    <span class="is-<?= e(strtolower((string) ($classTrend['overall_status'] ?? 'insufficient'))) ?>" title="<?= e((string) ($classTrend['summary'] ?? 'Histórico insuficiente.')) ?>"><i data-lucide="<?= (($classTrend['overall_status'] ?? '') === 'IMPROVING') ? 'trending-up' : ((($classTrend['overall_status'] ?? '') === 'WORSENING') ? 'trending-down' : 'arrow-right') ?>"></i><?= e((string) (($classTrend['overall_status'] ?? '') === 'IMPROVING' ? 'Melhorando' : ((($classTrend['overall_status'] ?? '') === 'WORSENING') ? 'Piorando' : ((($classTrend['overall_status'] ?? '') === 'STABLE') ? 'Estável' : 'Sem histórico')))) ?></span>
                                    <span class="intelligence-class-prediction" title="<?= e((string) ($classPrediction['summary'] ?? 'Histórico insuficiente para projeção.')) ?>">Previsão: <?= e((string) ($classPrediction['label'] ?? 'Histórico insuficiente')) ?></span>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
                <?php if ($classes === []): ?><div class="intelligence-empty">Nenhuma turma com sinal relevante.</div><?php endif; ?>
            </div>
        </article>
    </section>

    <section class="intelligence-panel panel-hover intelligence-class-predictive">
        <header class="intelligence-panel__header">
            <div>
                <span class="intelligence-panel__icon"><i data-lucide="brain-circuit"></i></span>
                <div>
                    <h3>Inteligência Preditiva das Turmas</h3>
                    <p>Tendências históricas e cenário provável para os próximos 14 dias.</p>
                </div>
            </div>
            <a class="intelligence-section-link" href="<?= base_url('inteligencia/comparacoes') ?>">Comparar turmas <i data-lucide="arrow-right"></i></a>
        </header>
        <?php if ($predictiveClasses === []): ?>
            <div class="intelligence-empty">Nenhuma turma ativa encontrada para análise.</div>
        <?php else: ?>
            <div class="intelligence-class-predictive-grid">
                <?php foreach ($predictiveClasses as $class): ?>
                    <?php
                    $trend = (array) ($class['historical_trends'] ?? []);
                    $prediction = (array) ($class['prediction'] ?? []);
                    $trendStatus = strtolower((string) ($trend['overall_status'] ?? 'INSUFFICIENT'));
                    $predictionStatus = strtolower((string) ($prediction['status'] ?? 'INSUFFICIENT'));
                    ?>
                    <article class="intelligence-class-predictive-card intelligence-class-predictive-card--<?= e($trendStatus) ?>" tabindex="0">
                        <div class="intelligence-class-predictive-card__top">
                            <div class="intelligence-class-predictive-card__identity">
                                <span class="intelligence-class-predictive-card__icon"><i data-lucide="school"></i></span>
                                <div>
                                    <strong><?= e((string) ($class['name'] ?? 'Turma')) ?></strong>
                                    <span>Risco atual: <?= e((string) ($class['risk_label'] ?? 'Baixo')) ?> · <?= (int) ($class['risk_score'] ?? 0) ?>/100</span>
                                </div>
                            </div>
                            <span class="intelligence-class-predictive-status intelligence-class-predictive-status--<?= e($predictionStatus) ?>">
                                <?= e((string) ($prediction['label'] ?? 'Histórico insuficiente')) ?>
                            </span>
                        </div>
                        <p><?= e((string) ($prediction['summary'] ?? $trend['summary'] ?? 'O histórico da turma ainda está sendo formado.')) ?></p>
                        <div class="intelligence-class-predictive-indicators">
                            <?php foreach (['frequency' => 'Frequência', 'occurrences' => 'Ocorrências', 'risk' => 'Risco', 'coverage' => 'Cobertura'] as $key => $label): ?>
                                <?php $indicator = (array) ($trend[$key] ?? []); ?>
                                <?php $indicatorStatus = strtolower((string) ($indicator['status'] ?? 'INSUFFICIENT')); ?>
                                <span class="intelligence-class-predictive-indicator intelligence-class-predictive-indicator--<?= e($key) ?> intelligence-class-predictive-indicator--<?= e($indicatorStatus) ?>" title="<?= e((string) ($indicator['explanation'] ?? 'Sem dados suficientes.')) ?>">
                                    <small><?= e($label) ?></small>
                                    <strong><i data-lucide="<?= $indicatorStatus === 'improving' ? 'trending-up' : ($indicatorStatus === 'worsening' ? 'trending-down' : ($indicatorStatus === 'stable' ? 'minus' : 'circle-help')) ?>"></i><?= e((string) ($indicator['label'] ?? 'Sem dados')) ?></strong>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="intelligence-class-predictive-actions">
                            <a href="<?= base_url('alunos/turma?id=' . (int) ($class['id'] ?? 0) . '#classPredictiveIntelligence') ?>"><i data-lucide="sparkles"></i> Ver análise</a>
                            <a href="<?= base_url('inteligencia/comparacoes?turma=' . (int) ($class['id'] ?? 0)) ?>"><i data-lucide="scale"></i> Comparar</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="intelligence-panel panel-hover intelligence-improving-classes">
        <header class="intelligence-panel__header">
            <div><span class="intelligence-panel__icon"><i data-lucide="trophy"></i></span><div><h3>Turmas que mais melhoraram</h3><p>Ranking positivo baseado na evolução histórica de frequência, ocorrências, risco, cobertura e pendências.</p></div></div>
        </header>
        <?php if (empty($improvingClasses)): ?>
            <div class="intelligence-empty">Ainda não há histórico suficiente ou nenhuma turma apresentou melhora consistente.</div>
        <?php else: ?>
            <div class="intelligence-improving-list">
                <?php foreach (($improvingClasses ?? []) as $index => $class): ?>
                    <?php $trend = (array) ($class['historical_trends'] ?? []); ?>
                    <article>
                        <span class="intelligence-improving-position"><?= $index + 1 ?>º</span>
                        <div><strong><?= e((string) ($class['name'] ?? 'Turma')) ?></strong><p><?= e((string) ($trend['summary'] ?? 'Evolução positiva identificada.')) ?></p></div>
                        <span class="intelligence-improving-score"><i data-lucide="trending-up"></i><?= e(number_format((float) ($trend['evolution_score'] ?? 0), 1, ',', '.')) ?></span>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
