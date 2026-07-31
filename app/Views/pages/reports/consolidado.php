<?php
component('base/page-header', [
    'title' => 'Relatório Consolidado',
    'subtitle' => 'Frequência, ocorrências e indicadores escolares reunidos em uma única análise.',
]);
$periodLabel = date('d/m/Y', strtotime($startDate)) . ' a ' . date('d/m/Y', strtotime($endDate));
$classLabel = $selectedClass ? ($selectedClass['name'] . ' · ' . $selectedClass['shift']) : 'Relatório consolidado da escola';
$percentage = (float)($summary['percentage'] ?? 0);
$goalReached = $percentage >= (float)$goal;
$query = http_build_query(['inicio'=>$startDate,'fim'=>$endDate,'turma'=>$selectedClass['id'] ?? 0]);
?>
<section class="attendance-report-filter card reports-print-hidden">
    <div class="attendance-report-section-heading">
        <div class="attendance-report-heading-icon"><i data-lucide="list-filter"></i></div>
        <div><span class="attendance-report-eyebrow">Filtros do relatório</span><h2><?= e($periodLabel) ?></h2><p><?= e($classLabel) ?></p></div>
    </div>
    <form method="get" action="<?= base_url('relatorios/consolidado') ?>" class="reports-consolidated-filter">
        <div class="form-group"><label for="consolidatedStart">Data inicial</label><input class="form-control" id="consolidatedStart" type="date" name="inicio" value="<?= e($startDate) ?>"></div>
        <div class="form-group"><label for="consolidatedEnd">Data final</label><input class="form-control" id="consolidatedEnd" type="date" name="fim" value="<?= e($endDate) ?>"></div>
        <div class="form-group"><label for="consolidatedClass">Turma</label><select class="form-control" id="consolidatedClass" name="turma"><option value="0">Relatório consolidado da escola</option><?php foreach($classes as $class): ?><option value="<?= (int)$class['id'] ?>" <?= (int)($selectedClass['id'] ?? 0)===(int)$class['id']?'selected':'' ?>><?= e($class['name'].' · '.$class['shift']) ?></option><?php endforeach; ?></select></div>
        <div class="attendance-report-filter-actions"><button type="submit" class="btn-primary"><i data-lucide="search"></i> Atualizar</button><a class="btn-secondary" href="<?= base_url('relatorios/exportar-csv?' . $query) ?>"><i data-lucide="sheet"></i> CSV</a><button class="btn-secondary" type="button" data-report-print><i data-lucide="printer"></i> Imprimir/PDF</button></div>
    </form>
</section>

<section class="reports-document card">
    <header class="reports-document-header">
        <div><span class="attendance-report-eyebrow">Documento gerado em <?= e($generatedAt) ?></span><h2>Relatório de desempenho escolar</h2><p><?= e($periodLabel) ?> · <?= e($classLabel) ?></p></div>
        <div class="reports-document-score <?= $goalReached ? 'is-good':'is-alert' ?>"><small>Frequência</small><strong><?= number_format($percentage,1,',','.') ?>%</strong><span>Meta <?= number_format((float)$goal,1,',','.') ?>%</span></div>
    </header>

    <div class="reports-kpi-grid">
        <article><span>Registros</span><strong><?= (int)$summary['total_records'] ?></strong><small><?= (int)$summary['school_days'] ?> dias com chamada</small></article>
        <article><span>Presentes</span><strong><?= (int)$summary['presentes'] ?></strong><small><?= number_format($percentage,1,',','.') ?>% do total</small></article>
        <article><span>Faltas</span><strong><?= (int)$summary['faltas'] ?></strong><small>Sem justificativa</small></article>
        <article><span>Justificadas</span><strong><?= (int)$summary['justificadas'] + (int)$summary['atestados'] + (int)$summary['onibus'] ?></strong><small>FJ, AM e FO</small></article>
        <article><span>Ocorrências</span><strong><?= (int)$occurrences['total'] ?></strong><small><?= (int)$occurrences['open_count'] ?> abertas</small></article>
        <article><span>Alunos em alerta</span><strong><?= count($studentsBelowGoal) ?></strong><small>Abaixo de <?= number_format((float)$goal,1,',','.') ?>%</small></article>
    </div>

    <section class="reports-section">
        <div class="reports-section-title"><div><span>Desempenho</span><h3>Frequência das turmas</h3></div><i data-lucide="school"></i></div>
        <div class="table-responsive attendance-report-table-wrap"><table class="table attendance-report-table reports-table"><thead><tr><th>Turma</th><th>Turno</th><th>Dias</th><th>Registros</th><th>Presentes</th><th>Faltas</th><th>Justificadas</th><th>Frequência</th></tr></thead><tbody><?php foreach($classPerformance as $row): $p=(float)($row['percentage']??0); ?><tr><td><strong><?= e($row['class_name']) ?></strong></td><td><?= e($row['shift']) ?></td><td><?= (int)$row['school_days'] ?></td><td><?= (int)$row['total_records'] ?></td><td><?= (int)$row['presentes'] ?></td><td><?= (int)$row['faltas'] ?></td><td><?= (int)$row['justificadas'] ?></td><td><span class="reports-rate <?= $p >= $goal ? 'is-good':'is-alert' ?>"><?= number_format($p,1,',','.') ?>%</span></td></tr><?php endforeach; ?><?php if(!$classPerformance): ?><tr><td colspan="8" class="reports-empty-cell">Nenhum registro no período.</td></tr><?php endif; ?></tbody></table></div>
    </section>

    <section class="reports-section">
        <div class="reports-section-title"><div><span>Detalhamento</span><h3>Alunos do período</h3></div><span class="reports-count-badge"><?= count($studentPerformance) ?></span></div>
        <div class="table-responsive attendance-report-table-wrap">
            <table class="table attendance-report-table reports-table reports-students-table">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Matrícula</th>
                        <th>Turma</th>
                        <th>Registros</th>
                        <th>Presentes</th>
                        <th>Faltas</th>
                        <th>FJ</th>
                        <th>AM</th>
                        <th>FO</th>
                        <th>Frequência</th>
                        <th>Situação</th>
                        <th class="reports-print-hidden">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($studentPerformance as $row):
                        $studentPercentage = (float)($row['percentage'] ?? 0);
                        $totalRecords = (int)($row['total_records'] ?? 0);
                        $hasRecords = $totalRecords > 0;
                        $withinGoal = $hasRecords && $studentPercentage >= (float)$goal;
                        $situationLabel = !$hasRecords ? 'Sem registros' : ($withinGoal ? 'Dentro da meta' : 'Abaixo da meta');
                        $situationClass = !$hasRecords ? 'is-neutral' : ($withinGoal ? 'is-good' : 'is-alert');
                    ?>
                    <tr>
                        <td><strong><?= e($row['student_name']) ?></strong></td>
                        <td><?= e($row['registration']) ?></td>
                        <td><?= e($row['class_name']) ?></td>
                        <td><?= $totalRecords ?></td>
                        <td><?= (int)$row['presentes'] ?></td>
                        <td><?= (int)$row['faltas'] ?></td>
                        <td><?= (int)$row['faltas_justificadas'] ?></td>
                        <td><?= (int)$row['atestados'] ?></td>
                        <td><?= (int)$row['faltas_onibus'] ?></td>
                        <td><span class="reports-rate <?= $situationClass ?>"><?= $hasRecords ? number_format($studentPercentage,1,',','.') . '%' : '—' ?></span></td>
                        <td><span class="reports-status <?= $situationClass ?>"><?= e($situationLabel) ?></span></td>
                        <td class="reports-print-hidden"><a class="btn-secondary btn-sm" href="<?= base_url('alunos/perfil?id='.(int)$row['student_id']) ?>">Ver aluno</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(!$studentPerformance): ?><tr><td colspan="12" class="reports-empty-cell">Nenhum aluno encontrado para o período e filtro selecionados.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <p class="reports-table-note"><strong>Legenda:</strong> FJ = falta justificada, AM = atestado médico e FO = falta de ônibus.</p>
    </section>

    <div class="reports-two-columns">
        <section class="reports-section reports-compact-panel"><div class="reports-section-title"><div><span>Ocorrências</span><h3>Resumo do período</h3></div><i data-lucide="shield-alert"></i></div><div class="reports-mini-stats"><div><strong><?= (int)$occurrences['total'] ?></strong><span>Total</span></div><div><strong><?= (int)$occurrences['open_count'] ?></strong><span>Abertas</span></div><div><strong><?= (int)$occurrences['serious_count'] ?></strong><span>Graves</span></div><div><strong><?= (int)$occurrences['affected_students'] ?></strong><span>Alunos</span></div></div><?php if($occurrencesByType): ?><ul class="reports-ranking-list"><?php foreach($occurrencesByType as $item): ?><li><span><?= e($item['label'] ?? $item['type']) ?></span><strong><?= (int)$item['total'] ?></strong></li><?php endforeach; ?></ul><?php endif; ?></section>
        <section class="reports-section reports-compact-panel"><div class="reports-section-title"><div><span>Tendência</span><h3>Frequência diária</h3></div><i data-lucide="trending-up"></i></div><div class="reports-trend-list"><?php foreach(array_slice($trend,-12) as $item): $p=(float)$item['percentage']; ?><div title="<?= e(date('d/m/Y',strtotime($item['date']))) ?> · <?= number_format($p,1,',','.') ?>%"><span><?= date('d/m',strtotime($item['date'])) ?></span><div><i style="width:<?= max(2,min(100,$p)) ?>%"></i></div><strong><?= number_format($p,1,',','.') ?>%</strong></div><?php endforeach; ?><?php if(!$trend): ?><p class="reports-empty-cell">Sem dados para apresentar tendência.</p><?php endif; ?></div></section>
    </div>
</section>
