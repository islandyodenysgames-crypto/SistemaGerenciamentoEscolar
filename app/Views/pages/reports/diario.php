<?php
component('base/page-header', [
    'title' => 'Relatório Diário de Frequência',
    'subtitle' => 'Acompanhe a cobertura das chamadas, a frequência e o desempenho das turmas na data selecionada.',
]);

$reportTimestamp = strtotime((string) ($date ?? ''));
$reportDateLabel = $reportTimestamp !== false ? date('d/m/Y', $reportTimestamp) : 'Data não informada';
$goal = (float) ($goal ?? 95);
$percentage = (float) ($summary['percentage'] ?? 0);
$goalReached = $percentage >= $goal;
$totalRecords = (int) ($summary['total_students'] ?? 0);
$justifiedTotal = (int) ($summary['justificadas'] ?? 0) + (int) ($summary['atestados'] ?? 0) + (int) ($summary['onibus'] ?? 0);
?>

<section class="attendance-report-filter card attendance-daily-filter reports-print-hidden">
    <div class="attendance-report-section-heading">
        <div class="attendance-report-heading-icon"><i data-lucide="calendar-range"></i></div>
        <div>
            <span class="attendance-report-eyebrow">Data analisada</span>
            <h2><?= e($reportDateLabel) ?></h2>
            <p>Altere a data para consultar outro dia letivo.</p>
        </div>
    </div>

    <form method="GET" action="<?= base_url('relatorios/diario') ?>" class="attendance-daily-filter-form">
        <div class="form-group">
            <label for="dailyAttendanceDate">Data</label>
            <input id="dailyAttendanceDate" class="form-control" type="date" name="data" value="<?= e($date) ?>">
        </div>
        <div class="attendance-report-filter-actions">
            <button type="submit" class="btn-primary"><i data-lucide="search"></i> Consultar</button>
            <button type="button" class="btn-secondary" data-report-print><i data-lucide="printer"></i> Imprimir/PDF</button>
            <a href="<?= base_url('relatorios') ?>" class="btn-secondary"><i data-lucide="arrow-left"></i> Voltar</a>
        </div>
    </form>
</section>

<section class="reports-document reports-daily-document card">
    <header class="reports-document-header">
        <div>
            <span class="attendance-report-eyebrow">Documento gerado em <?= e($generatedAt ?? date('d/m/Y H:i')) ?></span>
            <h2>Relatório diário de frequência</h2>
            <p><?= e($reportDateLabel) ?> · Visão geral da escola</p>
        </div>
        <div class="reports-document-score <?= $goalReached ? 'is-good' : 'is-alert' ?>">
            <small>Frequência do dia</small>
            <strong><?= number_format($percentage, 1, ',', '.') ?>%</strong>
            <span>Meta <?= number_format($goal, 1, ',', '.') ?>%</span>
        </div>
    </header>

    <div class="reports-kpi-grid reports-daily-kpi-grid">
        <article><span>Cobertura das chamadas</span><strong><?= number_format((float) ($coveragePercentage ?? 0), 1, ',', '.') ?>%</strong><small><?= (int) ($completedClasses ?? 0) ?> de <?= (int) ($totalClasses ?? 0) ?> turmas</small></article>
        <article><span>Registros</span><strong><?= $totalRecords ?></strong><small>Alunos computados</small></article>
        <article><span>Presentes</span><strong><?= (int) ($summary['presentes'] ?? 0) ?></strong><small>Na data selecionada</small></article>
        <article><span>Faltas</span><strong><?= (int) ($summary['faltas'] ?? 0) ?></strong><small>Sem justificativa</small></article>
        <article><span>Justificadas</span><strong><?= $justifiedTotal ?></strong><small>FJ, AM e FO</small></article>
        <article><span>Chamadas pendentes</span><strong><?= (int) ($pendingClasses ?? 0) ?></strong><small><?= (int) ($completedClasses ?? 0) ?> concluídas</small></article>
    </div>

    <section class="reports-section">
        <div class="reports-section-title"><div><span>Desempenho</span><h3>Ranking das turmas</h3></div><i data-lucide="trophy"></i></div>
        <div class="reports-daily-component reports-ranking-print-scope">
            <?php component('dashboard/ranking', [
                'ranking' => $ranking,
                'goalPercentage' => $goal,
                'generalPercentage' => $percentage,
                'presentes' => (int) ($summary['presentes'] ?? 0),
                'faltas' => (int) ($summary['faltas'] ?? 0),
                'totalClasses' => (int) ($totalClasses ?? 0),
            ]); ?>
        </div>
    </section>

    <section class="reports-section">
        <div class="reports-section-title"><div><span>Acompanhamento</span><h3>Turmas sem chamada</h3></div><span class="reports-count-badge"><?= (int) ($pendingClasses ?? 0) ?></span></div>
        <div class="reports-daily-component reports-pending-print-scope">
            <?php component('dashboard/pending', ['classesWithoutAttendance' => $classesWithoutAttendance]); ?>
        </div>
    </section>
</section>
