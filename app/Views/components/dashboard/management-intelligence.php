<?php
$intelligence=(array)($intelligence??[]);
$students=(array)($intelligence['students']??[]);
$classes=(array)($intelligence['classes']??[]);
$improving=(array)($intelligence['improving_classes']??[]);
$monitoring=(array)($intelligence['monitoring']??[]);
$riskCounts=['CRITICAL'=>0,'HIGH'=>0,'MODERATE'=>0];
foreach($students as $student){$level=strtoupper((string)($student['risk_level']??''));if(isset($riskCounts[$level]))$riskCounts[$level]++;}
?>
<section class="card management-intelligence-panel">
    <div class="card-header"><div><h3><i data-lucide="chart-no-axes-combined"></i> Visão gerencial</h3><p>Comparação operacional dos sinais de risco, evolução das turmas e cobertura dos acompanhamentos.</p></div><a class="table-link" href="<?= base_url('inteligencia/comparacoes') ?>">Ver comparações</a></div>
    <div class="management-intelligence-grid">
        <article><span>Distribuição de risco</span><div class="risk-distribution"><b class="is-critical"><?= $riskCounts['CRITICAL'] ?> crítico(s)</b><b class="is-high"><?= $riskCounts['HIGH'] ?> alto(s)</b><b class="is-moderate"><?= $riskCounts['MODERATE'] ?> atenção</b></div></article>
        <article><span>Cobertura dos acompanhamentos</span><strong><?= (int)($monitoring['covered_students']??0) ?> de <?= (int)($monitoring['priority_students']??count($students)) ?></strong><small><?= (int)($monitoring['coverage_percentage']??0) ?>% dos alunos prioritários cobertos</small></article>
        <article><span>Turmas em atenção</span><strong><?= count($classes) ?></strong><small><?= $classes!==[] ? e((string)($classes[0]['name']??'Turma prioritária')).' lidera a atenção atual' : 'Nenhuma turma prioritária no período' ?></small></article>
        <article><span>Turmas em melhora</span><strong><?= count($improving) ?></strong><small><?= $improving!==[] ? e((string)($improving[0]['name']??'Turma')).' apresenta a melhor evolução' : 'Histórico ainda insuficiente para destacar evolução' ?></small></article>
    </div>
</section>
