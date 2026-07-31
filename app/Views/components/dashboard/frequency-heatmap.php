<?php
$data = is_array($heatmap ?? null) ? $heatmap : [];
$items = is_array($data['items'] ?? null) ? $data['items'] : [];
$period = (string) ($data['period'] ?? 'month');
$periodLabel = (string) ($data['period_label'] ?? 'Último mês');
$goalPercentage = (float)($goalPercentage ?? 95);
$goodThreshold = max(0, $goalPercentage - 5);
$attentionThreshold = max(0, $goalPercentage - 15);
$weekdayLabels = [1 => 'Seg', 2 => 'Ter', 3 => 'Qua', 4 => 'Qui', 5 => 'Sex', 6 => 'Sáb', 7 => 'Dom'];
$level = static function (?float $value) use ($goalPercentage, $goodThreshold, $attentionThreshold): string {
    if ($value === null) return 'empty';
    if ($value >= $goalPercentage) return 'excellent';
    if ($value >= $goodThreshold) return 'good';
    if ($value >= $attentionThreshold) return 'attention';
    return 'critical';
};
?>
<section
    class="card frequency-heatmap-panel"
    id="frequency-heatmap-panel"
    data-heatmap-endpoint="<?= e(base_url('dashboard/mapa-calor-frequencia')) ?>"
    data-heatmap-current-period="<?= e($period) ?>"
>
    <header class="frequency-heatmap-header">
        <div>
            <span class="frequency-heatmap-eyebrow">Análise de frequência</span>
            <h2>Mapa de calor da frequência</h2>
            <p>Visualização diária de <strong data-heatmap-period-label><?= e(function_exists('mb_strtolower') ? mb_strtolower($periodLabel, 'UTF-8') : strtolower($periodLabel)) ?></strong>. Passe o cursor sobre um dia para consultar os detalhes.</p>
        </div>
        <form class="frequency-heatmap-filter" data-heatmap-filter>
            <label for="frequencyHeatmapPeriod">Período</label>
            <select id="frequencyHeatmapPeriod" data-heatmap-period>
                <option value="week" <?= $period === 'week' ? 'selected' : '' ?>>Última semana</option>
                <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Último mês</option>
                <option value="bimester" <?= $period === 'bimester' ? 'selected' : '' ?>>Último bimestre</option>
                <option value="semester" <?= $period === 'semester' ? 'selected' : '' ?>>Último semestre</option>
            </select>
        </form>
    </header>
    <div class="frequency-heatmap-legend" aria-label="Legenda">
        <span><i class="heatmap-level-excellent"></i><?= number_format($goalPercentage, 1, ',', '.') ?>% ou mais</span>
        <span><i class="heatmap-level-good"></i><?= number_format($goodThreshold, 1, ',', '.') ?>% até a meta</span>
        <span><i class="heatmap-level-attention"></i><?= number_format($attentionThreshold, 1, ',', '.') ?>% a <?= number_format($goodThreshold - 0.1, 1, ',', '.') ?>%</span>
        <span><i class="heatmap-level-critical"></i>Abaixo de <?= number_format($attentionThreshold, 1, ',', '.') ?>%</span>
        <span><i class="heatmap-level-empty"></i>Sem registro</span>
    </div>
    <div class="frequency-heatmap-body">
        <div class="frequency-heatmap-weekdays">
            <?php foreach ($weekdayLabels as $label): ?><span><?= e($label) ?></span><?php endforeach; ?>
        </div>
        <div class="frequency-heatmap-grid">
            <?php $leadingEmpty = $items !== [] ? max(0, (int) ($items[0]['weekday'] ?? 1) - 1) : 0; ?>
            <?php for ($i = 0; $i < $leadingEmpty; $i++): ?>
                <div class="frequency-heatmap-cell heatmap-level-placeholder" aria-hidden="true"></div>
            <?php endfor; ?>
            <?php foreach ($items as $item): ?>
                <?php
                    $percentage = $item['percentage'] === null ? null : (float) $item['percentage'];
                    $total = (int) ($item['total_records'] ?? 0);
                    $presents = (int) ($item['presents'] ?? 0);
                    $absent = max(0, $total - $presents);
                    $title = date('d/m/Y', strtotime((string)$item['date'])) . ' — ' . ($percentage === null
                        ? 'Sem registro de frequência'
                        : number_format($percentage, 1, ',', '.') . '% de presença · ' . $presents . ' presentes · ' . $absent . ' ausentes');
                ?>
                <div class="frequency-heatmap-cell heatmap-level-<?= e($level($percentage)) ?>" title="<?= e($title) ?>" aria-label="<?= e($title) ?>">
                    <span><?= (int)date('d', strtotime((string)$item['date'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
