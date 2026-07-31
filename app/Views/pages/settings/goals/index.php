<?php
component('base/page-header', [
    'title' => 'Metas da Escola',
    'subtitle' => 'Defina a referência institucional utilizada nos indicadores de frequência.',
]);
$goal = (float)($frequencyGoal ?? 95);
?>
<div class="intelligence-settings-page school-goals-settings-page">
    <?php if (!empty($success)): ?><div class="settings-feedback settings-feedback--success"><i data-lucide="circle-check"></i><span><?= e((string)$success) ?></span></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="settings-feedback settings-feedback--error"><i data-lucide="circle-alert"></i><span><?= e((string)$error) ?></span></div><?php endif; ?>

    <section class="settings-engine-hero panel-hover">
        <div class="settings-engine-hero__icon"><i data-lucide="target"></i></div>
        <div>
            <span class="settings-engine-hero__eyebrow">Meta institucional</span>
            <h2>Frequência mínima esperada</h2>
            <p>O valor é centralizado e passa a orientar gráficos, classificações, alertas, rankings e a identificação de alunos abaixo da meta.</p>
        </div>
        <span class="settings-engine-status"><i data-lucide="activity"></i> <?= number_format($goal, 1, ',', '.') ?>%</span>
    </section>

    <form method="post" action="<?= base_url('configuracoes/metas') ?>" class="settings-engine-form">
        <section class="settings-engine-card settings-engine-card--classes panel-hover">
            <header class="settings-engine-card__header">
                <span><i data-lucide="chart-no-axes-combined"></i></span>
                <div><h3>Frequência</h3><p>Parâmetro global do sistema</p></div>
            </header>
            <div class="settings-engine-card__body">
                <div class="setting-field setting-field--emerald">
                    <div class="setting-field__copy">
                        <div class="setting-field__title"><span class="setting-field__mini-icon"><i data-lucide="target"></i></span><label for="frequency_goal">Meta de frequência da escola</label><span class="risk-level-badge">Percentual</span></div>
                        <p>Alunos, turmas e períodos abaixo deste percentual serão tratados como abaixo da meta.</p>
                        <code>school_goals.frequency_goal</code>
                    </div>
                    <div class="setting-field__control">
                        <div style="display:flex;align-items:center;gap:.5rem"><input id="frequency_goal" name="frequency_goal" type="number" min="0" max="100" step="0.1" value="<?= e(number_format($goal, 1, '.', '')) ?>" required style="max-width:150px"><strong>%</strong></div>
                    </div>
                </div>
            </div>
        </section>
        <div class="settings-engine-actions">
            <a href="<?= base_url('configuracoes') ?>" class="btn-secondary"><i data-lucide="arrow-left"></i> Voltar</a>
            <button type="submit" class="btn-primary"><i data-lucide="save"></i> Salvar meta</button>
        </div>
    </form>
    <form method="post" action="<?= base_url('configuracoes/metas/restaurar') ?>" onsubmit="return confirm('Restaurar a meta recomendada de 95%?')" style="margin-top:.75rem;text-align:right">
        <button type="submit" class="btn-secondary"><i data-lucide="rotate-ccw"></i> Restaurar 95%</button>
    </form>

    <section class="card" style="margin-top:1rem;padding:1.2rem">
        <h3 style="margin-top:0">Integrações desta meta</h3>
        <p style="margin-bottom:.5rem">O novo valor influencia imediatamente:</p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:.65rem">
            <span>✓ Painel do Gestor e status geral</span><span>✓ Gráfico de evolução da frequência</span><span>✓ Indicadores por período</span><span>✓ Ranking diário das turmas</span><span>✓ Alunos em alerta</span><span>✓ Casos de baixa frequência</span><span>✓ Mapa de calor</span><span>✓ Painel da turma</span>
        </div>
    </section>
</div>
