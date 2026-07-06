<div class="settings-preview-pane active" data-preview-pane="sidebar">

    <div class="settings-sidebar-preview">

        <div class="settings-sidebar-logo">

            <div class="settings-sidebar-logo-icon">
                <i data-lucide="graduation-cap"></i>
            </div>

            <div>
                <strong id="schoolShortNamePreview">
                    <?= e($shortName ?? 'SFE') ?>
                </strong>

                <small id="schoolNamePreview">
                    <?= e($name ?? 'Sistema de Frequência Escolar') ?>
                </small>
            </div>

        </div>

        <div class="settings-sidebar-line"></div>

        <div class="settings-sidebar-link active">
            <i data-lucide="layout-dashboard"></i>
            <span>Dashboard</span>
        </div>

        <div class="settings-sidebar-link">
            <i data-lucide="graduation-cap"></i>
            <span>Alunos</span>
        </div>

        <div class="settings-sidebar-link">
            <i data-lucide="school"></i>
            <span>Turmas</span>
        </div>

        <div class="settings-sidebar-link">
            <i data-lucide="clipboard-check"></i>
            <span>Frequência</span>
        </div>

    </div>

</div>