<?php
$index = is_array($index ?? null) ? $index : [];
$details = is_array($index['details'] ?? null) ? $index['details'] : [];
$comparisons = is_array($index['comparisons'] ?? null) ? $index['comparisons'] : [];
$trend = is_array($index['trend'] ?? null) ? $index['trend'] : [];
$points = is_array($trend['points'] ?? null) ? $trend['points'] : [];
?>
<section id="school-index-details" class="school-index-details" data-school-index-details hidden>
    <header class="school-index-details__header">
        <div><span><i data-lucide="chart-no-axes-combined"></i></span><div><h3>Como o índice foi calculado</h3><p>Composição transparente da pontuação, comparação histórica e tendência.</p></div></div>
        <button type="button" data-school-index-close><i data-lucide="x"></i><span>Fechar detalhes</span></button>
    </header>

    <div class="school-index-breakdown">
        <?php foreach ($details as $item): ?>
            <?php $percent = (float)$item['max'] > 0 ? ((float)$item['score'] / (float)$item['max']) * 100 : 0; ?>
            <article>
                <div><strong><?= e((string)$item['label']) ?></strong><span><?= number_format((float)$item['score'],1,',','.') ?> / <?= e((string)$item['max']) ?></span></div>
                <div class="school-index-bar"><span style="width:<?= max(0,min(100,$percent)) ?>%"></span></div>
                <p><?= e((string)$item['description']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="school-index-secondary-grid">
        <article class="school-index-comparison">
            <h4><i data-lucide="git-compare-arrows"></i> Comparação entre períodos</h4>
            <div class="school-index-comparison-list">
                <?php foreach ($comparisons as $comparison): ?>
                    <div><span><?= e((string)$comparison['label']) ?></span>
                        <?php if ($comparison['score'] === null): ?><strong>Sem histórico</strong>
                        <?php else: ?><strong><?= (int)$comparison['score'] ?>/100 <small><?= (int)$comparison['difference'] > 0 ? '▲ +' : ((int)$comparison['difference'] < 0 ? '▼ ' : '• ') ?><?= (int)$comparison['difference'] ?></small></strong><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
        <article class="school-index-trend">
            <h4><i data-lucide="trending-up"></i> Tendência: <?= e((string)($trend['status'] ?? 'Estável')) ?></h4>
            <?php if (count($points) >= 2): ?>
                <div class="school-index-sparkline" aria-label="Evolução recente do índice">
                    <?php foreach ($points as $point): ?><div title="<?= e((string)$point['date']) ?>: <?= (int)$point['score'] ?>"><span style="height:<?= max(12,(int)$point['score']) ?>%"></span><small><?= e((string)$point['date']) ?></small></div><?php endforeach; ?>
                </div>
            <?php else: ?><p class="school-index-no-history">O histórico começará a ser exibido conforme os snapshots diários forem registrados.</p><?php endif; ?>
        </article>
    </div>

    <div class="school-index-analysis"><i data-lucide="lightbulb"></i><p>A principal contribuição positiva vem de <strong><?= e((string)($index['main_positive'] ?? 'Frequência')) ?></strong>. O fator que mais merece atenção é <strong><?= e((string)($index['main_attention'] ?? 'Ocorrências')) ?></strong>.</p></div>
</section>
<script>
(function(){
 var toggle=document.querySelector('[data-school-index-toggle]');
 var panel=document.querySelector('[data-school-index-details]');
 var close=document.querySelector('[data-school-index-close]');
 if(!toggle||!panel)return;
 function set(open){panel.hidden=!open;toggle.setAttribute('aria-expanded',open?'true':'false');if(open){panel.scrollIntoView({behavior:'smooth',block:'start'});}if(window.lucide)window.lucide.createIcons();}
 toggle.addEventListener('click',function(){set(panel.hidden);});
 if(close)close.addEventListener('click',function(){set(false);toggle.focus();});
})();
</script>
