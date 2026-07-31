<div class="settings-preview-pane" data-preview-pane="ranking">

    <div class="settings-ranking-preview">

        <div class="settings-ranking-brand">

            <div class="settings-ranking-logo">
                <?php if (!empty($logoPath)): ?>
                    <img
                        src="<?= asset($logoPath) ?>"
                        alt="<?= e($name ?? 'Logo da escola') ?>"
                    >
                <?php else: ?>
                    <i data-lucide="graduation-cap"></i>
                <?php endif; ?>
            </div>

            <div>
                <strong id="schoolNameRankingPreview">
                    <?= e($name ?? 'Sistema de Frequência Escolar') ?>
                </strong>

                <span><?= date('d/m/Y') ?></span>
            </div>

        </div>

        <div class="settings-ranking-kpi">
            <span>Frequência Geral</span>
            <strong>95,0%</strong>
        </div>

        <h3>🏆 Ranking Diário</h3>

        <div class="settings-ranking-row">
            <span>🥇</span>
            <strong>1º Ano A</strong>
            <b>98%</b>
        </div>

        <div class="settings-ranking-row">
            <span>🥈</span>
            <strong>2º Ano B</strong>
            <b>96%</b>
        </div>

        <div class="settings-ranking-row">
            <span>🥉</span>
            <strong>3º Ano C</strong>
            <b>94%</b>
        </div>

    </div>

</div>