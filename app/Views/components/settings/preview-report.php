<div class="settings-preview-pane" data-preview-pane="report">

    <div class="settings-report-preview">

        <div class="settings-report-header">

            <div class="settings-report-logo">
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
                <strong id="schoolNameReportPreview">
                    <?= e($name ?? 'Sistema de Frequência Escolar') ?>
                </strong>

                <span>Relatório de Frequência Escolar</span>
            </div>

        </div>

        <div class="settings-report-line"></div>

        <div class="settings-report-content">
            <span>Turma: 1º Ano A</span>
            <span>Data: <?= date('d/m/Y') ?></span>
            <span>Frequência: 95,0%</span>
        </div>

    </div>

</div>