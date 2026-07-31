<?php
$index = is_array($index ?? null) ? $index : [];
$score = (int)($index['score'] ?? 0);
$tone = (string)($index['tone'] ?? 'blue');
$trend = (array)($index['trend'] ?? []);
$difference = (int)($trend['difference'] ?? 0);
?>
<button type="button" class="manager-card manager-card-<?= e($tone) ?> school-index-card" data-school-index-toggle aria-expanded="false" aria-controls="school-index-details">
    <div class="manager-card-icon"><i data-lucide="gauge"></i></div>
    <h3>Índice Geral da Escola <span title="Síntese de frequência, ocorrências, risco, acompanhamentos e turmas.">i</span></h3>
    <?php component('base/progress', [
        'percentage'=>$score, 'value'=>$score . '/100', 'label'=>'Índice atual', 'size'=>190,
        'color'=>$tone === 'green' ? 'var(--success)' : ($tone === 'yellow' ? 'var(--warning)' : ($tone === 'red' ? 'var(--danger)' : 'var(--info)'))
    ]); ?>
    <div class="manager-card-status"><?= e((string)($index['label'] ?? 'Sem classificação')) ?></div>
    <div class="manager-card-meta"><?= $difference > 0 ? '▲ +' : ($difference < 0 ? '▼ ' : '• ') ?><?= e((string)$difference) ?> ponto(s) na tendência</div>
    <div class="manager-card-action"><i data-lucide="chart-line"></i><span>Ver composição e tendência</span><i data-lucide="chevron-down"></i></div>
</button>
