<?php
component('base/page-header', [
    'title' => 'Relatório de Ocorrências',
    'subtitle' => 'Análise detalhada dos registros disciplinares e pedagógicos da escola.',
]);
$periodLabel = date('d/m/Y', strtotime($startDate)) . ' a ' . date('d/m/Y', strtotime($endDate));
$classLabel = $selectedClass ? ($selectedClass['name'] . ' · ' . $selectedClass['shift']) : 'Todas as turmas';
?>
<section class="attendance-report-filter card reports-print-hidden">
    <div class="attendance-report-section-heading">
        <div class="attendance-report-heading-icon"><i data-lucide="list-filter"></i></div>
        <div><span class="attendance-report-eyebrow">Filtros do relatório</span><h2><?= e($periodLabel) ?></h2><p><?= e($classLabel) ?></p></div>
    </div>
    <form method="get" action="<?= base_url('relatorios/ocorrencias') ?>" class="reports-occurrence-filter">
        <div class="form-group"><label>Data inicial</label><input class="form-control" type="date" name="inicio" value="<?= e($startDate) ?>"></div>
        <div class="form-group"><label>Data final</label><input class="form-control" type="date" name="fim" value="<?= e($endDate) ?>"></div>
        <div class="form-group"><label>Turma</label><select class="form-control" name="turma"><option value="0">Todas as turmas</option><?php foreach($classes as $class): ?><option value="<?= (int)$class['id'] ?>" <?= (int)($selectedClass['id'] ?? 0)===(int)$class['id']?'selected':'' ?>><?= e($class['name'].' · '.$class['shift']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Tipo</label><select class="form-control" name="tipo"><option value="">Todos os tipos</option><?php foreach($occurrenceTypes as $code=>$label): ?><option value="<?= e($code) ?>" <?= $selectedType===$code?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Gravidade</label><select class="form-control" name="gravidade"><option value="">Todas</option><?php foreach($severityLabels as $code=>$label): ?><option value="<?= e($code) ?>" <?= $selectedSeverity===$code?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Situação</label><select class="form-control" name="status"><option value="">Todas</option><?php foreach($statusLabels as $code=>$label): ?><option value="<?= e($code) ?>" <?= $selectedStatus===$code?'selected':'' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <div class="attendance-report-filter-actions"><button type="submit" class="btn-primary"><i data-lucide="search"></i> Atualizar</button><button class="btn-secondary" type="button" data-report-print><i data-lucide="printer"></i> Imprimir/PDF</button></div>
    </form>
</section>

<section class="reports-document card">
    <header class="reports-document-header">
        <div><span class="attendance-report-eyebrow">Documento gerado em <?= e($generatedAt) ?></span><h2>Ocorrências escolares</h2><p><?= e($periodLabel) ?> · <?= e($classLabel) ?></p></div>
        <div class="reports-document-score <?= (int)$summary['serious_count'] > 0 ? 'is-alert':'is-good' ?>"><small>Total</small><strong><?= (int)$summary['total'] ?></strong><span><?= (int)$summary['serious_count'] ?> graves</span></div>
    </header>

    <div class="reports-kpi-grid">
        <article><span>Ocorrências</span><strong><?= (int)$summary['total'] ?></strong><small>No período</small></article>
        <article><span>Abertas</span><strong><?= (int)$summary['open_count'] ?></strong><small>Aguardando conclusão</small></article>
        <article><span>Resolvidas</span><strong><?= (int)$summary['resolved_count'] ?></strong><small>Resolvidas ou encerradas</small></article>
        <article><span>Graves</span><strong><?= (int)$summary['serious_count'] ?></strong><small>Alta ou crítica</small></article>
        <article><span>Alunos envolvidos</span><strong><?= (int)$summary['affected_students'] ?></strong><small>Alunos distintos</small></article>
        <article><span>Turmas envolvidas</span><strong><?= (int)$summary['affected_classes'] ?></strong><small>Turmas distintas</small></article>
    </div>

    <div class="reports-analysis-grid">
        <section class="reports-section reports-compact-panel"><div class="reports-section-title"><div><span>Distribuição</span><h3>Por tipo</h3></div><i data-lucide="tags"></i></div><ul class="reports-ranking-list"><?php foreach($byType as $item): ?><li><span><?= e($item['label']) ?></span><strong><?= (int)$item['total'] ?></strong></li><?php endforeach; ?><?php if(!$byType): ?><li><span>Sem dados</span><strong>0</strong></li><?php endif; ?></ul></section>
        <section class="reports-section reports-compact-panel"><div class="reports-section-title"><div><span>Distribuição</span><h3>Por gravidade</h3></div><i data-lucide="triangle-alert"></i></div><ul class="reports-ranking-list"><?php foreach($bySeverity as $item): ?><li><span><?= e($item['label']) ?></span><strong><?= (int)$item['total'] ?></strong></li><?php endforeach; ?><?php if(!$bySeverity): ?><li><span>Sem dados</span><strong>0</strong></li><?php endif; ?></ul></section>
        <section class="reports-section reports-compact-panel"><div class="reports-section-title"><div><span>Distribuição</span><h3>Por situação</h3></div><i data-lucide="circle-check-big"></i></div><ul class="reports-ranking-list"><?php foreach($byStatus as $item): ?><li><span><?= e($item['label']) ?></span><strong><?= (int)$item['total'] ?></strong></li><?php endforeach; ?><?php if(!$byStatus): ?><li><span>Sem dados</span><strong>0</strong></li><?php endif; ?></ul></section>
        <section class="reports-section reports-compact-panel"><div class="reports-section-title"><div><span>Distribuição</span><h3>Por disciplina</h3></div><i data-lucide="book-open"></i></div><ul class="reports-ranking-list"><?php foreach($bySubject as $item): ?><li><span><?= e($item['label']) ?></span><strong><?= (int)$item['total'] ?></strong></li><?php endforeach; ?><?php if(!$bySubject): ?><li><span>Sem dados</span><strong>0</strong></li><?php endif; ?></ul></section>
    </div>

    <section class="reports-section">
        <div class="reports-section-title"><div><span>Detalhamento</span><h3>Registros do período</h3></div><span class="reports-count-badge"><?= count($records) ?></span></div>
        <div class="table-responsive attendance-report-table-wrap"><table class="table attendance-report-table reports-table"><thead><tr><th>Data</th><th>Aluno</th><th>Turma</th><th>Tipo</th><th>Gravidade</th><th>Situação</th><th>Disciplina</th><th>Registrado por</th><th>Título</th></tr></thead><tbody><?php foreach($records as $row): ?><tr><td><?= e(date('d/m/Y',strtotime($row['occurrence_date']))) ?></td><td><strong><?= e($row['student_name']) ?></strong></td><td><?= e($row['class_name'] ?: 'Sem turma') ?></td><td><?= e($row['type_label']) ?></td><td><?= e($row['severity_label']) ?></td><td><?= e($row['status_label']) ?></td><td><?= e($row['subject_name']) ?></td><td><?= e($row['author_name']) ?></td><td><?= e($row['title']) ?></td></tr><?php endforeach; ?><?php if(!$records): ?><tr><td colspan="9" class="reports-empty-cell">Nenhuma ocorrência encontrada com os filtros selecionados.</td></tr><?php endif; ?></tbody></table></div>
    </section>
</section>
