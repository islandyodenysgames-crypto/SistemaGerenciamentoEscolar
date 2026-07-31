<?php
$frequency = (array)($frequency ?? []);
$occurrences = (array)($occurrences ?? []);
$goal = (float)($goal ?? 95);
$frequencyValue = (float)($frequency['percentage'] ?? 0);
$statusClass = $frequencyValue >= $goal ? 'is-good' : ($frequencyValue >= max(0, $goal - 5) ? 'is-warning' : 'is-critical');
?>
<section class="reports-overview-grid">
    <article class="report-overview-card card <?= $statusClass ?>">
        <span class="report-overview-icon"><i data-lucide="activity"></i></span>
        <div><small>Frequência — últimos 30 dias</small><strong><?= number_format($frequencyValue, 1, ',', '.') ?>%</strong><span>Meta da escola: <?= number_format($goal, 1, ',', '.') ?>%</span></div>
    </article>
    <article class="report-overview-card card">
        <span class="report-overview-icon"><i data-lucide="users"></i></span>
        <div><small>Alunos ativos</small><strong><?= (int)($totalStudents ?? 0) ?></strong><span><?= (int)($studentsBelowGoal ?? 0) ?> abaixo da meta no período</span></div>
    </article>
    <article class="report-overview-card card">
        <span class="report-overview-icon"><i data-lucide="school"></i></span>
        <div><small>Turmas ativas</small><strong><?= (int)($totalClasses ?? 0) ?></strong><span>Visão comparativa disponível</span></div>
    </article>
    <article class="report-overview-card card">
        <span class="report-overview-icon"><i data-lucide="triangle-alert"></i></span>
        <div><small>Ocorrências — últimos 30 dias</small><strong><?= (int)($occurrences['total'] ?? 0) ?></strong><span><?= (int)($occurrences['open_count'] ?? 0) ?> abertas · <?= (int)($occurrences['serious_count'] ?? 0) ?> graves</span></div>
    </article>
</section>

<section class="reports-launcher card">
    <div class="reports-launcher-copy">
        <span class="attendance-report-eyebrow">Relatório principal</span>
        <h2>Relatório consolidado da escola</h2>
        <p>Analise frequência, desempenho das turmas, alunos abaixo da meta, ocorrências e tendência diária no mesmo documento.</p>
    </div>
    <form action="<?= base_url('relatorios/consolidado') ?>" method="get" class="reports-quick-filter">
        <div class="form-group"><label for="reportStart">Início</label><input class="form-control" id="reportStart" type="date" name="inicio" value="<?= e($periodStart ?? date('Y-m-d', strtotime('-29 days'))) ?>"></div>
        <div class="form-group"><label for="reportEnd">Fim</label><input class="form-control" id="reportEnd" type="date" name="fim" value="<?= e($periodEnd ?? date('Y-m-d')) ?>"></div>
        <button class="btn-primary" type="submit"><i data-lucide="file-chart-column"></i> Gerar relatório</button>
    </form>
</section>

<div class="reports-catalog-grid">
    <a href="<?= base_url('relatorios/diario') ?>" class="report-catalog-card card">
        <span class="report-catalog-icon"><i data-lucide="calendar-days"></i></span><div><h3>Relatório diário</h3><p>Resultado das chamadas, ranking do dia e turmas sem registro.</p></div><i data-lucide="arrow-right"></i>
    </a>
    <a href="<?= base_url('relatorios/consolidado?inicio=' . date('Y-m-01') . '&fim=' . date('Y-m-d')) ?>" class="report-catalog-card card">
        <span class="report-catalog-icon"><i data-lucide="chart-no-axes-combined"></i></span><div><h3>Este mês</h3><p>Visão consolidada do mês atual com comparação entre turmas.</p></div><i data-lucide="arrow-right"></i>
    </a>
    <a href="<?= base_url('relatorios/consolidado?inicio=' . date('Y-01-01') . '&fim=' . date('Y-m-d')) ?>" class="report-catalog-card card">
        <span class="report-catalog-icon"><i data-lucide="landmark"></i></span><div><h3>Ano letivo</h3><p>Panorama acumulado da frequência e das ocorrências da escola.</p></div><i data-lucide="arrow-right"></i>
    </a>
    <a href="<?= base_url('relatorios/ocorrencias') ?>" class="report-catalog-card card">
        <span class="report-catalog-icon"><i data-lucide="shield-alert"></i></span><div><h3>Relatório de ocorrências</h3><p>Analise tipos, gravidades, situação, disciplinas e registros detalhados.</p></div><i data-lucide="arrow-right"></i>
    </a>
    <a href="<?= base_url('frequencia/historico') ?>" class="report-catalog-card card">
        <span class="report-catalog-icon"><i data-lucide="clipboard-list"></i></span><div><h3>Histórico de chamadas</h3><p>Consulte e gerencie os registros de frequência já realizados.</p></div><i data-lucide="arrow-right"></i>
    </a>
</div>
