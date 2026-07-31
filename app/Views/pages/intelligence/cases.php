<?php
$data = $cases ?? [];
$meta = $data['meta'] ?? [];
$period = $data['period'] ?? [];
$summary = $data['summary'] ?? [];
$items = $data['items'] ?? [];
$filters = $data['filters'] ?? [];
$pagination = $data['pagination'] ?? [];
$kind = (string) ($data['kind'] ?? 'students');
$type = (string) ($data['type'] ?? 'students_attention');
$classes = $data['classes'] ?? [];
$riskLabels = ['LOW' => 'Baixo', 'MODERATE' => 'Atenção', 'HIGH' => 'Alto', 'CRITICAL' => 'Crítico'];

component('base/page-header', [
    'title' => (string) ($meta['title'] ?? 'Casos inteligentes'),
    'subtitle' => (string) ($meta['subtitle'] ?? ''),
]);

$queryBase = ['tipo' => $type, 'q' => $filters['q'] ?? '', 'risco' => $filters['risk'] ?? '', 'turma' => $filters['class'] ?? '', 'por_pagina' => $filters['per_page'] ?? 10];
?>
<div class="intelligence-page intelligence-cases-page">
    <section class="intelligence-hero intelligence-cases-hero">
        <div>
            <a class="intelligence-back-link" href="<?= base_url('inteligencia') ?>"><i data-lucide="arrow-left"></i> Voltar à Central</a>
            <span class="intelligence-eyebrow"><i data-lucide="<?= e((string) ($meta['icon'] ?? 'brain-circuit')) ?>"></i> Central de casos</span>
            <h2><?= e((string) ($meta['title'] ?? 'Casos inteligentes')) ?></h2>
            <p><?= e((string) ($meta['subtitle'] ?? '')) ?></p>
        </div>
        <div class="intelligence-period">
            <i data-lucide="calendar-range"></i>
            <span>Período analisado</span>
            <strong><?= e(date('d/m/Y', strtotime((string) ($period['start'] ?? 'now')))) ?> a <?= e(date('d/m/Y', strtotime((string) ($period['end'] ?? 'now')))) ?></strong>
        </div>
    </section>

    <section class="intelligence-case-summary">
        <?php foreach ($summary as $card): ?>
            <article class="intelligence-case-summary__card">
                <span><i data-lucide="<?= e((string) ($card['icon'] ?? 'chart-no-axes-column')) ?>"></i></span>
                <div><strong><?= (int) ($card['value'] ?? 0) ?><?= e((string) ($card['suffix'] ?? '')) ?></strong><small><?= e((string) ($card['label'] ?? 'Indicador')) ?></small></div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="intelligence-panel">
        <header class="intelligence-panel__header">
            <div><span class="intelligence-panel__icon"><i data-lucide="list-filter"></i></span><div><h3>Casos encontrados</h3><p>Pesquise e filtre os registros que compõem o indicador.</p></div></div>
        </header>

        <form class="intelligence-case-filters" method="get" action="<?= base_url('inteligencia/casos') ?>">
            <input type="hidden" name="tipo" value="<?= e($type) ?>">
            <label class="intelligence-case-search"><i data-lucide="search"></i><input type="search" name="q" value="<?= e((string) ($filters['q'] ?? '')) ?>" placeholder="Pesquisar aluno, turma ou ocorrência"></label>
            <?php if ($kind !== 'occurrences'): ?>
                <select name="risco" aria-label="Filtrar por risco">
                    <option value="">Todos os riscos</option>
                    <?php foreach ($riskLabels as $value => $label): ?><option value="<?= e($value) ?>" <?= ($filters['risk'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php if ($classes !== []): ?>
                <select name="turma" aria-label="Filtrar por turma">
                    <option value="">Todas as turmas</option>
                    <?php foreach ($classes as $className): ?><option value="<?= e((string) $className) ?>" <?= ($filters['class'] ?? '') === $className ? 'selected' : '' ?>><?= e((string) $className) ?></option><?php endforeach; ?>
                </select>
            <?php endif; ?>
            <select name="por_pagina" aria-label="Itens por página">
                <?php foreach ([10, 20, 50] as $size): ?><option value="<?= $size ?>" <?= (int) ($filters['per_page'] ?? 10) === $size ? 'selected' : '' ?>><?= $size ?> por página</option><?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="submit"><i data-lucide="filter"></i> Aplicar</button>
            <a class="btn btn-secondary" href="<?= base_url('inteligencia/casos?tipo=' . urlencode($type)) ?>">Limpar</a>
        </form>

        <div class="intelligence-case-table-wrap">
            <table class="intelligence-case-table">
                <?php if ($kind === 'students'): ?>
                    <?php if ($type === 'stale_monitoring_actions'): ?>
                        <thead><tr><th>Aluno</th><th>Turma</th><th>Risco</th><th>Última atualização</th><th>Intervalo</th><th>Acompanhantes</th><th>Ações</th></tr></thead>
                    <?php elseif ($type === 'low_attendance'): ?>
                        <thead><tr><th>Aluno</th><th>Turma</th><th>Faixa</th><th>Frequência</th><th>Faltas F</th><th>Acompanhantes</th><th>Ações</th></tr></thead>
                    <?php else: ?>
                        <thead><tr><th>Aluno</th><th>Turma</th><th>Risco</th><th>Faltas F</th><th>Ocorrências</th><th>Acompanhantes</th><th>Ações</th></tr></thead>
                    <?php endif; ?>
                    <tbody>
                    <?php foreach ($items as $item): ?><tr>
                        <td><strong><?= e((string) ($item['name'] ?? '')) ?></strong><small><?= e((string) ($item['registration'] ?? '')) ?></small></td>
                        <td><?= e((string) ($item['class_name'] ?? 'Sem turma')) ?></td>
                        <td><span class="risk-badge risk-badge--<?= strtolower(e((string) ($item['risk_level'] ?? 'LOW'))) ?>"><?= e($riskLabels[$item['risk_level'] ?? 'LOW'] ?? 'Baixo') ?> · <?= (int) ($item['risk_score'] ?? 0) ?></span></td>
                        <?php if ($type === 'stale_monitoring_actions'): ?>
                            <td>
                                <?php if (!empty($item['latest_action_date'])): ?>
                                    <?= e(date('d/m/Y', strtotime((string) $item['latest_action_date']))) ?>
                                    <small>ação formal</small>
                                <?php else: ?>
                                    <span class="risk-badge risk-badge--low">Nenhuma ação</span>
                                    <small>Sem intervenção registrada</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="risk-badge risk-badge--moderate"><?= (int) ($item['days_without_action'] ?? 0) ?> dias</span></td>
                        <?php elseif ($type === 'low_attendance'): ?>
                            <td><strong><?= number_format((float) ($item['attendance_percentage'] ?? 0), 1, ',', '.') ?>%</strong></td>
                            <td><?= (int) ($item['unjustified_absences'] ?? 0) ?></td>
                        <?php else: ?>
                            <td><?= (int) ($item['unjustified_absences'] ?? 0) ?></td>
                            <td><?= (int) ($item['total_occurrences'] ?? 0) ?></td>
                        <?php endif; ?>
                        <td>
                            <?php $followers = (int) ($item['active_followers'] ?? 0); ?>
                            <span class="risk-badge <?= $followers > 0 ? 'risk-badge--moderate' : 'risk-badge--low' ?>" title="<?= e((string) ($item['follower_names'] ?? '')) ?>">
                                <?= $followers ?> <?= $followers === 1 ? 'professor' : 'professores' ?>
                            </span>
                            <?php if ($followers > 0 && (string) ($item['follower_names'] ?? '') !== ''): ?>
                                <small><?= e((string) ($item['follower_names'] ?? '')) ?></small>
                            <?php else: ?>
                                <small>Sem acompanhante ativo</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!empty($item['has_related_case']) && (int)($item['monitoring_id']??0)>0): ?>
                                <a class="intelligence-case-action" href="<?= base_url('alunos/perfil?id='.(int)($item['id']??0).'&case_id='.(int)$item['monitoring_id'].'#studentMonitoring') ?>"><i data-lucide="folder-open"></i> Abrir caso</a>
                                <?php if((string)($item['related_case_title']??'')!==''): ?><small><?= e((string)$item['related_case_title']) ?></small><?php endif; ?>
                            <?php else: ?>
                                <a class="intelligence-case-action" href="<?= base_url('alunos/perfil?id='.(int)($item['id']??0).'&create_case=1&alert_type='.urlencode((string)($item['alert_type']??$type)).'#studentMonitoring') ?>"><i data-lucide="folder-plus"></i> Criar novo caso</a>
                                <?php if((int)($item['active_case_count']??0)>0): ?><small><?= (int)$item['active_case_count'] ?> outro(s) caso(s) ativo(s)</small><?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr><?php endforeach; ?>
                <?php elseif ($kind === 'classes'): ?>
                    <thead><tr><th>Turma</th><th>Alunos ativos</th><th>Risco</th><th>Faltas F</th><th>Ocorrências</th><th>Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?><tr>
                        <td><strong><?= e((string) ($item['name'] ?? '')) ?></strong></td>
                        <td><?= (int) ($item['active_students'] ?? 0) ?></td>
                        <td><span class="risk-badge risk-badge--<?= strtolower(e((string) ($item['risk_level'] ?? 'LOW'))) ?>"><?= e($riskLabels[$item['risk_level'] ?? 'LOW'] ?? 'Baixo') ?> · <?= (int) ($item['risk_score'] ?? 0) ?></span></td>
                        <?php if ($type === 'stale_monitoring_actions'): ?>
                            <td>
                                <?php if (!empty($item['latest_action_date'])): ?>
                                    <?= e(date('d/m/Y', strtotime((string) $item['latest_action_date']))) ?>
                                    <small>ação formal</small>
                                <?php else: ?>
                                    <span class="risk-badge risk-badge--low">Nenhuma ação</span>
                                    <small>Sem intervenção registrada</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="risk-badge risk-badge--moderate"><?= (int) ($item['days_without_action'] ?? 0) ?> dias</span></td>
                        <?php else: ?>
                            <td><?= (int) ($item['unjustified_absences'] ?? 0) ?></td>
                            <td><?= (int) ($item['total_occurrences'] ?? 0) ?></td>
                        <?php endif; ?>
                        <td><a class="intelligence-case-action" href="<?= base_url('alunos/turma?id=' . (int) ($item['id'] ?? 0)) ?>"><i data-lucide="users-round"></i> Ver turma</a></td>
                    </tr><?php endforeach; ?>
                <?php else: ?>
                    <thead><tr><th>Ocorrência</th><th>Aluno</th><th>Turma</th><th>Data</th><th>Atraso</th><th>Ações</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?><tr>
                        <td><strong><?= e((string) ($item['title'] ?? 'Ocorrência crítica')) ?></strong><small>Crítica · Aberta</small></td>
                        <td><?= e((string) ($item['student_name'] ?? '')) ?></td>
                        <td><?= e((string) ($item['class_name'] ?? 'Sem turma')) ?></td>
                        <td><?= e(date('d/m/Y', strtotime((string) ($item['occurrence_date'] ?? 'now')))) ?></td>
                        <td><span class="risk-badge risk-badge--critical"><?= (int) ($item['days_open'] ?? 0) ?> dias</span></td>
                        <td><a class="intelligence-case-action" href="<?= base_url('ocorrencias/editar?id=' . (int) ($item['id'] ?? 0)) ?>"><i data-lucide="clipboard-pen-line"></i> Abrir ocorrência</a></td>
                    </tr><?php endforeach; ?>
                <?php endif; ?>
                    <?php if ($items === []): ?><tr><td colspan="<?= $kind === 'students' ? 7 : 6 ?>"><div class="intelligence-empty">Nenhum caso encontrado com os filtros informados.</div></td></tr><?php endif; ?>
                    </tbody>
            </table>
        </div>

        <?php if ((int) ($pagination['pages'] ?? 1) > 1): ?>
            <nav class="intelligence-pagination" aria-label="Paginação">
                <?php for ($page = 1; $page <= (int) $pagination['pages']; $page++): $params = $queryBase; $params['pagina'] = $page; ?>
                    <a class="<?= $page === (int) $pagination['page'] ? 'is-active' : '' ?>" href="<?= base_url('inteligencia/casos?' . http_build_query($params)) ?>"><?= $page ?></a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </section>
</div>
