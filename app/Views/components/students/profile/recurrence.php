<?php

$data = is_array($studentRecurrence ?? null) ? $studentRecurrence : [];
$cases = is_array($data['cases'] ?? null) ? $data['cases'] : [];

if ($cases === []) {
    return;
}

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

$trendLabels = [
    'WORSENING' => ['label' => 'Aumento', 'icon' => 'trending-up', 'class' => 'danger'],
    'IMPROVING' => ['label' => 'Redução', 'icon' => 'trending-down', 'class' => 'success'],
    'STABLE' => ['label' => 'Estável', 'icon' => 'minus', 'class' => 'neutral'],
];

$riskLabels = [
    'CRITICAL' => 'Crítico',
    'HIGH' => 'Alto',
    'ATTENTION' => 'Atenção',
];
?>

<section id="studentRecurrence" class="card recurrence-panel student-recurrence-panel">
    <header class="recurrence-panel-header">
        <div>
            <span class="recurrence-panel-eyebrow">Inteligência de reincidência</span>
            <h2>Reincidências do aluno</h2>
            <p>Casos com o mesmo título ou a mesma disciplina nos últimos <?= (int) ($data['period_days'] ?? 60) ?> dias, comparados ao período anterior.</p>
        </div>

        <div class="recurrence-panel-total">
            <strong><?= (int) ($data['active_cases'] ?? 0) ?></strong>
            <span>tipos ativos</span>
        </div>
    </header>

    <div class="recurrence-panel-summary">
        <div><span>Registros no período</span><strong><?= (int) ($data['total_occurrences'] ?? 0) ?></strong></div>
        <div><span>Casos críticos</span><strong><?= (int) ($data['critical_cases'] ?? 0) ?></strong></div>
        <div><span>Critério mínimo</span><strong><?= (int) ($data['minimum_occurrences'] ?? 3) ?></strong></div>
    </div>

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
                                $trend = $trendLabels[(string) ($case['trend'] ?? 'STABLE')] ?? $trendLabels['STABLE'];
                $risk = strtoupper((string) ($case['risk_level'] ?? 'ATTENTION'));
                $difference = (int) ($case['difference'] ?? 0);
            ?>
            <article class="recurrence-panel-item recurrence-panel-risk-<?= strtolower(e($risk)) ?>">
                <div class="recurrence-panel-identity">
                    <div class="recurrence-panel-icon"><i data-lucide="repeat-2"></i></div>
                    <div>
                        <h3><?= e((string)($case['group_label'] ?? 'Reincidência')) ?></h3>
                        <p><strong><?= e((string)($case['criterion_label'] ?? 'Critério recorrente')) ?></strong> · 
                            Primeira em <?= e(!empty($case['first_occurrence']) ? date('d/m/Y', strtotime((string) $case['first_occurrence'])) : '—') ?>
                            · Última em <?= e(!empty($case['last_occurrence']) ? date('d/m/Y', strtotime((string) $case['last_occurrence'])) : '—') ?>
                        </p>
                    </div>
                </div>

                <div class="recurrence-panel-numbers">
                    <div><strong><?= (int) ($case['total_occurrences'] ?? 0) ?></strong><span>ocorrências</span></div>
                    <div><strong><?= (int) ($case['previous_occurrences'] ?? 0) ?></strong><span>período anterior</span></div>
                    <div><strong><?= $case['days_since_last'] === null ? '—' : (int) $case['days_since_last'] ?></strong><span>dias desde a última</span></div>
                </div>

                <div class="recurrence-panel-badges">
                    <span class="recurrence-panel-risk-label"><?= e($riskLabels[$risk] ?? 'Atenção') ?></span>
                    <span class="recurrence-panel-trend recurrence-panel-trend-<?= e($trend['class']) ?>">
                        <i data-lucide="<?= e($trend['icon']) ?>"></i>
                        <?= e($trend['label']) ?><?= $difference !== 0 ? ' (' . ($difference > 0 ? '+' : '') . $difference . ')' : '' ?>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
    </details>
</section>
