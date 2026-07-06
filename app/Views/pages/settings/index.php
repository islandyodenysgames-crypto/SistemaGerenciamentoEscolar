<?php

component('base/page-header', [
    'title' => 'Configurações',
    'subtitle' => 'Central administrativa do Sistema de Frequência Escolar'
]);

$schoolName = school('name', app_name());
$schoolLogo = school('logo_path');
$schoolConfigured = school('id') !== null;

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

    <a href="<?= base_url('configuracoes/identidade') ?>" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="school"></i>
        </div>

        <div>
            <h3>Identidade da Escola</h3>
            <p>Logo, nome, contatos, redes sociais e dados institucionais.</p>

            <span class="settings-status <?= $schoolConfigured ? 'settings-status-success' : 'settings-status-warning' ?>">
                <?= $schoolConfigured ? 'Configurada' : 'Incompleta' ?>
            </span>
        </div>
    </a>

    <a href="#" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="target"></i>
        </div>

        <div>
            <h3>Metas da Escola</h3>
            <p>Frequência mínima, objetivos e parâmetros de acompanhamento.</p>

            <span class="settings-status settings-status-success">
                95%
            </span>
        </div>
    </a>

    <a href="#" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="calendar-days"></i>
        </div>

        <div>
            <h3>Ano Letivo</h3>
            <p>Calendário escolar, períodos e configurações do ano vigente.</p>

            <span class="settings-status settings-status-warning">
                Pendente
            </span>
        </div>
    </a>

    <a href="<?= base_url('usuarios') ?>" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="users"></i>
        </div>

        <div>
            <h3>Usuários e Permissões</h3>
            <p>Gerencie usuários, acessos e permissões do sistema.</p>

            <span class="settings-status settings-status-success">
                Ativo
            </span>
        </div>
    </a>

    <a href="#" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="palette"></i>
        </div>

        <div>
            <h3>Aparência</h3>
            <p>Cores, tema visual e identidade gráfica da aplicação.</p>

            <span class="settings-status settings-status-success">
                Padrão
            </span>
        </div>
    </a>

    <a href="#" class="card settings-card">
        <div class="settings-card-icon">
            <i data-lucide="database-backup"></i>
        </div>

        <div>
            <h3>Backup</h3>
            <p>Exportação, restauração e segurança dos dados escolares.</p>

            <span class="settings-status settings-status-warning">
                Não configurado
            </span>
        </div>
    </a>

</div>