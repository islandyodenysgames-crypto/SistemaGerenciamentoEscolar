<?php
$intelligence = $classIntelligence ?? [];
$risk = $intelligence['risk'] ?? ['score' => 0, 'level' => 'LOW'];
$distribution = $intelligence['distribution'] ?? [];
$attendance = $intelligence['attendance'] ?? [];
$occurrences = $intelligence['occurrences'] ?? [];
$priorityStudents = $intelligence['priority_students'] ?? [];
$recommendations = $intelligence['recommendations'] ?? [];
$period = $intelligence['period'] ?? [];
$historicalTrends = (array) ($intelligence['historical_trends'] ?? []);
$classPrediction = (array) ($intelligence['prediction'] ?? []);
$classReasons = [];
if ((int) ($attendance['unjustified_absences'] ?? 0) > 0) {
    $classReasons[] = (int) ($attendance['unjustified_absences'] ?? 0) . ' falta(s) sem justificativa registrada(s) para ' . (int) ($attendance['students_affected'] ?? 0) . ' aluno(s).';
}
if ((int) ($occurrences['total'] ?? 0) > 0) {
    $classReasons[] = (int) ($occurrences['total'] ?? 0) . ' ocorrência(s) registrada(s), sendo ' . (int) ($occurrences['serious'] ?? 0) . ' grave(s) e ' . (int) ($occurrences['open'] ?? 0) . ' aberta(s).';
}
if ((int) ($distribution['CRITICAL'] ?? 0) + (int) ($distribution['HIGH'] ?? 0) > 0) {
    $classReasons[] = ((int) ($distribution['CRITICAL'] ?? 0) + (int) ($distribution['HIGH'] ?? 0)) . ' aluno(s) classificado(s) em risco alto ou crítico.';
}
if ($classReasons === []) {
    $classReasons[] = 'Nenhum sinal prioritário foi identificado nos registros da janela atual.';
}

$levelLabels = [
    'LOW' => 'Baixo',
    'MODERATE' => 'Atenção',
    'HIGH' => 'Alto',
    'CRITICAL' => 'Crítico',
];
$level = (string) ($risk['level'] ?? 'LOW');
$trendLabel = static function (array $trend): string {
    $status = (string) ($trend['status'] ?? 'STABLE');
    $percentage = abs((float) ($trend['percentage'] ?? 0));
    return match ($status) {
        'IMPROVING' => 'Melhora de ' . number_format($percentage, 1, ',', '.') . '%',
        'WORSENING' => 'Piora de ' . number_format($percentage, 1, ',', '.') . '%',
        default => 'Estável',
    };
};
?>

<section class="class-intelligence" id="classIntelligence">
    <div class="class-intelligence__header">
        <div>
            <span class="class-intelligence__eyebrow">
                <i data-lucide="brain-circuit"></i>
                Inteligência da turma
            </span>
            <h3>Visão analítica</h3>
            <p>
                Sinais de frequência e ocorrências dos últimos
                <?= (int) ($period['days'] ?? 30) ?> dias.
            </p>
        </div>
        <div class="class-intelligence__header-actions">
            <button type="button" class="btn-secondary class-intelligence__toggle" id="classIntelligenceToggle" aria-expanded="true" aria-controls="classIntelligenceContent">
                <i data-lucide="chevron-up"></i><span>Recolher painel</span>
            </button>
            <a href="<?= base_url('inteligencia/comparacoes?turma=' . (int) ($classId ?? 0)) ?>" class="btn-secondary">
                <i data-lucide="scale"></i> Comparar turma
            </a>
            <a href="<?= base_url('inteligencia/casos?tipo=attention_classes') ?>" class="btn-secondary">
                <i data-lucide="external-link"></i> Ver na Central
            </a>
        </div>
    </div>

    <div class="class-intelligence__content" id="classIntelligenceContent">
    <div class="class-intelligence__overview">
        <article class="class-intelligence__risk class-intelligence__risk--<?= strtolower(e($level)) ?>">
            <div>
                <span>Nível de risco da turma</span>
                <strong><?= e($levelLabels[$level] ?? $level) ?></strong>
                <small>Índice calculado com as regras da Central de Inteligência</small>
            </div>
            <div class="class-intelligence__score">
                <?= (int) ($risk['score'] ?? 0) ?>
                <small>/100</small>
            </div>
        </article>

        <div class="class-intelligence__distribution">
            <?php foreach (['CRITICAL', 'HIGH', 'MODERATE', 'LOW'] as $riskLevel): ?>
                <div class="class-intelligence__level class-intelligence__level--<?= strtolower($riskLevel) ?>">
                    <strong><?= (int) ($distribution[$riskLevel] ?? 0) ?></strong>
                    <span><?= e($levelLabels[$riskLevel]) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php $classFilterQuery = '&turma=' . urlencode((string) ($className ?? '')); ?>
    <details class="class-intelligence__explanation class-intelligence__explanation--<?= strtolower(e($level)) ?>" aria-label="Explicação do nível de risco da turma">
        <summary>
            <span><i data-lucide="circle-help"></i><strong>Por que esta análise?</strong></span>
            <small>Clique para ver os fatores considerados</small>
            <i data-lucide="chevron-down" class="class-intelligence__explanation-chevron"></i>
        </summary>
        <div class="class-intelligence__explanation-content">
            <p>O nível <b><?= e(strtolower($levelLabels[$level] ?? $level)) ?></b> e a pontuação de <?= (int) ($risk['score'] ?? 0) ?>/100 foram formados pelos registros disponíveis no período.</p>
            <ul><?php foreach (array_slice($classReasons, 0, 3) as $reason): ?><li><?= e($reason) ?></li><?php endforeach; ?></ul>
            <small>Esta leitura resume fatos registrados e regras configuradas; não representa diagnóstico.</small>
        </div>
    </details>

    <div class="class-intelligence__metrics">
        <a class="class-intelligence__metric class-intelligence__metric--absence" href="<?= base_url('inteligencia/casos?tipo=unjustified_absences' . $classFilterQuery) ?>" data-class-metric-tooltip="Total de faltas com status F registradas para os alunos desta turma no período analisado. Faltas justificadas, atestados e falta de ônibus não entram neste número.">
            <i data-lucide="calendar-x"></i>
            <div><strong><?= (int) ($attendance['unjustified_absences'] ?? 0) ?></strong><span>Faltas sem justificativa</span></div>
            <small><?= e($trendLabel($attendance['trend'] ?? [])) ?> · Clique para ver os alunos</small>
        </a>
        <a class="class-intelligence__metric class-intelligence__metric--students" href="<?= base_url('inteligencia/casos?tipo=unjustified_absences' . $classFilterQuery) ?>" data-class-metric-tooltip="Quantidade de alunos únicos da turma que receberam pelo menos uma falta sem justificativa no período. Um aluno com várias faltas é contado apenas uma vez neste card.">
            <i data-lucide="users-round"></i>
            <div><strong><?= (int) ($attendance['students_affected'] ?? 0) ?></strong><span>Alunos afetados por faltas</span></div>
            <small><?= (int) ($attendance['attenuated_absences'] ?? 0) ?> falta(s) atenuada(s) · Clique para conferir</small>
        </a>
        <a class="class-intelligence__metric class-intelligence__metric--occurrence" href="<?= base_url('inteligencia/casos?tipo=occurrences_period' . $classFilterQuery) ?>" data-class-metric-tooltip="Total de ocorrências registradas para os alunos desta turma dentro do período analisado, independentemente de estarem abertas ou resolvidas.">
            <i data-lucide="clipboard-alert"></i>
            <div><strong><?= (int) ($occurrences['total'] ?? 0) ?></strong><span>Ocorrências no período</span></div>
            <small><?= e($trendLabel($occurrences['trend'] ?? [])) ?> · Clique para ver os alunos</small>
        </a>
        <a class="class-intelligence__metric class-intelligence__metric--serious" href="<?= base_url('inteligencia/casos?tipo=serious_occurrences' . $classFilterQuery) ?>" data-class-metric-tooltip="Ocorrências da turma classificadas com gravidade alta ou crítica no período analisado. O número abaixo informa quantas ocorrências ainda estão abertas.">
            <i data-lucide="shield-alert"></i>
            <div><strong><?= (int) ($occurrences['serious'] ?? 0) ?></strong><span>Ocorrências graves</span></div>
            <small><?= (int) ($occurrences['open'] ?? 0) ?> em aberto · Clique para conferir</small>
        </a>
    </div>

    <section class="class-predictive-intelligence" id="classPredictiveIntelligence">
        <div class="class-predictive-intelligence__header">
            <div>
                <span class="class-predictive-intelligence__eyebrow"><i data-lucide="sparkles"></i> Inteligência preditiva</span>
                <h4>Projeção coletiva para os próximos <?= (int) ($classPrediction['horizon_days'] ?? 14) ?> dias</h4>
                <p><?= e((string) ($classPrediction['summary'] ?? 'O histórico da turma ainda está sendo formado.')) ?></p>
            </div>
            <span class="class-predictive-intelligence__status class-predictive-intelligence__status--<?= strtolower(e((string) ($classPrediction['status'] ?? 'INSUFFICIENT'))) ?>">
                <?= e((string) ($classPrediction['label'] ?? 'Histórico insuficiente')) ?>
            </span>
        </div>

        <div class="class-predictive-intelligence__indicators">
            <?php foreach (['frequency' => 'Frequência', 'occurrences' => 'Ocorrências', 'risk' => 'Risco médio', 'coverage' => 'Cobertura', 'recommendations' => 'Pendências'] as $key => $label): ?>
                <?php $indicator = (array) ($historicalTrends[$key] ?? []); ?>
                <?php $indicatorStatus = strtolower((string) ($indicator['status'] ?? 'INSUFFICIENT')); ?>
                <article class="class-predictive-indicator class-predictive-indicator--<?= e($key) ?> class-predictive-indicator--<?= e($indicatorStatus) ?>" tabindex="0" title="<?= e((string) ($indicator['explanation'] ?? 'Ainda não há histórico suficiente para comparação.')) ?>">
                    <span><?= e($label) ?></span>
                    <div class="class-predictive-indicator__value">
                        <i data-lucide="<?= $indicatorStatus === 'improving' ? 'trending-up' : ($indicatorStatus === 'worsening' ? 'trending-down' : ($indicatorStatus === 'stable' ? 'minus' : 'circle-help')) ?>"></i>
                        <strong><?= e((string) ($indicator['label'] ?? 'Dados insuficientes')) ?></strong>
                    </div>
                    <small><?= e((string) ($indicator['explanation'] ?? 'Ainda não há histórico suficiente para comparação.')) ?></small>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="class-predictive-intelligence__details">
            <article>
                <header><i data-lucide="scan-search"></i><div><h5>Evidências consideradas</h5><p>Fatores históricos que sustentam a projeção.</p></div></header>
                <?php $evidence = (array) ($classPrediction['evidence'] ?? []); ?>
                <?php if ($evidence === []): ?>
                    <div class="class-predictive-intelligence__empty">Nenhuma evidência conclusiva disponível.</div>
                <?php else: ?>
                    <ul><?php foreach ($evidence as $item): ?><li><i data-lucide="check-circle-2"></i><?= e((string) $item) ?></li><?php endforeach; ?></ul>
                <?php endif; ?>
            </article>
            <article>
                <header><i data-lucide="route"></i><div><h5>Ações recomendadas</h5><p>Medidas preventivas sugeridas para a gestão.</p></div></header>
                <?php $predictiveRecommendations = (array) ($classPrediction['recommendations'] ?? []); ?>
                <?php if ($predictiveRecommendations === []): ?>
                    <div class="class-predictive-intelligence__empty">Nenhuma ação preventiva adicional foi sugerida.</div>
                <?php else: ?>
                    <ul><?php foreach ($predictiveRecommendations as $item): ?><li><i data-lucide="arrow-right-circle"></i><?= e((string) $item) ?></li><?php endforeach; ?></ul>
                <?php endif; ?>
            </article>
        </div>

        <div class="class-predictive-intelligence__footer">
            <span><i data-lucide="database"></i><?= (int) ($historicalTrends['samples'] ?? 0) ?> captura(s) em <?= (int) ($historicalTrends['span_days'] ?? 0) ?> dia(s) · Confiança <?= e(strtolower((string) ($classPrediction['confidence'] ?? 'LOW'))) ?></span>
            <a href="<?= base_url('inteligencia/comparacoes?turma=' . (int) ($classId ?? 0)) ?>" class="btn-secondary"><i data-lucide="scale"></i> Comparar com outras turmas</a>
        </div>
    </section>

    <div class="class-intelligence__grid">
        <div class="class-intelligence__panel">
            <div class="class-intelligence__panel-header">
                <div><h4>Alunos prioritários</h4><p>Casos de atenção, alto ou crítico.</p></div>
                <span><?= count($priorityStudents) ?></span>
            </div>
            <?php if ($priorityStudents === []): ?>
                <div class="class-intelligence__empty"><i data-lucide="badge-check"></i><p>Nenhum aluno prioritário no período.</p></div>
            <?php else: ?>
                <div class="class-intelligence__students">
                    <?php foreach ($priorityStudents as $student): ?>
                        <?php $studentLevel = (string) ($student['risk_level'] ?? 'LOW'); ?>
                        <a href="<?= base_url('alunos/perfil?id=' . (int) $student['id'] . '#studentIntelligence') ?>">
                            <div>
                                <strong><?= e((string) ($student['name'] ?? 'Aluno')) ?></strong>
                                <small><?= e(implode(' • ', $student['reasons'] ?? [])) ?></small>
                            </div>
                            <span class="risk-chip risk-chip--<?= strtolower($studentLevel) ?>">
                                <?= e($levelLabels[$studentLevel] ?? $studentLevel) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="class-intelligence__panel">
            <div class="class-intelligence__panel-header">
                <div><h4>Recomendações</h4><p>Próximas ações sugeridas.</p></div>
                <i data-lucide="lightbulb"></i>
            </div>
            <div class="class-intelligence__recommendations">
                <?php foreach ($recommendations as $recommendation): ?>
                    <?php $recommendationExplanation = (string) ($recommendation['explanation'] ?? $recommendation['text'] ?? 'Recomendação baseada nos indicadores atuais da turma.'); ?>
                    <article class="recommendation recommendation--<?= strtolower((string) ($recommendation['level'] ?? 'LOW')) ?>" tabindex="0" data-recommendation-tooltip="<?= e($recommendationExplanation) ?>" aria-label="<?= e((string) ($recommendation['title'] ?? 'Recomendação') . '. ' . $recommendationExplanation) ?>">
                        <i data-lucide="<?= e((string) ($recommendation['icon'] ?? 'circle-check')) ?>"></i>
                        <div><strong><?= e((string) ($recommendation['title'] ?? 'Recomendação')) ?></strong><p><?= e((string) ($recommendation['text'] ?? '')) ?></p><small class="recommendation__hint"><i data-lucide="info"></i>Passe o mouse para entender</small></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php component('intelligence/timeline', [
        'timeline' => $intelligence['timeline'] ?? [],
        'title' => 'Timeline inteligente da turma',
        'subtitle' => 'Eventos calculados a partir da janela atual e da comparação com o período anterior.',
    ]); ?>
    </div>
</section>
<script>
(function () {
    const panel = document.getElementById('classIntelligence');
    const content = document.getElementById('classIntelligenceContent');
    const toggle = document.getElementById('classIntelligenceToggle');
    if (!panel || !content || !toggle) return;
    const key = 'sfe:class-intelligence:collapsed';
    const label = toggle.querySelector('span');
    const icon = toggle.querySelector('i');
    const apply = (collapsed) => {
        panel.classList.toggle('is-collapsed', collapsed);
        content.hidden = collapsed;
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        if (label) label.textContent = collapsed ? 'Visualizar painel' : 'Recolher painel';
        if (icon) icon.setAttribute('data-lucide', collapsed ? 'chevron-down' : 'chevron-up');
        if (window.lucide) window.lucide.createIcons();
    };
    let collapsed = true;
    try {
        const saved = localStorage.getItem(key);
        collapsed = saved === null ? true : saved === '1';
    } catch (error) {}
    apply(collapsed);
    const openFromNavigation = () => {
        if (window.location.hash === '#classIntelligence') {
            collapsed = false;
            apply(false);
        }
    };
    document.querySelectorAll('a[href="#classIntelligence"]').forEach((link) => link.addEventListener('click', () => {
        collapsed = false;
        apply(false);
        try { localStorage.setItem(key, '0'); } catch (error) {}
    }));
    window.addEventListener('hashchange', openFromNavigation);
    openFromNavigation();
    toggle.addEventListener('click', () => {
        collapsed = !panel.classList.contains('is-collapsed');
        apply(collapsed);
        try { localStorage.setItem(key, collapsed ? '1' : '0'); } catch (error) {}
    });
})();
</script>
