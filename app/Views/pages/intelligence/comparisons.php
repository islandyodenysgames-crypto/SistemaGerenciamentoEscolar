<link rel="stylesheet" href="<?= base_url('assets/css/pages/intelligence-comparisons.css') ?>">
<?php
$data = $comparison ?? [];
$summary = $data['summary'] ?? [];
$classes = $data['classes'] ?? [];
$period = $data['period'] ?? [];
$riskLabels = ['LOW' => 'Baixo', 'MODERATE' => 'Atenção', 'HIGH' => 'Alto', 'CRITICAL' => 'Crítico'];
$trendText = static function (array $trend): string {
    $status = (string) ($trend['status'] ?? 'STABLE');
    $percentage = abs((float) ($trend['percentage'] ?? 0));
    return match ($status) {
        'IMPROVING' => 'Melhora ' . number_format($percentage, 1, ',', '.') . '%',
        'WORSENING' => 'Piora ' . number_format($percentage, 1, ',', '.') . '%',
        default => 'Estável',
    };
};
component('base/page-header', [
    'title' => 'Comparações entre turmas',
    'subtitle' => 'Compare os principais sinais de frequência, ocorrências e risco escolar.',
]);
?>
<div class="comparison-page">
    <section class="comparison-hero">
        <div>
            <a href="<?= base_url('inteligencia') ?>" class="comparison-back"><i data-lucide="arrow-left"></i> Voltar à Central</a>
            <span><i data-lucide="scale"></i> Sprint 2.4</span>
            <h2>Visão comparativa da escola</h2>
            <p>Os indicadores usam o mesmo período e as mesmas regras configuradas na Central de Inteligência.</p>
        </div>
        <div class="comparison-period">
            <i data-lucide="calendar-range"></i>
            <small>Período analisado</small>
            <strong><?= e(date('d/m/Y', strtotime((string) ($period['start'] ?? 'now')))) ?> a <?= e(date('d/m/Y', strtotime((string) ($period['end'] ?? 'now')))) ?></strong>
        </div>
    </section>

    <section class="comparison-summary">
        <article><i data-lucide="school"></i><div><strong><?= (int) ($summary['classes'] ?? 0) ?></strong><span>Turmas comparadas</span></div></article>
        <article><i data-lucide="gauge"></i><div><strong><?= e(number_format((float) ($summary['average_risk'] ?? 0), 1, ',', '.')) ?></strong><span>Risco médio</span></div></article>
        <article><i data-lucide="calendar-check"></i><div><strong><?= ($summary['average_frequency'] ?? null) !== null ? e(number_format((float) $summary['average_frequency'], 1, ',', '.')) . '%' : '—' ?></strong><span>Frequência média</span></div></article>
        <article><i data-lucide="triangle-alert"></i><div><strong><?= (int) ($summary['attention_classes'] ?? 0) ?></strong><span>Turmas em atenção</span></div></article>
    </section>

    <section class="comparison-panel">
        <header>
            <div><span><i data-lucide="list-ordered"></i></span><div><h3>Ranking comparativo</h3><p>Maior risco aparece primeiro. Use os atalhos para aprofundar cada turma.</p></div></div>
        </header>
        <?php if ($classes === []): ?>
            <div class="comparison-empty"><i data-lucide="inbox"></i><p>Nenhuma turma ativa encontrada.</p></div>
        <?php else: ?>
            <div class="comparison-table-wrap">
                <table class="comparison-table">
                    <thead><tr><th>Posição</th><th>Turma</th><th>Risco</th><th>Frequência</th><th>Faltas F</th><th>Ocorrências</th><th>Graves</th><th>Evolução</th><th>Ação</th></tr></thead>
                    <tbody>
                    <?php foreach ($classes as $class): ?>
                        <?php $level = (string) ($class['risk_level'] ?? 'LOW'); ?>
                        <tr id="turma-<?= (int) $class['id'] ?>" class="<?= !empty($class['highlighted']) ? 'is-highlighted' : '' ?>">
                            <td><span class="comparison-rank">#<?= (int) ($class['rank'] ?? 0) ?></span></td>
                            <td><strong><?= e((string) ($class['name'] ?? 'Turma')) ?></strong><small><?= e(trim(((string) ($class['year'] ?? '')) . 'º Ano • ' . ((string) ($class['shift'] ?? '')), ' •ºAno')) ?></small></td>
                            <td><span class="comparison-risk comparison-risk--<?= strtolower($level) ?>"><?= e($riskLabels[$level] ?? $level) ?> · <?= (int) ($class['risk_score'] ?? 0) ?></span></td>
                            <td><strong><?= ($class['frequency_percentage'] ?? null) !== null ? e(number_format((float) $class['frequency_percentage'], 1, ',', '.')) . '%' : '—' ?></strong><small><?= (int) ($class['active_students'] ?? 0) ?> aluno(s)</small></td>
                            <td><strong><?= (int) ($class['unjustified_absences'] ?? 0) ?></strong><small><?= (int) ($class['attendance_students_affected'] ?? 0) ?> afetado(s)</small></td>
                            <td><strong><?= (int) ($class['total_occurrences'] ?? 0) ?></strong><small><?= (int) ($class['open_occurrences'] ?? 0) ?> aberta(s)</small></td>
                            <td><?= (int) ($class['serious_occurrences'] ?? 0) ?></td>
                            <td><span class="comparison-trend comparison-trend--<?= strtolower((string) (($class['attendance_trend']['status'] ?? 'STABLE'))) ?>"><?= e($trendText($class['attendance_trend'] ?? [])) ?></span><small>Faltas</small><span class="comparison-trend comparison-trend--<?= strtolower((string) (($class['occurrence_trend']['status'] ?? 'STABLE'))) ?>"><?= e($trendText($class['occurrence_trend'] ?? [])) ?></span><small>Ocorrências</small></td>
                            <td><a class="comparison-action" href="<?= base_url('alunos/turma?id=' . (int) $class['id'] . '#classIntelligence') ?>"><i data-lucide="external-link"></i> Abrir</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php if (!empty($data['highlight_class_id'])): ?>
<script>document.addEventListener('DOMContentLoaded',()=>document.getElementById('turma-<?= (int) $data['highlight_class_id'] ?>')?.scrollIntoView({behavior:'smooth',block:'center'}));</script>
<?php endif; ?>
