<?php
$data = is_array($recurrence ?? null) ? $recurrence : [];
$cases = is_array($data['cases'] ?? null) ? $data['cases'] : [];
$typeLabels = [
    'OBSERVATION' => 'Observação',
    'WARNING' => 'Advertência',
    'SUSPENSION' => 'Suspensão',
    'REFERRAL' => 'Encaminhamento',
    'PRAISE' => 'Elogio',
    'BEHAVIOR' => 'Comportamento',
    'PEDAGOGICAL' => 'Pedagógica',
    'ATTENDANCE' => 'Frequência',
    'DISCIPLINARY' => 'Disciplinar',
    'OTHER' => 'Outra ocorrência',
];
$riskLabels = ['CRITICAL' => 'Crítico', 'HIGH' => 'Alto', 'ATTENTION' => 'Atenção'];
?>
<section class="card recurrence-panel recurrence-dashboard-card" id="activeRecurrences">
    <header class="recurrence-panel-header">
        <div>
            <span class="recurrence-panel-eyebrow">Inteligência de reincidência</span>
            <h2>Reincidências ativas</h2>
            <p>Casos com o mesmo título ou a mesma disciplina nos últimos <?= (int)($data['period_days'] ?? 60) ?> dias.</p>
        </div>
        <div class="recurrence-panel-total">
            <strong><?= (int)($data['active_students'] ?? 0) ?></strong>
            <span>alunos</span>
        </div>
    </header>

    <div class="recurrence-panel-summary">
        <div><span>Casos ativos</span><strong><?= (int)($data['active_cases'] ?? 0) ?></strong></div>
        <div><span>Casos críticos</span><strong><?= (int)($data['critical_cases'] ?? 0) ?></strong></div>
        <div><span>Novos na semana</span><strong><?= (int)($data['new_this_week'] ?? 0) ?></strong></div>
    </div>

    <?php if ($cases): ?>
        <details class="recurrence-results-toggle">
            <summary>
                <span class="recurrence-results-toggle-label">
                    <i data-lucide="list-filter"></i>
                    <span class="recurrence-results-show">Mostrar resultados</span>
                    <span class="recurrence-results-hide">Ocultar resultados</span>
                </span>
                <strong><?= count($cases) ?> <?= count($cases) === 1 ? 'grupo reincidente' : 'grupos reincidentes' ?></strong>
                <i data-lucide="chevron-down" class="recurrence-results-chevron"></i>
            </summary>
            <div class="recurrence-panel-list">
            <?php foreach ($cases as $case): ?>
                <?php
                                        $risk = strtoupper((string)($case['risk_level'] ?? 'ATTENTION'));
                    $records = is_array($case['records'] ?? null) ? $case['records'] : [];
                ?>
                <details class="recurrence-panel-item recurrence-panel-risk-<?= e(strtolower($risk)) ?>">
                    <summary>
                        <div class="recurrence-panel-identity">
                            <span class="recurrence-panel-icon"><i data-lucide="repeat-2"></i></span>
                            <div>
                                <strong><?= e((string)$case['student_name']) ?></strong>
                                <span><?= e((string)($case['class_name'] ?? 'Sem turma')) ?> · <?= e((string)($case['group_label'] ?? 'Reincidência')) ?></span>
                            </div>
                        </div>
                        <div class="recurrence-panel-item-meta">
                            <span class="recurrence-panel-criterion"><?= e((string)($case['criterion_label'] ?? 'Critério recorrente')) ?></span>
                            <span class="recurrence-panel-risk-label"><?= e($riskLabels[$risk] ?? 'Atenção') ?></span>
                            <span class="recurrence-panel-count"><?= (int)$case['total_occurrences'] ?> registros</span>
                            <i data-lucide="chevron-down" class="recurrence-panel-chevron"></i>
                        </div>
                    </summary>
                    <div class="recurrence-panel-records">
                        <div class="recurrence-panel-records-header">
                            <strong>Registros reincidentes</strong>
                            <a href="<?= base_url('alunos/perfil?id=' . (int)$case['student_id'] . '#studentRecurrence') ?>">Abrir perfil do aluno</a>
                        </div>
                        <?php foreach ($records as $record): ?>
                            <a class="recurrence-panel-record" href="<?= base_url('ocorrencias/editar?id=' . (int)($record['id'] ?? 0)) ?>">
                                <time><?= e(!empty($record['occurrence_date']) ? date('d/m/Y', strtotime((string)$record['occurrence_date'])) : '—') ?></time>
                                <span>
                                    <strong><?= e((string)($record['title'] ?? 'Ocorrência')) ?></strong>
                                    <small><?= e((string)($record['description'] ?? '')) ?></small>
                                </span>
                                <i data-lucide="arrow-up-right"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
            </div>
        </details>
    <?php else: ?>
        <div class="activity-empty">Nenhuma reincidência ativa foi identificada no período.</div>
    <?php endif; ?>
</section>
