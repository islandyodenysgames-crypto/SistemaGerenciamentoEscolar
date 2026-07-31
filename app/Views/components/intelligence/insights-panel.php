<?php
$insights = is_array($insights ?? null) ? $insights : [];
$compact = (bool) ($compact ?? false);
$actionOriented = (bool) ($actionOriented ?? false);
$collapsible = (bool) ($collapsible ?? false);
$collapsedByDefault = (bool) ($collapsedByDefault ?? false);
$title = (string) ($title ?? 'Insights prioritários');
$subtitle = (string) ($subtitle ?? 'Leitura automática das situações que merecem atenção e das melhorias identificadas.');
$panelId = 'insights-' . substr(md5($title . serialize(array_column($insights, 'key'))), 0, 10);
$storageKey = (string) ($storageKey ?? ('sfe.insights.' . $panelId . '.collapsed'));
$categoryLabels = [
    'ATTENDANCE' => 'Frequência',
    'OCCURRENCE' => 'Ocorrências',
    'CLASS' => 'Turmas',
    'STUDENT' => 'Alunos',
    'SCHOOL' => 'Escola',
];
$levelLabels = ['CRITICAL' => 'Crítico', 'ATTENTION' => 'Atenção', 'POSITIVE' => 'Positivo', 'INFORMATION' => 'Informativo'];
?>

<section id="<?= e($panelId) ?>" class="intelligence-insights intelligence-panel panel-hover<?= $compact ? ' intelligence-insights--dashboard' : '' ?><?= $actionOriented ? ' intelligence-insights--action-oriented' : '' ?>" aria-labelledby="<?= e($panelId) ?>-title" data-insights-panel data-insights-collapsible="<?= $collapsible ? '1' : '0' ?>" data-insights-collapsed-default="<?= $collapsedByDefault ? '1' : '0' ?>" data-insights-storage-key="<?= e($storageKey) ?>">
    <header class="intelligence-panel__header intelligence-insights__header">
        <div>
            <span class="intelligence-panel__icon"><i data-lucide="sparkles"></i></span>
            <div><h3 id="<?= e($panelId) ?>-title"><?= e($title) ?></h3><p><?= e($subtitle) ?></p></div>
        </div>
        <div class="intelligence-insights__header-actions">
            <span class="intelligence-insights__count" data-insights-count><?= count($insights) ?> <?= $actionOriented ? 'prioridade(s)' : 'insight(s)' ?></span>
            <?php if ($collapsible): ?>
                <button type="button" class="intelligence-insights__toggle" data-insights-toggle aria-expanded="true" aria-controls="<?= e($panelId) ?>-body">
                    <i data-lucide="chevron-up"></i><span>Ocultar painel</span>
                </button>
            <?php endif; ?>
        </div>
    </header>

    <div id="<?= e($panelId) ?>-body" class="intelligence-insights__body" data-insights-body>

    <?php if (count($insights) > 1): ?>
        <div class="intelligence-insights__filters" role="group" aria-label="Filtrar insights">
            <button type="button" class="is-active" data-insight-filter="all">Todos</button>
            <?php if ($actionOriented): ?>
                <button type="button" data-insight-filter="action">Exigem ação</button>
                <button type="button" data-insight-filter="positive">Evolução positiva</button>
            <?php else: ?>
                <button type="button" data-insight-filter="critical">Críticos</button>
                <button type="button" data-insight-filter="attention">Atenção</button>
                <button type="button" data-insight-filter="positive">Positivos</button>
                <?php foreach (array_unique(array_map(static fn(array $item): string => strtoupper((string) ($item['category'] ?? 'SCHOOL')), $insights)) as $category): ?>
                    <button type="button" data-insight-filter="<?= e(strtolower($category)) ?>"><?= e($categoryLabels[$category] ?? ucfirst(strtolower($category))) ?></button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="intelligence-insights__list">
        <?php foreach ($insights as $insight): ?>
            <?php
            $levelKey = strtoupper((string) ($insight['level'] ?? 'INFORMATION'));
            $level = strtolower($levelKey);
            $category = strtoupper((string) ($insight['category'] ?? 'SCHOOL'));
            $impact = is_array($insight['impact'] ?? null) ? $insight['impact'] : ['stars' => 1, 'label' => 'Baixo', 'score' => 20];
            $explanation = is_array($insight['explanation'] ?? null) ? $insight['explanation'] : [];
            $actions = is_array($insight['actions'] ?? null) ? $insight['actions'] : [];
            ?>
            <article class="intelligence-insight intelligence-insight--<?= e($level) ?>" data-insight-item data-level="<?= e($level) ?>" data-category="<?= e(strtolower($category)) ?>" data-action="<?= in_array($levelKey, ['CRITICAL','ATTENTION'], true) ? 'action' : 'information' ?>">
                <span class="intelligence-insight__icon"><i data-lucide="<?= e((string) ($insight['icon'] ?? 'lightbulb')) ?>"></i></span>
                <div class="intelligence-insight__content">
                    <div class="intelligence-insight__heading">
                        <strong><?= e((string) ($insight['title'] ?? 'Insight')) ?></strong>
                        <div class="intelligence-insight__badges">
                            <span><?= e($levelLabels[$levelKey] ?? 'Informativo') ?></span>
                            <?php if (!$actionOriented): ?><span class="intelligence-insight__category"><?= e($categoryLabels[$category] ?? ucfirst(strtolower($category))) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <p><?= e((string) ($insight['description'] ?? '')) ?></p>

                    <?php if (!$actionOriented): ?>
                    <div class="intelligence-insight__meta">
                        <div title="Pontuação calculada pela prioridade, quantidade de casos e intensidade da variação.">
                            <small>Impacto</small><strong><?= e((string) ($impact['label'] ?? 'Baixo')) ?></strong>
                            <span class="intelligence-insight__stars" aria-label="<?= (int) ($impact['stars'] ?? 1) ?> de 5 estrelas"><?php for ($i = 1; $i <= 5; $i++): ?><i data-lucide="star" class="<?= $i <= (int) ($impact['stars'] ?? 1) ? 'is-filled' : '' ?>"></i><?php endfor; ?></span>
                        </div>
                        <div title="Estimativa de quanto a análise está sustentada pelos sinais disponíveis."><small>Confiança</small><strong><?= (int) ($insight['confidence'] ?? 70) ?>%</strong></div>
                    </div>
                    <?php endif; ?>

                    <div class="intelligence-insight__recommendation"><i data-lucide="route"></i><span><strong>Ação sugerida:</strong> <?= e((string) ($insight['recommendation'] ?? '')) ?></span></div>

                    <?php if ($explanation !== []): ?>
                        <details class="intelligence-insight__explanation">
                            <summary><i data-lucide="info"></i> Como este insight foi calculado?</summary>
                            <ul><?php foreach ($explanation as $item): ?><li><?= e((string) $item) ?></li><?php endforeach; ?></ul>
                        </details>
                    <?php endif; ?>
                </div>
                <?php if ($actions !== []): ?>
                    <div class="intelligence-insight__actions">
                        <?php foreach ($actions as $action): ?>
                            <?php if (!empty($action['target'])): ?><a class="intelligence-insight__action" href="<?= base_url((string) $action['target']) ?>"><i data-lucide="<?= e((string) ($action['icon'] ?? 'arrow-up-right')) ?>"></i><span><?= e((string) ($action['label'] ?? 'Abrir')) ?></span></a><?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
    <p class="intelligence-insights__empty" data-insights-empty hidden>Nenhum insight corresponde ao filtro selecionado.</p>
    </div>
</section>
<script>
document.querySelectorAll('[data-insights-panel]').forEach(function (panel) {
    if (panel.dataset.filtersReady === '1') return;
    panel.dataset.filtersReady = '1';
    var buttons = panel.querySelectorAll('[data-insight-filter]');
    var items = panel.querySelectorAll('[data-insight-item]');
    var count = panel.querySelector('[data-insights-count]');
    var empty = panel.querySelector('[data-insights-empty]');
    var collapsible = panel.dataset.insightsCollapsible === '1';
    var toggle = panel.querySelector('[data-insights-toggle]');
    var body = panel.querySelector('[data-insights-body]');
    if (collapsible && toggle && body) {
        var key = panel.dataset.insightsStorageKey || '';
        var collapsed = panel.dataset.insightsCollapsedDefault === '1';
        try {
            var saved = localStorage.getItem(key);
            if (saved !== null) collapsed = saved === '1';
        } catch (error) {}
        var applyState = function () {
            panel.classList.toggle('is-collapsed', collapsed);
            body.hidden = collapsed;
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.querySelector('span').textContent = collapsed ? 'Mostrar painel' : 'Ocultar painel';
            var icon = toggle.querySelector('[data-lucide]');
            if (icon) icon.setAttribute('data-lucide', collapsed ? 'chevron-down' : 'chevron-up');
            if (window.lucide) window.lucide.createIcons();
        };
        applyState();
        toggle.addEventListener('click', function () {
            collapsed = !collapsed;
            try { localStorage.setItem(key, collapsed ? '1' : '0'); } catch (error) {}
            applyState();
        });
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            var filter = button.dataset.insightFilter;
            var visible = 0;
            buttons.forEach(function (item) { item.classList.toggle('is-active', item === button); });
            items.forEach(function (item) {
                var show = filter === 'all' || item.dataset.level === filter || item.dataset.category === filter || item.dataset.action === filter;
                item.hidden = !show;
                if (show) visible++;
            });
            if (count) count.textContent = visible + (panel.classList.contains('intelligence-insights--action-oriented') ? ' prioridade(s)' : ' insight(s)');
            if (empty) empty.hidden = visible !== 0;
        });
    });
});
</script>
