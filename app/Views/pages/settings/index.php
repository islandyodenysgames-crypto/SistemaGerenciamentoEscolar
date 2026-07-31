<?php

component('base/page-header', [
    'title' => 'Configurações',
    'subtitle' => 'Central administrativa do Sistema de Frequência Escolar'
]);

$schoolName = school('name', app_name());
$schoolLogo = school('logo_path');
$schoolConfigured = school('id') !== null;
$frequencyGoal = (float)($frequencyGoal ?? 95);

?>

<div class="settings-summary">

    <div class="settings-summary-card">
        <span>Escola</span>
        <strong><?= $schoolConfigured ? 'Configurada' : 'Incompleta' ?></strong>
    </div>

    <div class="settings-summary-card">
        <span>Nome</span>
        <strong><?= e($schoolName) ?></strong>
    </div>

    <div class="settings-summary-card">
        <span>Logo</span>
        <strong><?= !empty($schoolLogo) ? 'Enviado' : 'Pendente' ?></strong>
    </div>

</div>

<div class="settings-grid">

    <a
        href="<?= base_url('configuracoes/inteligencia') ?>"
        class="card settings-card"
    >
        <div class="settings-card-icon">
            <i data-lucide="brain-circuit"></i>
        </div>
        <div>
            <h3>Inteligência Escolar</h3>
            <p>Reincidência, pesos de risco, tendências, alertas e recomendações.</p>
            <span class="settings-status settings-status-success">Engine ativo</span>
        </div>
    </a>

    <a
        href="<?= base_url('configuracoes/identidade') ?>"
        class="card settings-card"
    >
        <div class="settings-card-icon">
            <i data-lucide="school"></i>
        </div>

        <div>

            <h3>Identidade da Escola</h3>

            <p>
                Logo, nome, contatos, redes sociais e identidade visual da escola.
            </p>

            <span class="settings-status <?= $schoolConfigured ? 'settings-status-success' : 'settings-status-warning' ?>">
                <?= $schoolConfigured ? 'Configurada' : 'Incompleta' ?>
            </span>

        </div>

    </a>

    <a
        href="<?= base_url('configuracoes/metas') ?>"
        class="card settings-card"
    >
        <div class="settings-card-icon">
            <i data-lucide="target"></i>
        </div>

        <div>

            <h3>Metas da Escola</h3>

            <p>
                Frequência mínima, indicadores e metas institucionais.
            </p>

            <span class="settings-status settings-status-success">
                <?= number_format($frequencyGoal, 1, ',', '.') ?>%
            </span>

        </div>

    </a>

    <a
        href="#"
        class="card settings-card"
    >
        <div class="settings-card-icon">
            <i data-lucide="calendar-days"></i>
        </div>

        <div>

            <h3>Ano Letivo</h3>

            <p>
                Calendário escolar, períodos letivos e configurações anuais.
            </p>

            <span class="settings-status settings-status-warning">
                Pendente
            </span>

        </div>

    </a>

    <a
        href="<?= base_url('usuarios') ?>"
        class="card settings-card"
    >
        <div class="settings-card-icon">
            <i data-lucide="users"></i>
        </div>

        <div>

            <h3>Usuários e Permissões</h3>

            <p>
                Gerencie usuários, perfis de acesso e permissões do sistema.
            </p>

            <span class="settings-status settings-status-success">
                Ativo
            </span>

        </div>

    </a>

    <a
        href="<?= base_url('configuracoes/dados') ?>"
        class="card settings-card"
    >
        <div class="settings-card-icon">
            <i data-lucide="database-backup"></i>
        </div>

        <div>

            <h3>Backup</h3>

            <p>
                Exportação, restauração e segurança dos dados escolares.
            </p>

            <span class="settings-status settings-status-success">
                Disponível
            </span>

        </div>

    </a>

</div>