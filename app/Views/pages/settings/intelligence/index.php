<?php
component('base/page-header', [
    'title' => 'Configurações da Inteligência',
    'subtitle' => 'Ajuste as regras do motor analítico sem alterar o código.',
]);

$icons = [
    'Reincidência' => 'repeat-2',
    'Risco escolar' => 'gauge',
    'Níveis de risco' => 'shield-alert',
    'Turmas' => 'school',
    'Tendências' => 'trending-up',
    'Notificações' => 'bell-ring',
    'Recomendações' => 'lightbulb',
    'Exibição de anexos' => 'images',
];

$categoryClasses = [
    'Reincidência' => 'recurrence',
    'Risco escolar' => 'school-risk',
    'Níveis de risco' => 'risk-levels',
    'Turmas' => 'classes',
    'Tendências' => 'trends',
    'Notificações' => 'notifications',
    'Recomendações' => 'recommendations',
    'Exibição de anexos' => 'attachments-display',
];

$fieldPresentation = [
    'recurrence_limit' => ['tone' => 'violet', 'badge' => 'Ocorrências', 'icon' => 'list-restart'],
    'analysis_window_days' => ['tone' => 'blue', 'badge' => 'Período', 'icon' => 'calendar-days'],
    'stale_critical_days' => ['tone' => 'rose', 'badge' => 'Prazo', 'icon' => 'clock-alert'],
    'risk_weight_frequency' => ['tone' => 'cyan', 'badge' => 'Frequência', 'icon' => 'clipboard-check'],
    'risk_weight_occurrences' => ['tone' => 'orange', 'badge' => 'Ocorrências', 'icon' => 'triangle-alert'],
    'risk_weight_severity' => ['tone' => 'red', 'badge' => 'Gravidade', 'icon' => 'shield-alert'],
    'risk_moderate_threshold' => ['tone' => 'attention', 'badge' => 'Atenção', 'icon' => 'circle-alert'],
    'risk_high_threshold' => ['tone' => 'high', 'badge' => 'Alto', 'icon' => 'flame'],
    'risk_critical_threshold' => ['tone' => 'critical', 'badge' => 'Crítico', 'icon' => 'siren'],
    'class_attention_threshold' => ['tone' => 'indigo', 'badge' => 'Turma', 'icon' => 'users-round'],
    'trend_stable_margin' => ['tone' => 'emerald', 'badge' => 'Estabilidade', 'icon' => 'activity'],
    'notification_recurrent_student' => ['tone' => 'violet', 'badge' => 'Reincidência', 'icon' => 'user-round-search'],
    'notification_critical_occurrence' => ['tone' => 'red', 'badge' => 'Crítica', 'icon' => 'siren'],
    'notification_critical_class' => ['tone' => 'orange', 'badge' => 'Turma', 'icon' => 'school'],
    'recommendation_guardian_contact' => ['tone' => 'amber', 'badge' => 'Responsável', 'icon' => 'phone-call'],
    'show_attachment_image_carousels' => ['tone' => 'emerald', 'badge' => 'Imagens', 'icon' => 'gallery-horizontal-end'],
];
?>

<div class="intelligence-settings-page">
    <?php if (!empty($success)): ?>
        <div class="settings-feedback settings-feedback--success"><i data-lucide="circle-check"></i><span><?= e((string) $success) ?></span></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="settings-feedback settings-feedback--error"><i data-lucide="circle-alert"></i><span><?= e((string) $error) ?></span></div>
    <?php endif; ?>

    <section class="settings-engine-hero panel-hover">
        <div class="settings-engine-hero__icon"><i data-lucide="brain-circuit"></i></div>
        <div>
            <span class="settings-engine-hero__eyebrow">Configuration Engine</span>
            <h2>Regras transparentes e ajustáveis</h2>
            <p>Os valores abaixo alimentam a Central de Inteligência e podem ser restaurados para as recomendações originais do sistema.</p>
        </div>
        <span class="settings-engine-status"><i data-lucide="database-zap"></i> Ativo</span>
    </section>

    <form method="post" action="<?= base_url('configuracoes/inteligencia') ?>" class="settings-engine-form">
        <div class="settings-engine-grid">
            <?php foreach (($categories ?? []) as $category => $settings): ?>
                <section class="settings-engine-card settings-engine-card--<?= e($categoryClasses[$category] ?? 'default') ?> panel-hover">
                    <header class="settings-engine-card__header">
                        <span><i data-lucide="<?= e($icons[$category] ?? 'sliders-horizontal') ?>"></i></span>
                        <div><h3><?= e((string) $category) ?></h3><p><?= count($settings) ?> parâmetro(s) configurável(is)</p></div>
                    </header>
                    <div class="settings-engine-card__body">
                        <?php foreach ($settings as $setting): ?>
                            <?php
                            $presentation = $fieldPresentation[(string) $setting['key_name']] ?? [
                                'tone' => 'default',
                                'badge' => null,
                                'icon' => 'sliders-horizontal',
                            ];
                            ?>
                            <div class="setting-field setting-field--<?= e((string) $presentation['tone']) ?>">
                                <div class="setting-field__copy">
                                    <div class="setting-field__title">
                                        <span class="setting-field__mini-icon"><i data-lucide="<?= e((string) $presentation['icon']) ?>"></i></span>
                                        <label for="setting-<?= e((string) $setting['key_name']) ?>"><?= e((string) $setting['label']) ?></label>
                                        <?php if ($presentation['badge'] !== null): ?><span class="risk-level-badge"><?= e((string) $presentation['badge']) ?></span><?php endif; ?>
                                    </div>
                                    <p><?= e((string) $setting['description']) ?></p>
                                    <code>intelligence.<?= e((string) $setting['key_name']) ?></code>
                                </div>
                                <div class="setting-field__control">
                                    <?php if ($setting['type'] === 'boolean'): ?>
                                        <label class="setting-switch">
                                            <input type="checkbox" id="setting-<?= e((string) $setting['key_name']) ?>" name="<?= e((string) $setting['key_name']) ?>" value="1" <?= (int) $setting['value'] === 1 ? 'checked' : '' ?>>
                                            <span></span>
                                        </label>
                                    <?php else: ?>
                                        <input class="form-control setting-number" type="number" id="setting-<?= e((string) $setting['key_name']) ?>" name="<?= e((string) $setting['key_name']) ?>" value="<?= e((string) $setting['value']) ?>" min="0" max="365">
                                        <?php if (str_contains((string) $setting['key_name'], 'days')): ?><small>dias</small><?php endif; ?>
                                        <?php if (str_contains((string) $setting['key_name'], 'weight') || str_contains((string) $setting['key_name'], 'threshold') || str_contains((string) $setting['key_name'], 'margin')): ?><small>%</small><?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <div class="settings-engine-actions">
            <div><strong>Configurações do motor</strong><span>Salve os ajustes ou restaure os valores recomendados quando necessário.</span></div>
            <div class="settings-engine-actions__buttons">
                <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Salvar alterações</button>
            </div>
        </div>
    </form>

    <form method="post" action="<?= base_url('configuracoes/inteligencia/restaurar') ?>" class="settings-reset-form" onsubmit="return confirm('Restaurar todos os valores recomendados da Inteligência?');">
        <button type="submit" class="btn btn-secondary"><i data-lucide="rotate-ccw"></i> Restaurar padrões</button>
    </form>

</div>
